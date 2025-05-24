<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class TaskUpdatedNotification extends Notification
{
    use Queueable;

    protected $task;

    public function __construct($task,  public string $message)
    {
        $this->task = $task;

    }

    public function via($notifiable)
    {
        return ['database', 'broadcast']; 
    }

    public function toArray($notifiable)
    {
        return [
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'status' => $this->task->status,
            'assigned_by' => auth()->user()->name ?? null,
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'task' => $this->task,
        ]);
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => $this->message,
            'title' => $this->task->title,
            'priority' => $this->task->priority ?? '',
            'task_id' => $this->task->id,
        ];
    }

}


   