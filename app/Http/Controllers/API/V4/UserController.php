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
}
