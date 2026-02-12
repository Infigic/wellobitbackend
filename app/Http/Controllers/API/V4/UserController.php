<?php

namespace App\Http\Controllers\API\V4;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends BaseController
{
    public function getUserDetail(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $personalisedDataStored = !empty($user->age) || !empty($user->gender) || !empty($user->activity_level) || !empty($user->reason);

            $trialStatus = 'NotStarted';
            $tracking = $user->tracking;

            if ($tracking && $tracking->trial_started_at) {
                if ($tracking->trial_ends_at && now()->greaterThan($tracking->trial_ends_at)) {
                    $trialStatus = 'Expired';
                } else {
                    $trialStatus = 'Active';
                }
            }

            $userData = $user->makeHidden(['tracking'])->toArray();
            $userData['personalised_data_stored'] = $personalisedDataStored;
            $userData['trialStatus'] = $trialStatus;

            return $this->sendResponse($userData, 'Profile detail retrieved successfully.');
        } else {
            return $this->sendError('Invalid request', ['error' => 'User not found']);
        }
    }

    public function updateUser(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            ]);
        }
        // Update profile image if provided
        if ($request->hasFile('profile_image')) {
            // Delete old image if exists
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }

            // Store new image
            $path = $request->file('profile_image')->store('profile_images', 'public');
            $validated['profile_image'] = $path;
        }

        // Update only name and profile image
        $user->update([
            'name' => $validated['name'] ?? $user->name,
            'profile_image' => $validated['profile_image'] ?? $user->profile_image,
        ]);
        $user->save();

        return $this->sendResponse($user, 'Profile updated successfully.');
    }
}
