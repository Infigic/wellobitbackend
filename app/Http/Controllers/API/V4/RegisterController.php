<?php

namespace App\Http\Controllers\API\V4;

use App\Mail\OtpMail;
use App\Models\User;
use Carbon\Carbon;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

class RegisterController extends BaseController
{

  /**
   * Register api
   *
   * @return \Illuminate\Http\Response
   */
  public function register(Request $request)
  {
    $platform = $request->input('platform', 'simple');

    if (!in_array($platform, ['simple', 'google', 'apple'])) {
      return $this->sendError('Invalid platform specified.', [], 422);
    }

    // ----------------------------
    // SIMPLE REGISTER FLOW
    // ----------------------------
    if ($platform === 'simple') {

      $user = User::where('email', $request->email)->first();
      if ($user && $user->platform != 'simple') {
        $pform = ucfirst($user->platform);
        $msg = "You are registered with $pform. Please login with the {$pform} option";
        return $this->sendError($msg, $msg, 422);
      }

      $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users,email',
        'password' => 'required|string|min:6|confirmed',
      ]);

      if ($validator->fails()) {
        return $this->sendError('Validation Error.', $validator->errors(), 422);
      }

      $otp = rand(1001, 9998);
      $expiresAt = Carbon::now()->addMinutes(60);

      $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'otp' => $otp,
        'otp_expires_at' => $expiresAt,
        'is_active' => false,
        'role' => 'customer',
        'platform' => 'simple',
      ]);

      $this->sendOtpEmail($user->email, $otp, $user->name);

      return $this->sendResponse([], 'Registered successfully. Please verify your email with the OTP sent.');
    }

    // ----------------------------
    // SOCIAL LOGIN (GOOGLE / APPLE)
    // ----------------------------
    $validator = Validator::make($request->all(), [
      'token' => 'required|string',
      'name' => 'nullable|string|max:255',
      'email' => 'nullable|email|max:255',
    ]);

    if ($validator->fails()) {
      return $this->sendError('Validation Error.', $validator->errors(), 422);
    }

    try {
      $decodedToken = $this->decodeIdToken($request->token, $platform);
      $providerId = $decodedToken->sub ?? null;

      if (!$providerId) {
        return $this->sendError('Invalid token: Missing provider ID.', [], 401);
      }
    } catch (\Exception $e) {
      return $this->sendError('Token validation failed: ' . $e->getMessage(), [], 401);
    }

    $email = $decodedToken->email ?? null;
    $name = $request->name ?? 'Guest';

    // 1. Match by provider_id 
    $user = User::where('provider_id', $providerId)
      ->where('platform', $platform)
      ->first();

    // Relogin with same platform and found name and email
    if ($user) {
      $update_user = [
        'provider_id' => $providerId,
        'platform' => $platform,
      ];

      if ($request->name) {
        $update_user['name'] = $request->name;
      }
      if ($request->email) {
        $update_user['email'] = $request->email;
      }

      $user->update($update_user);
    }

    // 2. Fallback match by email if provider_id not found 
    if (!$user && $email) {
      $user = User::where('email', $email)->first();

      // Update if email found but no provider_id
      if ($user && !$user->provider_id) {
        $update_user = [
          'provider_id' => $providerId,
          'platform' => $platform,
          'email_verified_at' => now(),
          'is_active' => true,
        ];

        if ($request->name) {
          $update_user['name'] = $request->name;
        }
        if ($request->email) {
          $update_user['email'] = $request->email;
        }
        //        dd($update_user);
        $user->update($update_user);
      }
    }

    // 3. Still not found? Create new user
    if (!$user) {

      $user = User::create([
        'name' => $name,
        'email' => $email, // could be null
        'provider_id' => $providerId,
        'platform' => $platform,
        'password' => Hash::make(Str::random(32)),
        'email_verified_at' => now(),
        'is_active' => true,
        'role' => 'customer',
      ]);
    }

    $token = $user->createToken('Aayoo')->plainTextToken;

    return $this->sendResponse([
      'token' => $token,
      'name' => $user->name,
      'email' => $user->email,
    ], ucfirst($platform) . ' login/register successful.');
  }

  /**
   * Login api
   *
   * @return \Illuminate\Http\Response
   */
  public function login(Request $request)
  {
    // 1. Validate Input
    $validator = Validator::make($request->all(), [
      'email' => 'required|string|email',
      'password' => 'required|string',
    ]);

    if ($validator->fails()) {
      return $this->sendError('Validation Error.', $validator->errors(), 422);
    }

    // 2. Attempt to fetch user manually to add custom checks
    $user = User::where('email', $request->email)->first();

    // 3. User existence check
    if (!$user) {
      return $this->sendError('Unauthorised.', ['error' => 'Invalid credentials'], 401);
    }

    if ($user->role !== 'customer') {
      return $this->sendError('Unauthorised.', ['error' => 'Only customers can log in via API.']);
    }

    // 4. Password match check
    if (!Hash::check($request->password, $user->password)) {
      return $this->sendError('Unauthorised.', ['error' => 'Invalid credentials'], 401);
    }

    // 5. Soft-deleted check
    if ($user->trashed()) {
      return $this->sendError('Unauthorised.', ['error' => 'This account has been deactivated.'], 403);
    }

    // 6. Email verification check
    if (is_null($user->email_verified_at)) {
      return $this->sendError('Unauthorised.', ['error' => 'Please verify your email.'], 403);
    }

    // 7. Active status check
    if (!$user->is_active) {
      return $this->sendError('Unauthorised.', ['error' => 'Account is not active.'], 403);
    }

    // 8. Create token & respond
    $token = $user->createToken('Aayoo')->plainTextToken;

    $data = [
      'token' => $token,
      'name' => $user->name,
      'email' => $user->email,
    ];

    return $this->sendResponse($data, 'User logged in successfully.');
  }

  public function logout(Request $request)
  {
    $accessToken = $request->bearerToken();

    if (!$accessToken) {
      return $this->sendError('Unauthorised.', ['error' => 'Token not provided.'], 401);
    }

    $token = PersonalAccessToken::findToken($accessToken);

    if (!$token || !$token->tokenable) {
      return $this->sendError('Unauthorised.', ['error' => 'Invalid or expired token.'], 401);
    }

    // Delete the current access token
    $token->delete();

    return $this->sendResponse([], 'User logout successfully.');
  }

  public function verifyOtp(Request $request)
  {
    //      dd(now());
    $validator = Validator::make($request->all(), [
      'email' => 'required|email|exists:users,email',
      'otp' => 'required|digits:4',
    ]);

    if ($validator->fails()) {
      return $this->sendError('Validation Error.', $validator->errors(), 422);
    }

    $user = User::where('email', $request->email)->first();

    if (!$user) {
      return $this->sendError('User not found.', [], 404);
    }

    if ($user->is_active && $user->email_verified_at) {
      return $this->sendError('Already Verified.', ['error' => 'This email has already been verified.'], 400);
    }

    if ($user->otp !== $request->otp) {
      return $this->sendError('Invalid OTP.', ['error' => 'The OTP you entered is incorrect.'], 401);
    }

    if (Carbon::now()->gt($user->otp_expires_at)) {
      return $this->sendError('OTP Expired.', ['error' => 'The OTP has expired. Please request a new one.'], 401);
    }

    // Verification success
    $user->update([
      'is_active' => true,
      'email_verified_at' => now(),
      'otp' => null,
      'otp_expires_at' => null,
    ]);

    $token = $user->createToken('Aayoo')->plainTextToken;

    return $this->sendResponse([
      'token' => $token,
      'name' => $user->name,
      'email' => $user->email,
    ], 'Email verified successfully. You are now logged in.');
  }

  public function resendOtp(Request $request)
  {
    $request->validate([
      'email' => 'required|email|exists:users,email',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user) {
      return $this->sendError('User not found.');
    }

    if ($user->is_active && $user->email_verified_at) {
      return $this->sendError('Already Verified.', ['error' => 'This email is already verified.']);
    }

    // If existing OTP is still valid, reuse it
    if ($user->otp && $user->otp_expires_at && Carbon::now()->lt($user->otp_expires_at)) {
      $this->sendOtpEmail($user->email, $user->otp, $user->name);

      return $this->sendResponse([], 'OTP resent successfully (existing OTP still valid).');
    }

    // Generate a new OTP
    $otp = rand(1001, 9998);
    $expiresAt = Carbon::now()->addMinutes(60);

    $user->update([
      'otp' => $otp,
      'otp_expires_at' => $expiresAt,
    ]);

    $this->sendOtpEmail($user->email, $otp, $user->name);

    return $this->sendResponse([], 'A new OTP has been sent to your email.');
  }

  public static function sendOtpEmail($email, $otp, $name)
  {
     Mail::to($email)->send(new OtpMail($name, $otp));
  }

  protected function decodeIdToken(string $idToken, string $platform): object
  {
    try {
      $keysUrl = match ($platform) {
        'google' => 'https://www.googleapis.com/oauth2/v3/certs',
        'apple' => 'https://appleid.apple.com/auth/keys',
        default => throw new \InvalidArgumentException("Unsupported platform: {$platform}"),
      };

      $response = Http::timeout(10)
        ->retry(2, 100)
        ->get($keysUrl);

      if (!$response->successful()) {
        throw new \RuntimeException('Failed to fetch JWK keys: ' . $response->status());
      }

      $keys = $response->json();

      if (empty($keys)) {
        throw new \RuntimeException("No JWK keys received from {$platform}");
      }

      return JWT::decode(
        $idToken,
        JWK::parseKeySet($keys, 'RS256')
      );
    } catch (\Throwable $e) {
      // Log the error in a production environment
      \Log::error('Token decode failed: ' . $e->getMessage());
      throw new \Exception('Unable to decode ID token: ' . $e->getMessage());
    }
  }

  /**
   * Send password reset OTP
   *
   * @param Request $request
   * @return \Illuminate\Http\Response
   */
  public function forgetPassword(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'email' => 'required|email|exists:users,email'
    ]);

    if ($validator->fails()) {
      return $this->sendError('Validation Error.', $validator->errors(), 422);
    }

    $user = User::where('email', $request->email)->first();

    if (!$user) {
      return $this->sendError('User not found with the provided email.', [], 404);
    }

    // Active status check
    if (!$user->is_active) {
      return $this->sendError('Unauthorised.', ['error' => 'Account is not active.'], 403);
    }

    $otp = rand(1001, 9998);
    $expiresAt = Carbon::now()->addMinutes(60);

    $user->update([
      'otp' => $otp,
      'otp_expires_at' => $expiresAt
    ]);

    $this->sendOtpEmail($user->email, $otp, $user->name);
    return $this->sendResponse(['id' => $user->id], 'Password reset OTP sent successfully. Please check your email.');
  }

  /**
   * Reset user password
   *
   * @param Request $request
   * @return \Illuminate\Http\Response
   */
  public function resetPassword(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'id' => 'required|integer|exists:users,id',
      'password' => 'required|string|min:6',
      'password_confirmation' => 'required|string|same:password'
    ]);

    if ($validator->fails()) {
      return $this->sendError('Validation Error.', $validator->errors(), 422);
    }

    $user = User::find($request->id);

    if (!$user) {
      return $this->sendError('User not found.', [], 404);
    }



    // Update password and clear OTP
    $user->update([
      'password' => Hash::make($request->password),
      'otp' => null,
      'otp_expires_at' => null,
      'otp_verified_at' => null
    ]);

    return $this->sendResponse([], 'Password has been reset successfully.');
  }

  /**
   * Verify OTP for password reset
   *
   * @param Request $request
   * @return \Illuminate\Http\Response
   */
  public function verifyResetOtp(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'id' => 'required|integer|exists:users,id',
      'otp' => 'required|digits:4',
    ]);

    if ($validator->fails()) {
      return $this->sendError('Validation Error.', $validator->errors(), 422);
    }

    $user = User::find($request->id);

    if (!$user) {
      return $this->sendError('User not found.', [], 404);
    }

    // Verify OTP and check if it's expired
    if ($user->otp !== $request->otp) {
      return $this->sendError('Invalid OTP.', [], 422);
    }

    if ($user->otp_expires_at < Carbon::now()) {
      return $this->sendError('OTP has expired. Please request a new one.', [], 422);
    }

    // Update user to indicate OTP verification success
    $user->update([
      'otp_verified_at' => Carbon::now()
    ]);

    return $this->sendResponse(['id' => $user->id], 'OTP verified successfully. You can now reset your password.');
  }
  
  public function sendTestMail(){
//       Mail::to('veerl1506@gmail.com')->send(new OtpMail("Viral", "2356"));
       Mail::to('pratik.gothaliya@gmail.com')->send(new OtpMail("Pratik", "2356"));
  }
}
