<?php

namespace App\Http\Controllers\API;

use App\Models\Hrv;
use App\Models\HrvLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class HrvLogController extends BaseController
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hrv_uuid' => 'required|string',
            'mood'     => 'nullable|string',
            'activity' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $userId = Auth::id();

        $existingLog = HrvLog::where('hrv_uuid', $request->hrv_uuid)->first();

        if ($existingLog) {
            if ($existingLog->user_id !== $userId) {
                return $this->sendError('This HRV record has already been used by another user.', [], 403);
            }

            $existingLog->update([
                'mood'     => $request->mood,
                'activity' => $request->activity,
                'notes'    => $request->notes,
            ]);

            return $this->sendResponse($existingLog, 'HRV log updated successfully.');
    }

    $log = HrvLog::create([
        'hrv_uuid' => $request->hrv_uuid,
        'user_id'  => $userId,
        'mood'     => $request->mood,
        'activity' => $request->activity,
        'notes'    => $request->notes,
    ]);

    return $this->sendResponse($log, 'HRV log created successfully.');
}

    public function index(Request $request)
    {
        $userId = Auth::id();

        $logs = HrvLog::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($log) {
                return [
                    'log_id'     => 'log_' . $log->id,
                    'hrv_uuid'   => $log->hrv_uuid,
                    'mood'       => $log->mood,
                    'activity'   => $log->activity,
                    'notes'      => $log->notes,
                    'created_at' => $log->created_at,
                ];
            });

        return $this->sendResponse($logs, 'HRV logs retrieved successfully.');
    }

    public function destroy($hrv_uuid)
    {
        $deleted = HrvLog::where('hrv_uuid', $hrv_uuid)
            ->where('user_id', Auth::id())
            ->delete();

        if (!$deleted) {
            return $this->sendError('Log not found or not belongs to user.', [], 404);
        }

        return $this->sendResponse([], 'HRV log deleted successfully.');
    }
}
