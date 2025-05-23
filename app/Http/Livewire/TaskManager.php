<?php
namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
class TaskManager extends Component
{
    public $tasks = [];
    public $users = [];
    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'priority' => 'required|in:low,medium,high',
        'status' => 'required|in:todo,in_progress,completed,cancelled',
        'user_id' => 'required|exists:users,id',
        'deadline' => 'nullable|date',
    ];

    public $title, $description, $priority = 'medium', $status = 'todo', $deadline, $user_id;
    public $taskId, $isModalOpen = false, $updateMode = false;
public $filterStatus = '';
public $filterPriority = '';
public $selectedTasks = [];
public $selectAll = false;

    public function mount()
    {
        $this->loadTasks();
        // $this->users = User::role('user')->get();
        $users = new User();
       $this->users =  $users->role('user')->get();
    }

    public function loadTasks()
    {
        $this->tasks = Auth::user()->hasRole('admin')
            ? Task::with('user')->orderByRaw("FIELD(priority, 'high', 'medium', 'low')")->get()
            : Task::with('user')->where('user_id', Auth::id())->orderByRaw("FIELD(priority, 'high', 'medium', 'low')")->get();
    }

    public function openModal()
    {
        $this->resetForm();
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->resetForm();
        $this->isModalOpen = false;
    }

    public function resetForm()
    {
        $this->reset(['title', 'description', 'priority', 'status', 'deadline', 'user_id', 'taskId', 'updateMode']);
    }

    public function edit($id)
    {
        $task = Task::findOrFail($id);
        $this->taskId = $task->id;
        $this->title = $task->title;
        $this->description = $task->description;
        $this->priority = $task->priority;
        $this->status = $task->status;
        $this->deadline = $task->deadline;
        $this->user_id = $task->user_id;

        $this->updateMode = true;
        $this->isModalOpen = true;
    }

    public function save()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'priority' => Rule::in(['low', 'medium', 'high']),
            'status' => Rule::in(['todo', 'in_progress', 'completed', 'cancelled']),
            'user_id' => 'required|exists:users,id',
        ]);

        Task::updateOrCreate(
            ['id' => $this->taskId],
            $this->only(['title', 'description', 'priority', 'status', 'deadline', 'user_id'])
        );

        session()->flash('message', $this->taskId ? 'Task updated.' : 'Task created.');
        $this->closeModal();
        $this->loadTasks();
    }

    public function delete($id)
    {
        Task::findOrFail($id)->delete();
        session()->flash('message', 'Task deleted.');
        $this->loadTasks();
    }

    public function bulkComplete()
{
    Task::whereIn('id', $this->selectedTasks)->update(['status' => 'completed']);
    $this->selectedTasks = [];
    session()->flash('message', 'Selected tasks marked as completed.');
}

public function bulkDelete()
{
    Task::whereIn('id', $this->selectedTasks)->delete();
    $this->selectedTasks = [];
    session()->flash('message', 'Selected tasks deleted.');
}
public function render()
{
    $query = Task::query();

    // Apply filters
    if ($this->filterStatus) {
        $query->where('status', $this->filterStatus);
    }

    if ($this->filterPriority) {
        $query->where('priority', $this->filterPriority);
    }

    // Check if the logged-in user is admin
    if (auth()->user()?->isAdmin()) {
        // Admin sees all tasks
        $tasks = $query->orderBy('priority')->get();
    } else {
        // Regular users see only their tasks
        $tasks = $query->where('user_id', auth()->id())->orderBy('priority')->get();
    }

    return view('livewire.task-manager', [
        'tasks' => $tasks,
    ])->layout('layouts.app');
}

}
