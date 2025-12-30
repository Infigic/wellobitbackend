<?php

namespace App\Http\Controllers\API\V3;

use App\Models\Hrv;
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

        foreach ($request->input('data') as $hrvData) {
            $hrvData['user_id'] = $userId;
            $hrvData['status'] = $this->getHrvStatus($hrvData['hrv']);
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

        if ($frequency === 'daily') {
            return $this->getDailyHrvData($hrvs, $userId, $startDate, $baseline_value);
        } else {
            return $this->getWeeklyHrvData($hrvs, $userId, $startDate, $endDate, $baseline_value);
        }
    }

    private function getDailyHrvData($hrvs, $userId, $startDate, $baseline_value)
    {
        // Transform HRV records based on baseline_value to get new status
        $transformedHrvs = $this->transformHrvRecords($hrvs, $baseline_value);
        
        // Convert back to collection for easier manipulation
        $transformedCollection = collect($transformedHrvs);
        
        // Calculate counts based on new status from transformed records
        $stressedCount = $transformedCollection->where('status', 3)->count(); // Stressed
        $balancedCount = $transformedCollection->where('status', 2)->count(); // Balanced
        $happyCount = $transformedCollection->where('status', 1)->count(); // Happy

        $totalCount = $stressedCount + $balancedCount + $happyCount;
        $stressedPercentage = ceil($totalCount > 0 ? ($stressedCount / $totalCount) * 100 : 0);
        $balancedPercentage = ceil($totalCount > 0 ? ($balancedCount / $totalCount) * 100 : 0);
        $happyPercentage = ceil($totalCount > 0 ? ($happyCount / $totalCount) * 100 : 0);

        $sumPercent = $stressedPercentage + $balancedPercentage + $happyPercentage;
        if ($sumPercent > 100) {
            $overflow = $sumPercent - 100;

            $percentages = [
                'stressed' => $stressedPercentage,
                'balanced' => $balancedPercentage,
                'happy' => $happyPercentage,
            ];
            arsort($percentages);
            foreach ($percentages as $key => &$value) {
                if ($overflow <= 0) break;
                $deduct = min($value, $overflow);
                $value -= $deduct;
                $overflow -= $deduct;
            }
            $stressedPercentage = $percentages['stressed'];
            $balancedPercentage = $percentages['balanced'];
            $happyPercentage = $percentages['happy'];
        }

        return $this->sendResponse([
            'user_id' => $userId,
            'date' => $startDate->toDateString(),
            'hrv' => $transformedHrvs,
            'average' => [
                'stressed' => $stressedPercentage,
                'balanced' => $balancedPercentage,
                'happy' => $happyPercentage,
            ],
        ], 'Daily HRV data.');
    }

    private function getWeeklyHrvData($hrvs, $userId, $startDate, $endDate, $baseline_value)
    {
        $totalStressed = 0;
        $totalBalanced = 0;
        $totalHappy = 0;
        $weekData = [];
        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            $dayRecords = $hrvs->filter(function ($item) use ($current) {
                return Carbon::parse($item->datetime)->isSameDay($current);
            });
            $hrvValues = $dayRecords->pluck('hrv');
            $averageHrv = $hrvValues->count() > 0 ? floor($hrvValues->avg()) : 0;
            $status = $this->getHrvStatus($averageHrv);

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
                $slotStatus = $this->getHrvStatus($slotAvg);
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
            $stressedCount = $transformedCollection->where('status', 3)->count();
            $balancedCount = $transformedCollection->where('status', 2)->count();
            $happyCount = $transformedCollection->where('status', 1)->count();
            
            $totalStressed += $stressedCount;
            $totalBalanced += $balancedCount;
            $totalHappy += $happyCount;
            $current->addDay();
        }
        
        $totalCount = $totalStressed + $totalBalanced + $totalHappy;
        $stressedPercentage = ceil($totalCount > 0 ? ($totalStressed / $totalCount) * 100 : 0);
        $balancedPercentage = ceil($totalCount > 0 ? ($totalBalanced / $totalCount) * 100 : 0);
        $happyPercentage = ceil($totalCount > 0 ? ($totalHappy / $totalCount) * 100 : 0);
        
        $sumPercent = $stressedPercentage + $balancedPercentage + $happyPercentage;
        if ($sumPercent > 100) {
            $overflow = $sumPercent - 100;
            $percentages = [
                'stressed' => $stressedPercentage,
                'balanced' => $balancedPercentage,
                'happy' => $happyPercentage,
            ];
            arsort($percentages);
            foreach ($percentages as $key => &$value) {
                if ($overflow <= 0) break;
                $deduct = min($value, $overflow);
                $value -= $deduct;
                $overflow -= $deduct;
            }
            $stressedPercentage = $percentages['stressed'];
            $balancedPercentage = $percentages['balanced'];
            $happyPercentage = $percentages['happy'];
        }
        return $this->sendResponse([
            'user_id' => $userId,
            'date' => $startDate->toDateString() . ' - ' . $endDate->toDateString(),
            'hrv' => $weekData,
            'average' => [
                'stressed' => $stressedPercentage,
                'balanced' => $balancedPercentage,
                'happy' => $happyPercentage,
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

        $data['average'] = $count > 0 ? floor($sum / $count) : 0;

        return $this->sendResponse($data, 'HRV data with random quote based on latest HRV status.');
    }
    private function getHrvStatus(int $hrv): int
    {
        return match (true) {
            $hrv === 0 => 0,     // No data / undefined
            $hrv < 20  => 3,     // Stressed
            $hrv < 50  => 2,     // Balanced
            default     => 1,    // Happy
        };
    }
    //    private function transformHrvRecords($records,$baseline_value=0): array
    //    {
    //        return $records->map(function ($item) {
    //          
    //            if($baseline_value > 0){
    //              if($item['hrv'] < ($baseline_value * 0.85)){
    //                $item['status'] = 1;
    //              }
    //              elseif($item['hrv'] < ($baseline_value * 1.15)){
    //                $item['status'] = 3;
    //              }
    //              else{
    //                $item['status'] = 2;
    //              }              
    //            }
    //            return [
    //                'id' => $item['id'],
    //                'user_id' => $item['user_id'],
    //                'sample_id' => $item['sample_id'],
    //                'datetime' => \Carbon\Carbon::parse($item['datetime'])->toDateString(), // YYYY-MM-DD
    //                'device_timestamp' => $item['device_timestamp'],
    //                'hrv' => $item['hrv'],
    //                'sdnn' => $item['sdnn'],
    //                'status' => $item['status'],
    //            ];
    //        })->toArray();
    //    }

    private function transformHrvRecords($records, $baseline_value = 0): array
    {
        return $records->map(function ($item) use ($baseline_value) {

            // Default status if baseline not set
//            $item['status'] = 0;
            
            if (floatval($baseline_value) > 0) {
                if ($item['hrv'] < ($baseline_value * 0.85)) {
                    $item['status'] = 3;
                } elseif ($item['hrv'] > ($baseline_value * 1.15)) {
                    $item['status'] = 1;
                } else {
                    $item['status'] = 2;
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
}
