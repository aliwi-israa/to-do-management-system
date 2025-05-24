<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Task;
use App\Models\User;
use Illuminate\Validation\Rule;

class TaskManager extends Component
{
    public $users = [];

    public $title = '', $description = '', $priority = 'medium', $status = 'todo', $deadline, $user_id;
    public $taskId, $updateMode = false;

    public $filterStatus = '';
    public $filterPriority = '';
    public $selectedRows = [];
    public $selectPageRows = false;
    public $showModal = false;
    public $showDeleteModal = false;
    public $editMode = false;
    protected $listeners = ['updateTaskOrder', 'deleteTaskConfirmed'];

    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'priority' => 'required|in:low,medium,high',
        'status' => 'required|in:todo,in_progress,completed,cancelled',
        'user_id' => 'required|exists:users,id',
        'deadline' => 'nullable|date',
    ];



        public $confirmingTaskDeletion = false;
    public $deleteTaskId = null;

    public function mount()
    {
        $usersQuery = new User();
        $this->users = $usersQuery->newQuery()->role('user')->get();
    }

    public function updateTaskOrder($orderedIds)
    {
        foreach ($orderedIds as $index => $id) {
            Task::where('id', $id)->update(['sort_order' => $index]);
        }
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

        Task::updateOrCreate(
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
        $this->reset(['selectedRows', 'selectPageRows']);
    }

    public function updatedFilterPriority()
    {
        $this->reset(['selectedRows', 'selectPageRows']);
    }

protected function filteredTasks()
{
    $query = Task::query();

    if ($this->filterStatus) {
        $query->where('status', $this->filterStatus);
    }
    if ($this->filterPriority) {
        $query->where('priority', $this->filterPriority);
    }
    $query->orderBy('sort_order');

    if (auth()->user()?->isAdmin()) {
        return $query;
    } else {
        return $query->where('user_id', auth()->id());
    }
}


public function updateOrder($orderedIds)
{
foreach ($orderedIds as $item) {
    Task::where('id', $item['value'])->update(['sort_order' => $item['order']]);
}

}
    public function render()
    {
        $tasks = $this->filteredTasks()->orderBy('sort_order')->get();

        return view('livewire.task-manager', [
            'tasks' => $tasks,
        ]);
    }
}