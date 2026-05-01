<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class RoleSwitcher extends Component
{
    public bool $open = false;

    public function toggle(): void
    {
        $this->open = ! $this->open;
    }

    public function signInAs(int $userId): void
    {
        $user = User::query()->findOrFail($userId);
        Auth::login($user);
        $this->open = false;
        $this->dispatch('user-changed');
    }

    public function signOut(): void
    {
        Auth::logout();
        $this->open = false;
        $this->dispatch('user-changed');
    }

    #[On('user-changed')]
    public function refresh(): void
    {
    }

    public function render()
    {
        return view('livewire.components.role-switcher', [
            'currentUser' => Auth::user(),
            'users' => User::query()->orderBy('role')->orderBy('name')->get(),
        ]);
    }
}
