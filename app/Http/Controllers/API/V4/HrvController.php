<?php

namespace App\Http\Controllers\API\V4;

use App\Models\Hrv;
use App\Models\UserBaseline;
use App\Models\Quote;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\DB;

class HrvController extends BaseController
{
    public function store(Request $request)
    {
        // Validate that 'hrvs' is a required array
        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
            'data.*.sample_id' => 'required',
            'data.*.device_timestamp' => 'required',
            'data.*.datetime' => 'required|date_format:Y-m-d H:i:s',
            'data.*.hrv' => 'required|integer',
            'data.*.sdnn' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $userId = Auth::id();
        $hrvRecords = [];
        $baseline_value = UserBaseline::where('user_id', $userId)->value('baseline_value');

        foreach ($request->input('data') as $hrvData) {
            $hrvData['user_id'] = $userId;
            $hrvData['status'] = $this->getHrvStatus($hrvData['hrv'], $baseline_value);
            $hrvData['baseline_value'] = $baseline_value;
            // Upsert based on user_id + sample_id
            $hrv = Hrv::updateOrCreate(
                ['user_id' => $userId, 'sample_id' => $hrvData['sample_id']],
                $hrvData
            );

            $hrvRecords[] = $hrv;
        }

        return $this->sendResponse($hrvRecords, 'HRV records added/updated for logged-in user.');
    }

    public function index()
    {
        $userId = Auth::id();
        $frequency = request()->get('frequency');
        $baseline_value = request()->get('baseline_value') ?? 0;

        if (!in_array($frequency, ['daily', 'weekly'])) {
            return $this->sendError('Invalid frequency.', [
                'frequency' => 'Frequency must be either daily or weekly',
            ]);
        }

        try {
            switch ($frequency) {
                case 'daily':
                    $date = request()->get('date');
                    if (!$date) {
                        return $this->sendError('Date is required for daily frequency.');
                    }

                    $startDate = Carbon::parse($date)->startOfDay();
                    $endDate = Carbon::parse($date)->endOfDay();
                    break;

                case 'weekly':
                    $startDateInput = request()->get('start_date');
                    $endDateInput = request()->get('end_date');

                    if (!$startDateInput || !$endDateInput) {
                        return $this->sendError('Start and end dates are required for weekly frequency.');
                    }

                    $startDate = Carbon::parse($startDateInput)->startOfDay();
                    $endDate = Carbon::parse($endDateInput)->endOfDay();

                    if ($startDate->gt($endDate)) {
                        return $this->sendError('Start date must be before or equal to end date.');
                    }

                    break;
            }
        } catch (\Exception $e) {
            return $this->sendError('Invalid date format.');
        }

        DB::statement("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION'");

        $ordering_hrv = ($frequency === 'daily') ? 'desc' : 'asc';

        $hrvs = Hrv::where('user_id', $userId)
            ->whereBetween('datetime', [$startDate, $endDate])
            ->groupBy('sample_id')
            ->orderBy('datetime', $ordering_hrv)
            ->get();

        if ($hrvs->isEmpty()) {
            return $this->sendResponse(
                [],
                'No HRV data found for the selected date range.'
            );
        }

        if ($frequency === 'daily') {
            return $this->getDailyHrvData($hrvs, $userId, $startDate);
        } else {
            return $this->getWeeklyHrvData($hrvs, $userId, $startDate, $endDate, $baseline_value);
        }
    }

    private function getDailyHrvData($hrvs, $userId, $startDate)
    {

        $baseline_value = Hrv::where('user_id', $userId)->whereDate('datetime', $startDate)->max('baseline_value');

        // Transform HRV records based on baseline_value to get new status
        $transformedHrvs = $this->transformHrvRecords($hrvs, $baseline_value);

        // Convert back to collection for easier manipulation
        $transformedCollection = collect($transformedHrvs);

        // Calculate counts based on new status from transformed records
        $lowCount = $transformedCollection->where('status', 1)->count(); // Stressed
        $recoveryCount = $transformedCollection->where('status', 2)->count(); // Recovery
        $balancedCount = $transformedCollection->where('status', 3)->count(); // Balanced
        $goodCount = $transformedCollection->where('status', 4)->count(); // Happy

        $totalCount = $lowCount + $recoveryCount + $balancedCount + $goodCount;
        $lowPercentage = ceil($totalCount > 0 ? ($lowCount / $totalCount) * 100 : 0);
        $recoveryPercentage = ceil($totalCount > 0 ? ($recoveryCount / $totalCount) * 100 : 0);
        $balancedPercentage = ceil($totalCount > 0 ? ($balancedCount / $totalCount) * 100 : 0);
        $goodPercentage = ceil($totalCount > 0 ? ($goodCount / $totalCount) * 100 : 0);

        $sumPercent = $lowPercentage + $recoveryPercentage + $balancedPercentage + $goodPercentage;
        if ($sumPercent > 100) {
            $overflow = $sumPercent - 100;

            $percentages = [
                'low' => $lowPercentage,
                'recovery' => $recoveryPercentage,
                'balanced' => $balancedPercentage,
                'good' => $goodPercentage,
            ];
            arsort($percentages);
            foreach ($percentages as $key => &$value) {
                if ($overflow <= 0) break;
                $deduct = min($value, $overflow);
                $value -= $deduct;
                $overflow -= $deduct;
            }
            $lowPercentage = $percentages['low'];
            $recoveryPercentage = $percentages['recovery'];
            $balancedPercentage = $percentages['balanced'];
            $goodPercentage = $percentages['good'];
        }

        return $this->sendResponse([
            'user_id' => $userId,
            'date' => $startDate->toDateString(),
            'hrv' => $transformedHrvs,
            'average' => [
                'low' => $lowPercentage,
                'recovery' => $recoveryPercentage,
                'balanced' => $balancedPercentage,
                'good' => $goodPercentage,
            ],
        ], 'Daily HRV data.');
    }

    private function getWeeklyHrvData($hrvs, $userId, $startDate, $endDate)
    {
        $totalLow = 0;
        $totalRecovery = 0;
        $totalBalanced = 0;
        $totalGood = 0;
        $weekData = [];
        $current = $startDate->copy();
        // $baseline_value = UserBaseline::where('user_id', $userId)->value('baseline_value');
        while ($current->lte($endDate)) {
            $dayRecords = $hrvs->filter(function ($item) use ($current) {
                return Carbon::parse($item->datetime)->isSameDay($current);
            });

            $baseline_value = Hrv::where('user_id', $userId)->whereDate('datetime', $current)->max('baseline_value') ?? 0;
            $hrvValues = $dayRecords->pluck('hrv');
            $averageHrv = $hrvValues->count() > 0 ? floor($hrvValues->avg()) : 0;
            $status = $this->getHrvStatus($averageHrv, $baseline_value);

            // Slot definitions
            $slots = [
                ["slot" => 1, "start" => "00:00", "end" => "06:00", "from" => 0, "to" => 6],
                ["slot" => 2, "start" => "06:00", "end" => "12:00", "from" => 6, "to" => 12],
                ["slot" => 3, "start" => "12:00", "end" => "18:00", "from" => 12, "to" => 18],
                ["slot" => 4, "start" => "18:00", "end" => "24:00", "from" => 18, "to" => 24],
            ];
            $slotData = [];
            foreach ($slots as $slot) {
                $slotRecords = $dayRecords->filter(function ($item) use ($slot) {
                    $hour = Carbon::parse($item->datetime)->hour;
                    return $hour >= $slot["from"] && $hour < $slot["to"];
                });
                $slotHrvValues = $slotRecords->pluck('hrv');
                $slotAvg = $slotHrvValues->count() > 0 ? floor($slotHrvValues->avg()) : 0;
                $slotStatus = $this->getHrvStatus($slotAvg, $baseline_value);
                $slotData[] = [
                    "slot" => $slot["slot"],
                    "start" => $slot["start"],
                    "end" => $slot["end"],
                    "hrv" => $slotAvg,
                    "status" => $slotStatus,
                    "data" => array_values($this->transformHrvRecords($slotRecords, $baseline_value))
                ];
            }

            $weekData[] = [
                'date' => $current->toDateString(),
                'user_id' => $userId,
                'hrv' => $averageHrv,
                'status' => $status,
                'slots' => $slotData
            ];

            // Transform day records based on baseline_value to get new status
            $transformedDayRecords = $this->transformHrvRecords($dayRecords, $baseline_value);
            $transformedCollection = collect($transformedDayRecords);

            // Calculate counts based on new status from transformed records
            $lowCount = $transformedCollection->where('status', 1)->count();
            $recoveryCount = $transformedCollection->where('status', 2)->count();
            $balancedCount = $transformedCollection->where('status', 3)->count();
            $goodCount = $transformedCollection->where('status', 4)->count();

            $totalLow += $lowCount;
            $totalRecovery += $recoveryCount;
            $totalBalanced += $balancedCount;
            $totalGood += $goodCount;
            $current->addDay();
        }

        $totalCount = $totalLow + $totalRecovery + $totalBalanced + $totalGood;
        $lowPercentage = ceil($totalCount > 0 ? ($totalLow / $totalCount) * 100 : 0);
        $recoveryPercentage = ceil($totalCount > 0 ? ($totalRecovery / $totalCount) * 100 : 0);
        $balancedPercentage = ceil($totalCount > 0 ? ($totalBalanced / $totalCount) * 100 : 0);
        $goodPercentage = ceil($totalCount > 0 ? ($totalGood / $totalCount) * 100 : 0);

        $sumPercent = $lowPercentage + $recoveryPercentage + $balancedPercentage + $goodPercentage;
        if ($sumPercent > 100) {
            $overflow = $sumPercent - 100;
            $percentages = [
                'low' => $lowPercentage,
                'recovery' => $recoveryPercentage,
                'balanced' => $balancedPercentage,
                'good' => $goodPercentage,
            ];
            arsort($percentages);
            foreach ($percentages as $key => &$value) {
                if ($overflow <= 0) break;
                $deduct = min($value, $overflow);
                $value -= $deduct;
                $overflow -= $deduct;
            }
            $lowPercentage = $percentages['low'];
            $recoveryPercentage = $percentages['recovery'];
            $balancedPercentage = $percentages['balanced'];
            $goodPercentage = $percentages['good'];
        }
        return $this->sendResponse([
            'user_id' => $userId,
            'date' => $startDate->toDateString() . ' - ' . $endDate->toDateString(),
            'hrv' => $weekData,
            'average' => [
                'low' => $lowPercentage,
                'recovery' => $recoveryPercentage,
                'balanced' => $balancedPercentage,
                'good' => $goodPercentage,
            ],
        ], 'Weekly HRV data.');
    }

    public function home()
    {
        $userId = Auth::id();
        $date = request()->get('date'); // Optional date parameter

        try {
            $dateObj = $date ? Carbon::parse($date) : now(); // Use provided date or current date
        } catch (\Exception $e) {
            return $this->sendError('Invalid date format. Please use YYYY-MM-DD.');
        }

        $data['latest'] = Hrv::where('user_id', $userId)
            ->orderBy('datetime', 'desc')
            ->first();

        if ($data['latest']) {
            $quote = Quote::where('type', $data['latest']->status)
                ->inRandomOrder()
                ->value('quote');
            $data['latest']->quote = $quote;
        }

        $todayHrvData = Hrv::where('user_id', $userId)
            ->whereBetween('datetime', [$dateObj->copy()->startOfDay(), $dateObj->copy()->endOfDay()]);

        $count = $todayHrvData->count();
        $sum = $todayHrvData->sum('hrv');
        $data['baseline_value'] = UserBaseline::where('user_id', $userId)->value('baseline_value');
        $data['average'] = $count > 0 ? floor($sum / $count) : 0;

        return $this->sendResponse($data, 'HRV data with random quote based on latest HRV status.');
    }
    private function getHrvStatus(float $hrv, float $baseline_value = 0): int
    {

        /*

            Applied on 04-09-2025
            Less than 50% - Low
            Less than 15% - Recovery
            Between -15 to +15 - Balanced
            More than 15% - Good

            if ($item['hrv'] > ($baseline_value * 0.85) && $item['hrv'] < ($baseline_value * 1.15)) {
                    $item['status'] = 3; //BAlanceed
                } elseif ($item['hrv'] < ($baseline_value * 0.85) && $item['hrv'] > ($baseline_value * 0.50)) {
                    $item['status'] = 2; //Moderate
                } elseif ($item['hrv'] < ($baseline_value * 0.50)) {
                    $item['status'] = 1; //Low
                } elseif($item['hrv'] > ($baseline_value * 1.15)) {
                    $item['status'] = 4; //Good
                }


            */


        if ($baseline_value > 0) {
            if ($hrv < $baseline_value * 0.50) {
                $item['status'] = 1; // Low
            } elseif ($hrv < $baseline_value * 0.85) {
                $item['status'] = 2; // Moderate
            } elseif ($hrv <= $baseline_value * 1.15) {
                $item['status'] = 3; // Balanced
            } else {
                $item['status'] = 4; // Good
            }
            return $item['status'];
        } else {
            return match (true) {
                $hrv === 0 => 1,     // No data / undefined
                $hrv <= 15  => 1,     // Stressed
                $hrv <= 30  => 2,     // Moderate
                $hrv <= 45  => 3,     // Balanced
                default     => 4,    // Happy
            };
        }
    }

    private function transformHrvRecords($records, $baseline_value = 0): array
    {
        return $records->map(function ($item) use ($baseline_value) {

            if (floatval($baseline_value) > 0) {

                if ($item['hrv'] < $baseline_value * 0.50) {
                    $item['status'] = 1; // Low
                } elseif ($item['hrv'] < $baseline_value * 0.85) {
                    $item['status'] = 2; // Moderate
                } elseif ($item['hrv'] <= $baseline_value * 1.15) {
                    $item['status'] = 3; // Balanced
                } else {
                    $item['status'] = 4; // Good
                }
            }

            return [
                'id' => $item['id'],
                'user_id' => $item['user_id'],
                'sample_id' => $item['sample_id'],
                'datetime' => \Carbon\Carbon::parse($item['datetime'])->toDateString(), // YYYY-MM-DD
                'device_timestamp' => $item['device_timestamp'],
                'hrv' => $item['hrv'],
                'sdnn' => $item['sdnn'],
                'status' => $item['status'],
            ];
        })->toArray();
    }

    function user_baseline(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'baseline_value' => 'required|numeric',   // allows int, float, decimal
            'type' => 'required|in:1,2',              // only 1 or 2 allowed
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        $userId = Auth::id();
        $type = $request->input('type');
        $baselineValue = $request->input('baseline_value');
        $today = now()->toDateString();

        // Check if we already have a baseline for this user and type
        $existingBaseline = UserBaseline::where('user_id', $userId)
            ->first();

        // If no existing baseline, create a new one
        if (!$existingBaseline) {
            UserBaseline::create([
                'user_id' => $userId,
                'baseline_value' => $baselineValue,
                'type' => $type,
                'updated_at' => $today,
                'created_at' => $today
            ]);

            return $this->sendResponse(
                ['message' => 'Baseline created successfully'],
                'Baseline created successfully.'
            );
        }

        // If baseline exists, check if we need to update it based on the type
        $lastUpdated = Carbon::parse($existingBaseline->updated_at);
        $daysSinceLastUpdate = $lastUpdated->diffInDays(now());
        $shouldUpdate = false;

        if ($type == 1 && $daysSinceLastUpdate >= 7) {
            $shouldUpdate = true;
        } elseif ($type == 2 && $daysSinceLastUpdate >= 30) {
            $shouldUpdate = true;
        }

        if ($shouldUpdate) {
            $existingBaseline->update([
                'type' => $type,
                'baseline_value' => $baselineValue,
                'updated_at' => $today
            ]);

            return $this->sendResponse(
                ['message' => 'Baseline updated successfully'],
                'Baseline updated successfully.'
            );
        }

        return $this->sendResponse(
            ['message' => 'Baseline not updated. Update not required yet.'],
            'Baseline not updated. Update not required yet.',
            200
        );
    }
}
