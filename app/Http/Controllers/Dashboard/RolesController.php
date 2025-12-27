<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Throwable;

class RolesController extends Controller
{

    public function __construct()
    {
        $this->middleware('can:viewAny,' . \App\Models\Role::class);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::with('permissions')->get();
        return view('dashboard.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Role $role)
    {
        $permissions = Permission::all()->groupBy(function ($permission) {
            $parts = explode('-', $permission->name);
            return count($parts) > 1 ? end($parts) : 'other';
        });

        return view('dashboard.roles.create', compact('role', 'permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request)
    {
         $data = $request->validated();
        
        DB::beginTransaction();

        try { 

            $role = Role::create($data);

            $role->permissions()->attach($request->permissions);

        DB::commit();

        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
            return back()->with('error', 'Failed to create Role');
        }

        return redirect()
            ->route('dashboard.roles.index')
            ->with('success', 'Role created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        // $role = Role::with('permissions')->get();

         $groupedPermissions = Permission::all()->groupBy(function ($permission) {
            $parts = explode('-', $permission->name);
            return count($parts) > 1 ? end($parts) : 'other';
        });

        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('dashboard.roles.show', compact('role', 'groupedPermissions', 'rolePermissions'));    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        $permissions = Permission::all();

        return view('dashboard.roles.edit', compact('role', 'permissions'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role)
    {
        $data = $request->validated();

        DB::beginTransaction();

        try { 

            $role->update($data);

            $role->permissions()->sync($request->permissions ?? []); // aync => delete old permissions and add new
         
            DB::commit();

        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
            return back()->with('error', 'Failed to create Role');
        }
        return redirect()->route('dashboard.roles.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        $role->delete();

        return back();
    }
}
