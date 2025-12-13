<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;
use App\Enums\UserRole;

class Login extends BaseLogin
{
    protected function redirectTo(): string
    {
        $user = auth()->user();

        if ($user->role === UserRole::SUPER_ADMIN) {
            return route('filament.admin.pages.dashboard');
        }

        return route('filament.admin.resources.requests.index');
    }

    public function getHeading(): string|Htmlable
    {
        return 'Admin panelga kirish';
    }
}
