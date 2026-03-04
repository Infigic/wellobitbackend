<?php 
namespace App\Http\Controllers\API\V1;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends BaseController
{
    public function deleteUser(Request $request)
    {
        $user = $request->user();
        if ($user) {
            echo 1;
            $user->tokens()->delete(); 
            $user->delete();
            return $this->sendResponse(null, 'User deleted successfully.');
        } else {
            return $this->sendError('Invalid request', ['error' => 'User not found']);
        }

    }
}