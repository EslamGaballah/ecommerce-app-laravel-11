<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class RolesController extends Controller
{

    //  public function __construct()
    // {
    //     $this->authorizeResource(Role::class, 'role');    
    // }
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
    public function create()
    {
        $role = new Role();
        // $permissions = Permission::all();
        
        $permissions = Permission::all()->groupBy(function ($permission) {
            $parts = explode('-', $permission->name);
            return count($parts) > 1 ? end($parts) : 'other';
        });



        return view('dashboard.roles.create', compact('role', 'permissions'));


        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
         $request->validate([
            'name' => 'required|unique:roles,name|string|max:255',
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'exists:permissions,id'
        ]);
        
        DB::beginTransaction();

        try { 

            $role = Role::create([
                'name' => $request->name,
                // 'permission_id' => $request->permissions[],
            ]);

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
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|unique:roles,name|string|max:255',
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'exists:permissions,id'
        ]);

        DB::beginTransaction();

        try { 

            $role->update([
                'name' => $request->name,
            ]);

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
