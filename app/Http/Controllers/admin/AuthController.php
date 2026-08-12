<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;

class AuthController extends Controller
{
    // Admin ba sub-admin (controller) — dutoi login korte parbe admin panel e
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors(),
            ], 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid email or password',
            ], 401);
        }

        $user = Auth::user();

        if (!in_array($user->role, ['admin', 'controller'])) {
            return response()->json([
                'status'  => false,
                'message' => 'You are not authorized to access admin panel',
            ], 403);
        }

        $token = $user->createToken('admin-token')->plainTextToken;

        return response()->json([
            'status'  => 200,
            'message' => 'Login successful',
            'token'   => $token,
            'id'      => $user->id,
            'name'    => $user->name,
            'email'   => $user->email,
            'role'    => $user->role,
            'is_super_admin' => $user->isSuperAdmin(),
            'email_verified' => (bool) $user->email_verified_at,
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'  => 200,
            'message' => 'Logged out successfully',
        ]);
    }

    // Current logged-in admin/sub-admin er info — frontend e role onujayi UI dekhate lagbe
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'status' => 200,
            'data'   => [
                'id'             => $user->id,
                'name'           => $user->name,
                'email'          => $user->email,
                'role'           => $user->role,
                'is_super_admin' => $user->isSuperAdmin(),
                'email_verified' => (bool) $user->email_verified_at,
            ],
        ]);
    }

    // Notun sub-admin (role=controller) toiri kora — shudhu super admin korte parbe
    // (EnsureSuperAdmin middleware diye route level e restrict kora)
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'controller', 
        ]);

        // email verification link pathano hocche
        $user->sendEmailVerificationNotification();

        return response()->json([
            'status'  => 200,
            'message' => 'Sub-admin created successfully. A verification email has been sent.',
            'data'    => $user,
        ]);
    }

    // Shudhu super admin sub-admin (role=controller) der list dekhte parbe
    public function subAdmins()
    {
        $subAdmins = User::where('role', 'controller')->orderBy('created_at', 'DESC')->get();

        return response()->json([
            'status' => 200,
            'data'   => $subAdmins,
        ]);
    }

    // Shudhu super admin sub-admin delete korte parbe. Onno kono super admin delete kora jabe na.
    public function destroySubAdmin($id)
    {
        $user = User::findOrFail($id);

        if ($user->role !== 'controller') {
            return response()->json([
                'status'  => 403,
                'message' => 'Only sub-admin accounts can be deleted from here.',
            ], 403);
        }

        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'status'  => 200,
            'message' => 'Sub-admin deleted successfully',
        ]);
    }

    // ----- Forgot / Reset Password (Laravel er built-in Password broker) -----

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'status'  => 200,
                'message' => 'Password reset link sent to your email.',
            ]);
        }

        return response()->json([
            'status'  => 400,
            'message' => 'Unable to send reset link. Please check the email address.',
        ], 422);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();

                // password change hole purano shob session/token invalidate kora hocche
                $user->tokens()->delete();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'status'  => 200,
                'message' => 'Password has been reset successfully. Please login again.',
            ]);
        }

        return response()->json([
            'status'  => 400,
            'message' => 'This reset link is invalid or has expired.',
        ], 422);
    }

    // ----- Email Verification -----

    // Logged-in user notun kore verification email pathate chaile
    public function sendVerificationEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'status'  => 200,
                'message' => 'Email already verified.',
            ]);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json([
            'status'  => 200,
            'message' => 'Verification link sent.',
        ]);
    }

    // Signed URL theke ashe (email er link e click korle), tai route e 'signed' middleware use kora hoy
    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json([
                'status'  => 400,
                'message' => 'Invalid verification link.',
            ], 400);
        }

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return response()->json([
            'status'  => 200,
            'message' => 'Email verified successfully.',
        ]);
    }
}
