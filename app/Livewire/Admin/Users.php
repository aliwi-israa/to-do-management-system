<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use App\Models\Role;
use Livewire\Attributes\On;

class Users extends Component
{
    public $users, $name, $email, $role_id, $password, $user_id, $userId;
    public $updateMode = false;
    public $showFormModal = false;
    public $confirmingUserDeletion = false;
    public $deleteUserId = null;

    protected $listeners = ['deleteUserConfirmed'];

    public function render()
    {
        $this->users = User::with('role')->latest()->get();
        return view('livewire.admin.users', ['roles' => Role::all()])
                    ->layout('components.layouts.app');
    }

    public function resetInputFields()
    {
        $this->name = '';
        $this->email = '';
        $this->role_id = '';
        $this->password = '';
        $this->userId = null;
        $this->updateMode = false;
    }

    public function store()
    {
        $this->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'role_id' => 'required|exists:roles,id',
            'password' => 'required|min:6'
        ]);

        User::create([
            'name' => $this->name,
            'email' => $this->email,
            'role_id' => $this->role_id,
            'password' => bcrypt($this->password),
        ]);

        session()->flash('message', 'User created successfully.');
            $this->closeFormModal();

        $this->resetInputFields();
    }


    public function openFormModal()
    {
        $this->resetInputFields();
        $this->updateMode = false;
        $this->showFormModal = true;
    }

    public function closeFormModal()
    {
        $this->showFormModal = false;
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);

        $this->name = $user->name;
        $this->email = $user->email;
        $this->role_id = $user->role_id;
        $this->userId = $user->id; 
        $this->updateMode = true;
        $this->showFormModal = true;
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $this->userId,
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = User::findOrFail($this->userId);

        $user->update([
            'name' => $this->name,
            'email' => $this->email,
            'role_id' => $this->role_id,
        ]);

        session()->flash('message', 'User updated successfully.');
        $this->closeFormModal();
        $this->resetInputFields();
    }


    public function confirmDelete($id)
    {
        $this->deleteUserId = $id;
        $this->confirmingUserDeletion = true;
    }

    public function deleteUser()
    {
        User::findOrFail($this->deleteUserId)->delete();
        $this->confirmingUserDeletion = false;
        session()->flash('message', 'User deleted successfully.');
    }

}
