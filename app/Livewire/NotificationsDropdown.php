<?php

namespace App\Livewire;

use Livewire\Component;

class NotificationsDropdown extends Component
{
    protected $listeners = [
    'notificationsUpdated' => 'refreshNotifications',
];
    public function getNotificationsProperty()
    {
        return auth()->check()
            ? auth()->user()->notifications()->latest()->get()
            : collect();
    }

    public function markAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
    }

    public function refreshNotifications()
    {
        $this->reset();
    }

    public function render()
    {
        return view('livewire.notifications-dropdown');
    }
}