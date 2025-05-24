<div class="p-6 max-w-7xl mx-auto">

    {{-- Filters & Bulk Actions --}}
    <div class="flex flex-wrap items-center space-x-4 mb-6">

        {{-- Status Filter --}}
        <select wire:model="filterStatus" class="form-select border-gray-300 rounded-md px-3 py-2">
            <option value="">All Statuses</option>
            <option value="todo">To Do</option>
            <option value="in_progress">In Progress</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
        </select>

        {{-- Priority Filter --}}
        <select wire:model="filterPriority" class="form-select border-gray-300 rounded-md px-3 py-2">
            <option value="">All Priorities</option>
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
        </select>

        {{-- Bulk Action Buttons --}}
        <button wire:click="bulkComplete" 
            @if(count($selectedTasks) === 0) disabled @endif
            class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed">
            Mark Selected Completed
        </button>

        <button wire:click="bulkDelete"
            @if(count($selectedTasks) === 0) disabled @endif
            class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed">
            Delete Selected
        </button>

        {{-- Create Task Button --}}
        <button wire:click="openModal" class="ml-auto px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            + Create Task
        </button>

    </div>

    {{-- Flash Message --}}
    @if (session()->has('message'))
        <div class="mb-4 text-green-600 font-semibold">
            {{ session('message') }}
        </div>
    @endif

    {{-- Tasks Table --}}
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200 rounded-md" wire:sortable="updateTaskOrder">

            <thead>
                <tr class="bg-gray-100 text-gray-700">
                    <th class="w-10 px-3 py-3"><input type="checkbox" wire:model="selectAll"></th>
                    <th class="w-6 px-3 py-3"></th> {{-- Drag handle --}}
                    <th class="px-3 py-3 text-left">Title</th>
                    <th class="px-3 py-3 text-left">Assigned User</th>
                    <th class="px-3 py-3 text-left">Priority</th>
                    <th class="px-3 py-3 text-left">Status</th>
                    <th class="px-3 py-3 text-left">Deadline</th>
                    <th class="w-40 px-3 py-3 text-center">Actions</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($tasks as $task)
                    <tr wire:sortable.item="{{ $task->id }}" wire:key="task-{{ $task->id }}" class="border-b hover:bg-gray-50">
                        {{-- Bulk Select --}}
                        <td class="text-center">
<input type="checkbox" wire:model="selectedTasks" value="{{ $task->id }}">
                        </td>

                        {{-- Drag Handle --}}
                        <td wire:sortable.handle class="cursor-move text-center text-gray-400 select-none px-3">☰</td>

                        {{-- Title --}}
                        <td class="px-3 py-2">{{ $task->title }}</td>

                        {{-- Assigned User --}}
                        <td class="px-3 py-2">{{ $task->user->name ?? '-' }}</td>

                        {{-- Priority --}}
                        <td class="px-3 py-2 capitalize">{{ $task->priority }}</td>

                        {{-- Status --}}
                        <td class="px-3 py-2 capitalize">{{ str_replace('_', ' ', $task->status) }}</td>

                        {{-- Deadline --}}
                        <td class="px-3 py-2">
                            {{ $task->deadline ? $task->deadline->format('Y-m-d H:i') : '-' }}
                        </td>

                        {{-- Actions --}}
                        <td class="px-3 py-2 text-center space-x-2">

                            {{-- Edit Button --}}
                            <button wire:click="edit({{ $task->id }})"
                                class="px-2 py-1 bg-yellow-400 text-white rounded hover:bg-yellow-500 text-sm">
                                Edit
                            </button>

                            {{-- Delete Button --}}
                            <button wire:click="delete({{ $task->id }})"
                                class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-sm">
                                Delete
                            </button>

                        </td>
                    </tr>
                @endforeach

                @if ($tasks->isEmpty())
                    <tr>
                        <td colspan="8" class="text-center py-4 text-gray-500">No tasks found.</td>
                    </tr>
                @endif
            </tbody>

        </table>
    </div>
{{-- Create/Edit Modal --}}
@if ($showModal)
<div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-20">
    <div class="bg-white w-full max-w-lg rounded shadow-lg p-6">
        <h2 class="text-lg font-bold mb-6">{{ $editMode ? 'Edit Task' : 'Create Task' }}</h2>

        <div class="grid grid-cols-2 gap-4 mb-4">
            {{-- Title --}}
            <div>
                <label for="title" class="block font-semibold text-gray-700 mb-1">Title</label>
                <input type="text" wire:model.defer="title" id="title" class="form-input w-full border-gray-300 rounded-md" />
                @error('title') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            {{-- Assign User --}}
            <div>
                <label for="user_id" class="block font-semibold text-gray-700 mb-1">Assign To</label>
                <select wire:model.defer="user_id" id="user_id" class="form-select w-full border-gray-300 rounded-md">
                    <option value="">-- Select User --</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
                @error('user_id') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            {{-- Description --}}
            <div>
                <label for="description" class="block font-semibold text-gray-700 mb-1">Description</label>
                <textarea wire:model.defer="description" id="description" class="form-textarea w-full border-gray-300 rounded-md" rows="3"></textarea>
                @error('description') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            {{-- Priority --}}
            <div>
                <label for="priority" class="block font-semibold text-gray-700 mb-1">Priority</label>
                <select wire:model.defer="priority" id="priority" class="form-select w-full border-gray-300 rounded-md">
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                </select>
                @error('priority') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            {{-- Status --}}
            <div>
                <label for="status" class="block font-semibold text-gray-700 mb-1">Status</label>
                <select wire:model.defer="status" id="status" class="form-select w-full border-gray-300 rounded-md">
                    <option value="todo">To Do</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                @error('status') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            {{-- Deadline --}}
            <div>
                <label for="deadline" class="block font-semibold text-gray-700 mb-1">Deadline</label>
                <input type="datetime-local" wire:model.defer="deadline" id="deadline" class="form-input w-full border-gray-300 rounded-md" />
                @error('deadline') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="flex justify-end space-x-2">
            <button wire:click="$set('showModal', false)" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
            <button wire:click="save" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                {{ $editMode ? 'Update Task' : 'Create Task' }}
            </button>
        </div>
    </div>
</div>
@endif

{{-- Delete Confirmation Modal --}}
@if ($showDeleteModal)
<div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-30 z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
        <h2 class="text-xl font-semibold mb-4 text-gray-800">Delete Task</h2>
        <p class="text-gray-700 mb-6">Are you sure you want to delete this task? This action cannot be undone.</p>

        <div class="flex justify-end gap-3">
            <button wire:click="$set('showDeleteModal', false)"
                class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded">
                Cancel
            </button>
            <button wire:click="deleteTask"
                class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded">
                Yes, Delete
            </button>
        </div>
    </div>
</div>
@endif


</div>
