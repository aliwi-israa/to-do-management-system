<div class="p-6 max-w-7xl mx-auto">
        @if (session()->has('message'))
        <div class="mb-6 flex items-center justify-between p-4 rounded-md bg-green-100 border border-green-300 text-green-800 shadow-sm">
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round"
                        d="M5 13l4 4L19 7" /></svg>
                <span class="font-medium">{{ session('message') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="font-bold text-green-700 hover:text-green-900">&times;</button>
        </div>
    @endif

<div class="flex flex-wrap items-center space-x-4 mb-6">

    {{-- Status Filter --}}
    <select wire:model.live="filterStatus" class="form-select border-gray-300 rounded-md px-3 py-2" style="width:25%">
        <option value="">All Statuses</option>
        <option value="todo">To Do</option>
        <option value="in_progress">In Progress</option>
        <option value="completed">Completed</option>
        <option value="cancelled">Cancelled</option>
    </select>

    {{-- Priority Filter --}}
    <select wire:model.live="filterPriority" class="form-select border-gray-300 rounded-md px-3 py-2" style="width:25%">
        <option value="">All Priorities</option>
        <option value="low">Low</option>
        <option value="medium">Medium</option>
        <option value="high">High</option>
    </select>

    {{-- Bulk Action Dropdown (only when rows are selected) --}}
    @if ($selectedRows)
        <div class="relative ml-2" x-data="{ open: false }" style="border: 1px solid rgb(209, 213, 219); padding: 8px 12px; border-radius: 8px;">
            <button @click="open = !open" type="button" class="btn btn-default">
                Bulk Actions
                <svg class="inline w-4 h-4 ml-1"style="display:inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" @click.away="open = false"
                 class="absolute left-0 mt-2 w-40 bg-white border border-gray-200 rounded shadow-lg z-10">
                @if(auth()->user()->hasRole('admin'))
                    <a wire:click.prevent="bulkDelete"
                    href="#"
                    class="block px-4 py-2 text-gray-700 hover:bg-gray-100 cursor-pointer">
                        Delete Selected
                    </a>
                @endif
                <a wire:click.prevent="bulkComplete"
                   href="#"
                   class="block px-4 py-2 text-gray-700 hover:bg-gray-100 cursor-pointer">
                    Mark as Completed
                </a>
            </div>
        </div>
    @endif

    {{-- Create Task Button --}}
    @if(auth()->user()->hasRole('admin'))
        <button wire:click="openModal" class="ml-auto px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            + Create Task
        </button>
    @endif
</div>

@if($tasks && $tasks->count())
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200 rounded-md">
            <thead>
                <tr class="bg-gray-100 text-gray-700">
                    <th>
                        <input wire:model.live="selectPageRows" type="checkbox">
                    </th>
                    <th class="w-6 px-3 py-3"></th>
                    <th class="px-3 py-3 text-left">Title</th>
                    <th class="px-3 py-3 text-left">Assigned User</th>
                    <th class="px-3 py-3 text-left">Priority</th>
                    <th class="px-3 py-3 text-left">Status</th>
                    <th class="px-3 py-3 text-left">Deadline</th>
                    <th class="w-40 px-3 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody id="task-list" wire:sortable="updateOrder">
                
                @foreach ($tasks as $task)

                    <tr  wire:sortable.item="{{ $task->id }}" wire:key="task-{{ $task->id }}" data-id="{{ $task->id }}" class="border-b hover:bg-gray-50">
                        <td class="text-center">
                            <input wire:model.live="selectedRows" type="checkbox" value="{{ $task->id }}">
                        </td>
                        <td wire:sortable.handle class="cursor-move" >☰</td>  
                        <td class="px-3 py-2">{{ $task->title }}</td>
                        <td class="px-3 py-2">{{ $task->user->name ?? '-' }}</td>
                        <td class="px-3 py-2 capitalize">{{ $task->priority }}</td>
                        <td class="px-3 py-2 capitalize">{{ str_replace('_', ' ', $task->status) }}</td>
                        <td class="px-3 py-2">
                            {{ $task->deadline ? $task->deadline->format('Y-m-d H:i') : '-' }}
                        </td>
                        <td class="px-3 py-2 text-center space-x-2">
                            <button wire:click="edit({{ $task->id }})"
                                class="px-2 py-1 bg-yellow-400 text-white rounded hover:bg-yellow-500 text-sm">
                                Edit
                            </button>
                            @if(auth()->user()->hasRole('admin'))
                                <button wire:click="confirmDelete({{ $task->id }})"
                                    class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-sm">
                                    Delete
                                </button>
                            @endif
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
    <div class="mt-4">
    {{ $tasks->links() }}
    </div>
@else
    <p>No users found.</p>
@endif

{{-- Create/Edit Modal --}}
@if ($showModal)
<div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-30 z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-xl">
        @if(auth()->user()->hasRole('user'))
            <h2 class="text-lg font-bold mb-6">{{ 'Edit Task Status: ' . $title }}</h2>
        @else
            <h2 class="text-lg font-bold mb-6">{{ $editMode ? 'Edit Task' : 'Create Task' }}</h2>
        @endif
    
        @if(auth()->user()->hasRole('user'))
            {{-- Only allow editing status for users --}}
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
        @else
            {{-- Full form for admins/managers --}}
            <div>
                <label for="title" class="block font-semibold text-gray-700 mb-1">Title</label>
                <input type="text" wire:model.defer="title" id="title" class="form-input w-full border-gray-300 rounded-md" />
                @error('title') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label for="description" class="block font-semibold text-gray-700 mb-1">Description</label>
                <textarea wire:model.defer="description" id="description" class="form-textarea w-full border-gray-300 rounded-md" rows="3"></textarea>
                @error('description') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="flex flex-row justify-between">
                <div style="width:45%">
                    <label for="user_id" class="block font-semibold text-gray-700 mb-1">Assign To</label>
                    <select wire:model.defer="user_id" id="user_id" class="form-select w-full border-gray-300 rounded-md">
                        <option value="">-- Select User --</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                    @error('user_id') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
                <div style="width:45%">
                    <label for="priority" class="block font-semibold text-gray-700 mb-1">Priority</label>
                    <select wire:model.defer="priority" id="priority" class="form-select w-full border-gray-300 rounded-md">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                    @error('priority') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex flex-row justify-between">
                <div style="width:45%">
                    <label for="status" class="block font-semibold text-gray-700 mb-1">Status</label>
                    <select wire:model.defer="status" id="status" class="form-select w-full border-gray-300 rounded-md">
                        <option value="todo">To Do</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    @error('status') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
                <div style="width:45%">
                    <label for="deadline" class="block font-semibold text-gray-700 mb-1">Deadline</label>
                    <input type="datetime-local" wire:model.defer="deadline" id="deadline" class="form-input w-full border-gray-300 rounded-md" />
                    @error('deadline') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>
        @endif

        <div class="flex justify-end space-x-2 mt-4">
            <button wire:click="$set('showModal', false)" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
            <button wire:click="save" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    @if(auth()->user()->hasRole('user'))
                        Change status
                    @else
                        {{ $editMode ? 'Update Task' : 'Create Task' }}
                    @endif
            </button>
        </div>
    </div>
</div>
@endif

{{-- Delete Confirmation Modal --}}
@if ($confirmingTaskDeletion)
<!-- confirmation modal -->
    <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-30 z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">Confirm Deletion</h2>
            <p class="text-gray-700">Are you sure you want to delete this Task?</p>

            <div class="mt-6 flex justify-end gap-3">
                <button wire:click="$set('confirmingTaskDeletion', false)"
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
