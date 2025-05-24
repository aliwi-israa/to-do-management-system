<div class="max-w-5xl mx-auto py-8 px-4">
<div class="flex flex-row items-center justify-end gap-4 mb-6 w-full">
    <button wire:click="openFormModal" 
        class="px-3 py-2 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 transition">
        + Create User
    </button>
</div>

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

    <!-- User form modal -->
    @if($showFormModal)
    <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-30 z-50">
        <div class="bg-white p-6 rounded-lg shadow-md w-full max-w-lg mx-4">
            <h3 class="text-xl font-semibold mb-4 text-gray-800">
                {{ $updateMode ? 'Edit User' : 'Create User' }}
            </h3>

            <form wire:submit.prevent="{{ $updateMode ? 'update' : 'store' }}">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input type="text" wire:model="name" placeholder="Name"
                        class="form-input border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" />
                    <input type="email" wire:model="email" placeholder="Email"
                        class="form-input border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" />

                    <select wire:model="role_id"
                        class="form-select border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Select Role --</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>

                    @if(!$updateMode)
                        <input type="password" wire:model="password" placeholder="Password"
                            class="form-input border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" />
                    @endif
                </div>

                <div class="mt-4 flex space-x-3 justify-end">
                    <button type="button" wire:click="closeFormModal"
                        class="px-4 py-2 bg-gray-300 text-gray-800 font-semibold rounded-md hover:bg-gray-400 transition">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 transition">
                        {{ $updateMode ? 'Update' : 'Create' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Users table below -->
    <div class="overflow-x-auto">
        @if($users && $users->count())
            <table class="min-w-full bg-white rounded-md shadow-md">
                <thead class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                    <tr>
                        <th class="py-3 px-6 text-left">Name</th>
                        <th class="py-3 px-6 text-left">Email</th>
                        <th class="py-3 px-6 text-left">Role</th>
                        <th class="py-3 px-6 text-left">Actions</th>
                    </tr>
                </thead>

                <tbody class="text-gray-700">
                    @foreach($users as $user)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-6">{{ $user->name }}</td>
                            <td class="py-3 px-6">{{ $user->email }}</td>
                            <td class="py-3 px-6">{{ $user->role->name ?? 'N/A' }}</td>
                            <td class="py-3 px-6 space-x-2">
                                <button wire:click="edit({{ $user->id }})"
                                    class="text-yellow-600 hover:text-yellow-800 font-semibold">Edit</button>

                                <button wire:click="confirmDelete({{ $user->id }})"
                                    class="text-red-600 px-3 py-1 rounded hover:text-red-700">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">
                {{ $users->links() }}
            </div>
        @else
            <p>No users found.</p>
        @endif
    </div>

    <!-- confirmation modal -->
    @if ($confirmingUserDeletion)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-30 z-50">
            <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
                <h2 class="text-xl font-semibold mb-4 text-gray-800">Confirm Deletion</h2>
                <p class="text-gray-700">Are you sure you want to delete this user?</p>

                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('confirmingUserDeletion', false)"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded">
                        Cancel
                    </button>
                    <button wire:click="deleteUser"
                        class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded">
                        Yes, Delete
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
