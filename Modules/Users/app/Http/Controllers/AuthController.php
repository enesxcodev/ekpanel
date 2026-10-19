<?php

namespace Modules\Users\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Modules\Users\Services\UsersFrontendService;

class AuthController extends Controller
{
    public function login(Request $request, UsersFrontendService $users): JsonResponse
    {
        return response()->json(['user' => $users->login($request)]);
    }

    public function logout(Request $request, UsersFrontendService $users): JsonResponse
    {
        $users->logout($request);

        return response()->json(['status' => 'logged-out']);
    }

    public function current(UsersFrontendService $users): JsonResponse
    {
        return response()->json(['user' => $users->current()]);
    }

    public function sendVerification(UsersFrontendService $users): JsonResponse
    {
        return response()->json(['sent' => $users->sendEmailVerification()]);
    }

    public function verify(Request $request, UsersFrontendService $users): JsonResponse
    {
        $users->verifyEmail($request);

        return response()->json(['status' => 'email-verified']);
    }

    public function forgotPassword(Request $request, UsersFrontendService $users): JsonResponse
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:255']]);

        return response()->json(['status' => $users->sendPasswordResetLink($data['email'], $request)]);
    }

    public function resetContext(Request $request, string $token): JsonResponse
    {
        return response()->json(['token' => $token, 'email' => $request->query('email')])
            ->header('Cache-Control', 'no-store')->header('Referrer-Policy', 'no-referrer');
    }

    public function resetPassword(Request $request, UsersFrontendService $users): JsonResponse
    {
        $status = $users->resetPassword($request->only(['email', 'token', 'password', 'password_confirmation']));

        return response()->json(['status' => $status], $status === Password::PasswordReset ? 200 : 422);
    }
}
