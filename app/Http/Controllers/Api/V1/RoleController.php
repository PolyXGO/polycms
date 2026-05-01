<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreRoleRequest;
use App\Http\Requests\Api\V1\UpdateRoleRequest;
use App\Http\Resources\Api\V1\RoleCollection;
use App\Http\Resources\Api\V1\RoleResource;
use App\Models\Role;
use App\Services\PermissionRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Facades\Hook;

class RoleController extends Controller
{
    public function __construct(
        protected PermissionRegistrar $permissionRegistrar,
        protected PermissionRegistry $permissionRegistry
    ) {
    }

    public function index(Request $request): RoleCollection
    {
        $this->authorize('viewAny', Role::class);

        $query = Role::query()
            ->with('permissions')
            ->withCount('users');

        if ($type = $request->string('type')->lower()->toString()) {
            if ($type === 'system') {
                $query->where('is_system', true);
            } elseif ($type === 'custom') {
                $query->where('is_system', false);
            }
        }

        if ($search = $request->string('search')->toString()) {
            $query->where('name', 'like', "%{$search}%");
        }

        $perPage = min((int) $request->get('per_page', 20), 100);

        $roles = $query
            ->orderByDesc('is_system')
            ->orderBy('name')
            ->paginate($perPage);

        return new RoleCollection($roles);
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $data = $request->validated();

        Hook::doAction('role.creating', $data);

        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => $data['guard_name'],
            'module_owner' => $data['module_owner'] ?? null,
            'metadata' => $data['metadata'] ?? [],
        ]);

        $this->syncPermissions($role, $data['permissions'] ?? []);
        $this->audit('role.created', $request->user()?->id, $role);

        return $this->successResponse(new RoleResource($role->load('permissions')), 'Role created successfully', 201);
    }

    public function show(Role $role): JsonResponse
    {
        $this->authorize('view', $role);

        return $this->successResponse(new RoleResource($role->load('permissions')));
    }

    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        $data = $request->validated();

        $this->authorize('update', $role);

        Hook::doAction('role.updating', $role, $data);

        $role->fill(Arr::only($data, ['name', 'guard_name', 'module_owner']));

        if (array_key_exists('metadata', $data)) {
            $role->metadata = $data['metadata'] ?? [];
        }

        $role->save();

        if (array_key_exists('permissions', $data)) {
            $this->syncPermissions($role, $data['permissions'] ?? []);
        }

        $this->audit('role.updated', $request->user()?->id, $role);

        return $this->successResponse(new RoleResource($role->load('permissions')), 'Role updated successfully');
    }

    public function destroy(Role $role): JsonResponse
    {
        Hook::doAction('role.deleting', $role);

        $this->authorize('delete', $role);

        $role->delete();

        $this->forgetPermissionCache();
        $this->audit('role.deleted', request()->user()?->id, $role);

        return $this->successResponse(null, 'Role deleted successfully', 204);
    }

    public function clone(Request $request, Role $role): JsonResponse
    {
        Hook::doAction('role.cloning', $role);

        $this->authorize('clone', $role);

        $name = $request->string('name')->toString();
        if ($name === '') {
            $name = $role->name.' Copy';
        }

        $baseGuard = $role->guard_name ?? 'web';

        $clone = Role::create([
            'name' => $this->generateUniqueName($name),
            'guard_name' => $baseGuard,
            'module_owner' => null,
            'metadata' => $role->metadata ?? [],
        ]);

        $clone->syncPermissions($role->permissions->pluck('name')->toArray());

        $this->forgetPermissionCache();
        $this->audit('role.cloned', $request->user()?->id, $clone, ['source_role_id' => $role->id]);

        return $this->successResponse(new RoleResource($clone->load('permissions')), 'Role cloned successfully', 201);
    }

    public function meta(): JsonResponse
    {
        $this->authorize('viewAny', Role::class);

        $definitions = $this->permissionRegistry->all();
        $permissions = collect($definitions);

        if ($permissions->isEmpty()) {
            $permissions = Permission::query()
                ->orderBy('name')
                ->get()
                ->map(fn (Permission $permission) => [
                    'name' => $permission->name,
                    'group' => 'core',
                    'guard_name' => $permission->guard_name,
                    'module_owner' => null,
                ])
                ->keyBy('name');
        }

        return $this->successResponse([
            'permissions' => $permissions->values(),
            'system_roles' => Role::query()
                ->where('is_system', true)
                ->orderBy('name')
                ->get()
                ->map(fn (Role $role) => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'metadata' => $role->metadata ?? [],
                ]),
        ]);
    }

    protected function syncPermissions(Role $role, array $permissions): void
    {
        if (!empty($permissions)) {
            $role->syncPermissions($permissions);
        } else {
            $role->syncPermissions([]);
        }

        $this->forgetPermissionCache();
    }

    protected function forgetPermissionCache(): void
    {
        $this->permissionRegistrar->forgetCachedPermissions();
    }

    protected function generateUniqueName(string $base): string
    {
        $name = $base;
        $counter = 1;

        while (Role::where('name', $name)->exists()) {
            $name = $base.' '.$counter;
            $counter++;
        }

        return $name;
    }

    protected function audit(string $event, ?int $userId, Role $role, array $context = []): void
    {
        Log::info($event, array_merge([
            'user_id' => $userId,
            'role_id' => $role->id,
            'role_name' => $role->name,
            'timestamp' => now()->toIso8601String(),
        ], $context));
    }
}

