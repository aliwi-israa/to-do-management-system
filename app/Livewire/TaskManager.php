<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Task;
use App\Models\User;
use Illuminate\Validation\Rule;
use  App\Events\TaskUpdated;
use App\Notifications\TaskUpdatedNotification;
use Livewire\WithPagination;

class TaskManager extends Component
{
    use WithPagination;
    
    public $page = 1;
    public $perPage = 10;
    public $users = [];

    public $title = '';
    public $description = '';
    public $priority = 'medium';
    public $status = 'todo';
    public $deadline;
    public $user_id;

    public $taskId;
    public $updateMode = false;
    public $editMode = false;
    public $showModal = false;

    public $filterStatus = '';
    public $filterPriority = '';

    public $selectedRows = [];
    public $selectPageRows = false;

    public $confirmingTaskDeletion = false;
    public $deleteTaskId = null;

    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'priority' => 'required|in:low,medium,high',
        'status' => 'required|in:todo,in_progress,completed,cancelled',
        'user_id' => 'required|exists:users,id',
        'deadline' => 'nullable|date',
    ];
    protected $paginationTheme = 'tailwind';
    protected $listeners = ['deleteTaskConfirmed'];


    public function mount()
    {
        $usersQuery = new User();
        $this->users = $usersQuery->newQuery()->role('user')->get();
    }

    public function resetForm()
    {
        $this->reset(['title', 'description', 'priority', 'status', 'deadline', 'user_id', 'taskId', 'updateMode']);
    }

    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->resetForm();
        $this->showModal = false;
    }

    public function edit($id)
    {
        $task = Task::findOrFail($id);
        $this->taskId = $task->id;
        $this->title = $task->title;
        $this->description = $task->description;
        $this->priority = $task->priority;
        $this->status = $task->status;
        $this->deadline = $task->deadline ? $task->deadline->format('Y-m-d\TH:i') : null;
        $this->user_id = $task->user_id;

        $this->updateMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $task = Task::updateOrCreate(
            ['id' => $this->taskId],
            [
                'title' => $this->title,
                'description' => $this->description,
                'priority' => $this->priority,
                'status' => $this->status,
                'deadline' => $this->deadline,
                'user_id' => $this->user_id
            ]
        );

        session()->flash('message', $this->taskId ? 'Task updated.' : 'Task created.');
        $this->closeModal();

        $currentUser = auth()->user();

        if ($currentUser->hasRole('user')) {
            // trigger event
            event(new TaskUpdated($task));
            // Notify all admins if a normal user edited the task
            $admins = new User();
            $admins = $admins->newQuery()->role('admin')->get();

            foreach ($admins as $admin) {
                $admin->notify(new TaskUpdatedNotification($task, 'Task Status has been updated'));
            }
        } elseif ($currentUser->hasRole('admin')) {
            //trigger event 
            event(new TaskUpdated($task));
            //fire nontification
            $user = User::find($task->user_id);
            $user->notify(new TaskUpdatedNotification($task,  $this->taskId ? 'a Task assigned to you has been updated.' : 'a Task was created for you.'));
        }
        
    }

    public function updatedSelectPageRows($value)
    {
        if ($value) {
            $tasks = $this->filteredTasks()->pluck('id')->map(fn($id) => (string) $id);
            $this->selectedRows = $tasks->toArray();
        } else {
            $this->reset(['selectedRows','selectPageRows']);
        }
    }

    public function updatedFilterStatus()
    {
        $this->resetPage();
        $this->reset(['selectedRows', 'selectPageRows']);
    }

    public function updatedFilterPriority()
    {
        $this->resetPage();
        $this->reset(['selectedRows', 'selectPageRows']);
    }

    protected function filteredTasks()
    {
        $userId = auth()->id();

        $query = Task::query();

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }
        if ($this->filterPriority) {
            $query->where('priority', $this->filterPriority);
        }

        if (auth()->user()?->isAdmin()) {
            // admin can sees ALL tasks
            $query->leftJoin('task_orders', function ($join) use ($userId) {
                    $join->on('tasks.id', '=', 'task_orders.task_id')
                        ->where('task_orders.user_id', '=', $userId);
                })
                ->orderByRaw('task_orders.sort_order IS NULL, task_orders.sort_order ASC') // NULL last
                ->select('tasks.*');
        } else {
            // make user sees only his own tasks
            $query->where('tasks.user_id', $userId)
                ->leftJoin('task_orders', function ($join) use ($userId) {
                    $join->on('tasks.id', '=', 'task_orders.task_id')
                        ->where('task_orders.user_id', '=', $userId);
                })
                ->orderByRaw('task_orders.sort_order IS NULL, task_orders.sort_order ASC')
                ->select('tasks.*');
        }

        return $query;
    }

    public function updateOrder($orderedIds)
    {
        $userId = auth()->id();

        foreach ($orderedIds as $item) {
            \DB::table('task_orders')->updateOrInsert(
                ['user_id' => $userId, 'task_id' => $item['value']],
                ['sort_order' => $item['order'], 'updated_at' => now()]
            );
        }
    }

    public function confirmDelete($id)
    {
        $this->deleteTaskId = $id;
        $this->confirmingTaskDeletion = true;
    }

    public function deleteTask()
    {
        Task::findOrFail($this->deleteTaskId)->delete();
        $this->confirmingTaskDeletion = false;
        session()->flash('message', 'Task deleted successfully.');
    }

    public function bulkComplete()
    {
        Task::whereIn('id', $this->selectedRows)->update(['status' => 'completed']);
        $this->reset(['selectedRows', 'selectPageRows']);
        session()->flash('message', 'Selected tasks marked as completed.');
    }

    public function bulkDelete()
    {
        Task::whereIn('id', $this->selectedRows)->delete();
        $this->reset(['selectedRows', 'selectPageRows']);
        session()->flash('message', 'Tasks deleted successfully.');
    }

    public function render()
    {
        $tasks = $this->filteredTasks()->paginate(10);

        return view('livewire.task-manager', [
            'tasks' => $tasks,
        ]);
    }
}