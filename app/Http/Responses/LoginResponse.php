<?php

namespace App\Http\Responses;

use App\Enums\UserRole;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): Response
    {
        $user = $request->user();

        $home = match (true) {
            $user->hasRole(UserRole::Student->value) => '/my/profile',
            $user->hasRole(UserRole::Teacher->value) => '/grades',
            default => '/dashboard',
        };

        return $request->wantsJson()
            ? new JsonResponse('', 200)
            : redirect()->intended($home);
    }
}
