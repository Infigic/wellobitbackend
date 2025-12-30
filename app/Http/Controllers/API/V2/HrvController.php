<?php

namespace App\Http\Controllers\API\V2;

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

        
        $ordering_hrv = ($frequency === 'daily')?'desc':'asc';
        
        
        $hrvs = Hrv::where('user_id', $userId)
            ->whereBetween('datetime', [$startDate, $endDate])
            ->groupBy('sample_id')
            ->orderBy('datetime', $ordering_hrv)
            ->get();

        if ($frequency === 'daily') {
            $stressedSum = $hrvs->where('status', 3)->sum('hrv'); // Stressed
            $balancedSum = $hrvs->where('status', 2)->sum('hrv'); // Balanced
            $happySum = $hrvs->where('status', 1)->sum('hrv'); // Happy

            $totalSum = $stressedSum + $balancedSum + $happySum;
            $stressedPercentage = ceil($totalSum > 0 ? ($stressedSum / $totalSum) * 100 : 0);
            $balancedPercentage = ceil($totalSum > 0 ? ($balancedSum / $totalSum) * 100 : 0);
            $happyPercentage = ceil($totalSum > 0 ? ($happySum / $totalSum) * 100 : 0);

            $sumPercent = $stressedPercentage + $balancedPercentage + $happyPercentage;
            if ($sumPercent > 100) {
                $overflow = $sumPercent - 100;

                // Create array to find max and reduce from it
                $percentages = [
                    'stressed' => $stressedPercentage,
                    'balanced' => $balancedPercentage,
                    'happy' => $happyPercentage,
                ];

                // Sort descending to reduce the highest one first
                arsort($percentages);

                foreach ($percentages as $key => &$value) {
                    if ($overflow <= 0) break;

                    $deduct = min($value, $overflow);
                    $value -= $deduct;
                    $overflow -= $deduct;
                }

                // Assign corrected values back
                $stressedPercentage = $percentages['stressed'];
                $balancedPercentage = $percentages['balanced'];
                $happyPercentage = $percentages['happy'];
            }

            return $this->sendResponse([
                'user_id' => $userId,
                'date' => $startDate->toDateString(),
                'hrv' => $this->transformHrvRecords($hrvs),
                'average' => [
                    'stressed' => $stressedPercentage,
                    'balanced' => $balancedPercentage,
                    'happy' => $happyPercentage,
                ],
            ], 'Daily HRV data.');
        } else {
            $totalStressed = 0;
            $totalBalanced = 0;
            $totalHappy = 0;

            // Generate each day of the week (Monday to Sunday)
            $current = $startDate->copy();
            while ($current->lte($endDate)) {

                $dayRecords = $hrvs->filter(function ($item) use ($current) {
                    return Carbon::parse($item->datetime)->isSameDay($current);
                });

                $hrvValues = $dayRecords->pluck('hrv');
                $averageHrv = $hrvValues->count() > 0 ? floor($hrvValues->avg()) : 0;
                $status = $this->getHrvStatus($averageHrv);

                $weekData[] = [
                    'user_id' => $userId,
                    'date' => $current->toDateString(),
                    'hrv' => $averageHrv,
                    'status' => $status,
                ];


                $stressedSum = $dayRecords->where('status', 3)->sum('hrv'); // Stressed
                $balancedSum = $dayRecords->where('status', 2)->sum('hrv'); // Balanced
                $happySum = $dayRecords->where('status', 1)->sum('hrv'); // Happy

                $totalStressed += $stressedSum;
                $totalBalanced += $balancedSum;
                $totalHappy += $happySum;

                $current->addDay();
            }

            $totalSum = $totalStressed + $totalBalanced + $totalHappy;
            $stressedPercentage = ceil($totalSum > 0 ? ($totalStressed / $totalSum) * 100 : 0);
            $balancedPercentage = ceil($totalSum > 0 ? ($totalBalanced / $totalSum) * 100 : 0);
            $happyPercentage = ceil($totalSum > 0 ? ($totalHappy / $totalSum) * 100 : 0);

            $sumPercent = $stressedPercentage + $balancedPercentage + $happyPercentage;
            if ($sumPercent > 100) {
                $overflow = $sumPercent - 100;

                // Create array to find max and reduce from it
                $percentages = [
                    'stressed' => $stressedPercentage,
                    'balanced' => $balancedPercentage,
                    'happy' => $happyPercentage,
                ];

                // Sort descending to reduce the highest one first
                arsort($percentages);

                foreach ($percentages as $key => &$value) {
                    if ($overflow <= 0) break;

                    $deduct = min($value, $overflow);
                    $value -= $deduct;
                    $overflow -= $deduct;
                }

                // Assign corrected values back
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
    private function transformHrvRecords($records): array
    {
        return $records->map(function ($item) {
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
