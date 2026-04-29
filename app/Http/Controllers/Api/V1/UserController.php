<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreUserRequest;
use App\Http\Requests\Api\V1\UpdateUserRequest;
use App\Http\Resources\Api\V1\UserCollection;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Facades\Hook;

class UserController extends Controller
{
    public function index(Request $request): UserCollection
    {
        $this->authorize('viewAny', User::class);

        $query = User::query()->with('roles');

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->string('role')->toString()) {
            $query->whereHas('roles', function ($builder) use ($role) {
                $builder->where('name', $role);
            });
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        if (!in_array($sortBy, ['created_at', 'name', 'email'], true)) {
            $sortBy = 'created_at';
        }

        if (!in_array(strtolower((string) $sortOrder), ['asc', 'desc'], true)) {
            $sortOrder = 'desc';
        }

        $query->orderBy($sortBy, $sortOrder);

        $perPage = min((int) $request->get('per_page', 15), 100);

        $users = $query->paginate($perPage);

        return new UserCollection($users);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $this->authorize('create', User::class);

        $data = $request->validated();

        Hook::doAction('user.creating', $data);

        $roles = $data['roles'] ?? [];
        unset($data['roles']);

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        if (!empty($roles)) {
            $this->authorize('manageRoles', User::class);
            $this->syncRoles($user, $roles);
        } else {
            $user->assignRole('customer');
        }

        $user->load('roles');

        return $this->successResponse(new UserResource($user), 'User created successfully', 201);
    }

    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);

        $user->load('roles');

        return $this->successResponse(new UserResource($user));
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $data = $request->validated();

        Hook::doAction('user.updating', $user, $data);

        $roles = $data['roles'] ?? null;

        if (array_key_exists('email', $data) && $user->id === $request->user()->id && $data['email'] !== $user->email) {
            throw ValidationException::withMessages([
                'email' => __('You cannot change your own email here.'),
            ]);
        }

        unset($data['roles']);

        // Handle password update
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        } else {
            unset($data['password']);
        }

        $user->fill($data);
        $user->save();

        // Handle role update
        // Prevent users from removing their own admin role if they are the only one?
        // Logic should be handled carefully. For now, assuming standard role sync.
        if (is_array($roles)) {
            $this->authorize('manageRoles', User::class);
            $this->syncRoles($user, $roles);
        }

        $user->load('roles');

        return $this->successResponse(new UserResource($user), 'User updated successfully');
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        Hook::doAction('user.deleting', $user);

        $this->authorize('delete', $user);

        // Prevent self-deletion
        if ($user->id === $request->user()->id) {
            throw ValidationException::withMessages([
                'user' => __('You cannot delete your own account.'),
            ]);
        }

        // Prevent deleting super-admin if not super-admin?
        // Simple check: Admin cannot delete other Admins unless Super Admin?
        // For simplicity:
        if ($user->hasRole('admin') && !$request->user()->hasRole('admin')) {
            throw ValidationException::withMessages([
                'user' => __('Only administrators can delete administrator accounts.'),
            ]);
        }

        $user->delete();

        return $this->successResponse(null, 'User deleted successfully', 204);
    }

    protected function syncRoles(User $user, array $roles): void
    {
        $availableRoles = Role::query()->pluck('name')->toArray();

        $filteredRoles = array_values(array_intersect($roles, $availableRoles));

        if (empty($filteredRoles)) {
            $filteredRoles = ['customer'];
        }

        $user->syncRoles($filteredRoles);
    }

    public function meta(): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        return $this->successResponse([
            'roles' => Role::query()->orderBy('name')->pluck('name')->values()->all(),
            'permissions' => Permission::query()->orderBy('name')->pluck('name')->values()->all(),
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class); // Ensure user has permission to view users

        $query = $request->get('q');

        if (!$query) {
            return response()->json([]);
        }

        $users = User::query()
            ->where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->select(['id', 'name', 'email'])
            ->limit(20)
            ->get();

        return response()->json($users);
    }
}

