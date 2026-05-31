<?php
namespace App\Http\Controllers;
use App\Http\Requests\StoreAdminUserRequest;
use App\Http\Requests\UpdateAdminUserRequest;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\UserService;

class AdminUserController extends Controller
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly UserService $service
    ) {}

    public function index()
    {
        $users = $this->users->paginate(10);
        return view('admin-users.index', compact('users'));
    }

    public function create()
    {
        return view('admin-users.create');
    }

    public function store(StoreAdminUserRequest $request)
    {
        $this->service->store($request->validated(), $request->user());
        return redirect()->route('admin-users.index')->with('success', 'Admin berhasil ditambahkan.');
    }

    public function edit(User $admin_user)
    {
        return view('admin-users.edit', compact('admin_user'));
    }

    public function update(UpdateAdminUserRequest $request, User $admin_user)
    {
        try {
            $this->service->update($admin_user, $request->validated(), $request->user());
            return redirect()->route('admin-users.index')->with('success', 'Admin berhasil diperbarui.');
        } catch (\LogicException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(User $admin_user)
    {
        try {
            $this->service->destroy($admin_user, auth()->user());
            return back()->with('success', 'Admin berhasil dihapus.');
        } catch (\LogicException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
