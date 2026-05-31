<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserService
{
    public function __construct(private readonly UserRepositoryInterface $users) {}

    public function store(array $validated, User $actor): User
    {
        $validated['password'] = Hash::make($validated['password']);
        
        $user = $this->users->create($validated);
        
        Log::info('Admin user created', [
            'by' => $actor->id,
            'username' => $user->username
        ]);
        
        return $user;
    }

    public function update(User $user, array $validated, User $actor): User
    {
        if ($user->id === $actor->id) {
            throw new \LogicException('Tidak dapat memodifikasi akun sendiri.');
        }

        $data = [
            'name'      => $validated['name'],
            'username'  => $validated['username'],
            'role'      => $validated['role'],
            'is_active' => filter_var($validated['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $updatedUser = $this->users->update($user, $data);

        Log::info('Admin user updated', [
            'by' => $actor->id,
            'target' => $user->id
        ]);

        return $updatedUser;
    }

    public function destroy(User $user, User $actor): void
    {
        if ($user->id === $actor->id) {
            throw new \LogicException('Tidak dapat menghapus akun sendiri.');
        }

        Log::info('Admin user deleted', [
            'by' => $actor->id,
            'target' => $user->id
        ]);

        $this->users->delete($user);
    }
}
