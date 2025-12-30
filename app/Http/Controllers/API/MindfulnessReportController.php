<?php

namespace App\Http\Controllers\API;

use App\Models\MindfulnessReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class MindfulnessReportController extends BaseController
{
    /**
     * Store multiple mindfulness reports
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeReports(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userId' => 'required|integer|exists:users,id',
            'reports' => 'required|array|min:1',
            'reports.*.sessionId' => 'required|string|uuid',
            'reports.*.timestamp' => 'required|date_format:Y-m-d\TH:i:s\Z',
            'reports.*.avgHR' => 'required|numeric|min:0',
            'reports.*.sdnn' => 'required|numeric|min:0',
            'reports.*.rmssd' => 'required|numeric|min:0',
            'reports.*.pnn50' => 'required|numeric|min:0',
            'reports.*.pnn20' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors(), 422);
        }

        $failedItems = [];
        $successCount = 0;

        DB::beginTransaction();

        try {
            foreach ($request->reports as $index => $reportData) {
                try {
                    MindfulnessReport::updateOrCreate(
                        ['session_id' => $reportData['sessionId']],
                        [
                            'user_id' => $request->userId,
                            'timestamp' => $reportData['timestamp'],
                            'avg_hr' => $reportData['avgHR'],
                            'sdnn' => $reportData['sdnn'],
                            'rmssd' => $reportData['rmssd'],
                            'pnn50' => $reportData['pnn50'],
                            'pnn20' => $reportData['pnn20'],
                        ]
                    );
                    $successCount++;
                } catch (\Exception $e) {
                    $failedItems[] = [
                        'index' => $index,
                        'sessionId' => $reportData['sessionId'],
                        'error' => $e->getMessage()
                    ];
                }
            }

            DB::commit();

            $responseData = [
                'failedItems' => $failedItems,
                'successCount' => $successCount
            ];

            return $this->sendResponse($responseData, 'Reports stored successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->sendError('Failed to store reports', $e->getMessage(), 500);
        }
    }

    /**
     * Fetch mindfulness reports by user and timestamp
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchReportsByTimestamp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userId' => 'required|integer|exists:users,id',
            'timestamp' => 'required|min:0',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors(), 422);
        }

        try {
            $query = MindfulnessReport::where('user_id', $request->userId);

            // If timestamp != 0, filter a specific timestamp
            // Else, fetch all reports greater than timestamp received
            if ($request->timestamp != 0) {
                $mysqlDate = \Carbon\Carbon::createFromTimestamp(
                    $request->timestamp
                )->toDateTimeString();

                $query->where('timestamp', '>', $mysqlDate);
            }

            $reports = $query->get()->map(function ($report) {
                return [
                    'sessionId' => $report->session_id,
                    'timestamp' => $report->timestamp->timestamp,    // Carbon cast
                    'avgHR' => $report->avg_hr,
                    'sdnn' => $report->sdnn,
                    'rmssd' => $report->rmssd,
                    'pnn50' => $report->pnn50,
                    'pnn20' => $report->pnn20,
                    'userID' => $report->user_id,
                ];
            });

            return $this->sendResponse(
                ['reports' => $reports],
                $reports->isEmpty() ? 'No reports found' : 'Reports retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->sendError('Failed to fetch reports', $e->getMessage(), 500);
        }
    }
}
