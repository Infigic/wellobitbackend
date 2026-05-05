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
            'hrv_uuid' => 'required|exists:hrvs,sample_id',
            'mood' => 'nullable|string',
            'activity' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $userId = Auth::id();

        $hrv = Hrv::where('sample_id', $request->hrv_uuid)
            ->where('user_id', $userId)
            ->first();

        if (!$hrv) {
            return $this->sendError('HRV not found or not belongs to user', [], 404);
        }

        $log = HrvLog::updateOrCreate(
            [
                'hrv_uuid' => $request->hrv_uuid,
                'user_id' => $userId,
            ],
            [
                'hrv_id' => $hrv->id,
                'mood' => $request->mood,
                'activity' => $request->activity,
                'notes' => $request->notes,
            ]
        );

        return $this->sendResponse($log, 'HRV log saved successfully.');
    }
}