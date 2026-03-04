<?php 
namespace App\Http\Controllers\API\V1;


use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserController extends BaseController
{
    public function deleteUser(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return $this->sendError('Invalid request', ['error' => 'User not found'], 404);
        }

        // Revoke all tokens
        $user->tokens()->delete();

        // Anonymize personal data
        $user->update([
            'name' => 'Deleted User',
            'email' => 'deleted_' . $user->id . '_' . time() . '@deleted.com',
            'profile_image' => null,
            'password' => bcrypt(Str::random(32)),
            'remember_token' => null,
            'provider_id' => null,
            'otp' => null,
            'otp_expires_at' => null,
            'age' => null,
            'gender' => null,
            'activity_level' => null,
            'reason' => null,
        ]);

        // Soft delete user
        $user->delete();

        return $this->sendResponse(null, 'User deleted successfully.');
    }
}