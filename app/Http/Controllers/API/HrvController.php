<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Models\Hrv;
use App\Models\Quote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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

        $hrvs = Hrv::where('user_id', $userId)
            ->whereBetween('datetime', [$startDate, $endDate])
            ->orderBy('datetime', 'asc')
            ->get();

        if ($frequency === 'weekly') {
            $weekData = [];

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

                $current->addDay();
            }

            return $this->sendResponse($weekData, 'Weekly HRV data (Monday to Sunday).');
        }

        return $this->sendResponse($this->transformHrvRecords($hrvs), 'Daily HRV data.');
    }

    public function home()
    {
        $userId = Auth::id();

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
            ->whereBetween('datetime', [now()->startOfDay(), now()->endOfDay()]);

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
