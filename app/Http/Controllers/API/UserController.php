<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends BaseController
{
    public function getUserDetail(Request $request)
    {
        $user = $request->user();

        if ($user) {
            return $this->sendResponse($user, 'Profile detail retrieved successfully.');
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


    /**
     * Store user details
     */
    public function storeUserDetails(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return $this->sendError('User not found', ['error' => 'User not found'], 401);
        }

        
        $genderMap = config('user.gender_map');
        $activityMap = config('user.activity_map');
        $reasonMap = config('user.reason_map');

        $validator = Validator::make($request->all(), [
            'age' => 'nullable|integer|min:0|max:120',
            'gender' => ['nullable', Rule::in(array_keys($genderMap))],
            'activityLevel' => ['nullable', Rule::in(array_keys($activityMap))],
            'reason' => 'nullable|array|min:1|max:5',
            'reason.*' => Rule::in(array_keys($reasonMap)),
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $data = $validator->validated();

        if (isset($data['gender'])) {
            if (!isset($genderMap[$data['gender']])) {
                return $this->sendError('Invalid gender value', ['error' => 'Invalid gender value'], 422);
            }
            $data['gender'] = $genderMap[$data['gender']];
        }

        if (isset($data['activityLevel'])) {
            if (!isset($activityMap[$data['activityLevel']])) {
                return $this->sendError('Invalid activity level value', ['error' => 'Invalid activity level value'], 422);
            }
            $data['activity_level'] = $activityMap[$data['activityLevel']];
            unset($data['activityLevel']);
        }

        if (isset($data['reason'])) {
            $data['reason'] = collect($data['reason'])
                ->map(fn($item) => $reasonMap[$item])
                ->unique()
                ->values()
                ->toArray();
        }

        if (empty($data)) {
            return $this->sendError('No data to update', [], 422);
        }

        try {
            $updated = $user->update($data);

            if (!$updated) {
                return $this->sendError('Failed to update user details', ['error' => 'Unable to update user details.'], 500);
            }

            $user->refresh();

            return $this->sendResponse($user, 'User details stored successfully.');

        } catch (\Exception $e) {
            return $this->sendError('An error occurred while updating user details', ['error' => $e->getMessage()], 500);
        }
    }
}
