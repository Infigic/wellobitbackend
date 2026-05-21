<?php

namespace App\Http\Controllers\API\V4;

use Illuminate\Http\Request;

class UserEarnedBadgeController extends BaseController
{
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'badge_id'       => 'required|integer',
            'badge_title'    => 'required|string|max:255',
            'badge_subtitle' => 'nullable|string|max:255',
            'earned_at'      => 'required|date',
        ]);

        $badge = $user->earnedBadges()->create($validated);

        return $this->sendResponse($badge, 'Badge stored successfully.');
    }
}
