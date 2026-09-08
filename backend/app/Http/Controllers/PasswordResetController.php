<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class PasswordResetController extends Controller
{
    public function forgot(Request $request)
    {
        $v = Validator::make($request->all(), [
            'identifier' => ['required','string','max:190'],
        ]);
        if ($v->fails()) {
            return response()->json(['message'=>'Enter your registered email or phone number.'], 422);
        }

        $identifier = trim($request->input('identifier'));
        $user = User::where('email',$identifier)->orWhere('phone',$identifier)->first();

        // Do not reveal whether an account exists.
        if (!$user) {
            return response()->json(['message'=>'If the account exists, a reset code will be sent.']);
        }

        // Store a short-lived one-time reset token using Laravel's password broker.
        // The application's mail/SMS notification provider must be configured in production.
        $status = Password::sendResetLink(['email'=>$user->email]);

        return response()->json([
            'message' => 'If the account exists, a reset code or reset link will be sent.'
        ], $status === Password::RESET_LINK_SENT ? 200 : 422);
    }

    public function reset(Request $request)
    {
        $v = Validator::make($request->all(), [
            'email' => ['required','email'],
            'token' => ['required','string'],
            'password' => ['required','string','min:8','confirmed'],
        ]);
        if ($v->fails()) {
            return response()->json(['message'=>'Invalid password reset request.'], 422);
        }

        $status = Password::reset(
            $request->only('email','password','password_confirmation','token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message'=>'Password reset successful.'])
            : response()->json(['message'=>'The reset token is invalid or expired.'], 422);
    }
}
