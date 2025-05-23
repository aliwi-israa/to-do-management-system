<?php
namespace App\Http\Controllers\Api\V1;

use App\Models\Task;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        return response()->json($query->paginate(10));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'in:low,medium,high',
            'status' => 'in:todo,in_progress,completed,cancelled',
            'user_id' => 'required|exists:users,id',
            'deadline' => 'nullable|date',
        ]);

        $task = Task::create($data);

        return response()->json($task, 201);
    }

    public function show(Task $task)
    {
        return response()->json($task);
    }

    public function update(Request $request, Task $task)
    {
        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'in:low,medium,high',
            'status' => 'in:todo,in_progress,completed,cancelled',
            'deadline' => 'nullable|date',
        ]);

        $task->update($data);

        return response()->json($task);
    }

    public function destroy(Task $task)
    {
        $task->delete();

        return response()->json(null, 204);
    }
}
