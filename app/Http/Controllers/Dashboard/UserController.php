<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class UserController extends Controller
{

   public function __construct()
{
// $this->middleware('can:viewAny,' . \App\Models\User::class);
}
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with('roles')->latest()->paginate(10);
        return view('dashboard.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($user)
    {
        $roles = Role::all();
        return view('dashboard.users.create', compact('user','roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:8',
            'role_id' => 'required|exists:roles,id',
        ]);
         DB::beginTransaction();

         try { 
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->roles()->attach($request->role_id);

        DB::commit();

         } catch (Throwable $e) {
                    DB::rollBack();
                    throw $e;
                    return back()->with('error', 'Failed to create user');
            }

        return redirect()->route('dashboard.users.index')
                        ->with('success','User created successfully');

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $roles = Role::all();

        return view('dashboard.users.edit', compact('user','roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
         $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'nullable|confirmed|min:8',
            'role_id' => 'required'
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->roles()->sync($request->roles);

        return back()->with('success','User created successfully');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
       $user->delete();
        return redirect()->route('dashboard.users.index')->with('success','User deleted');
    }
}
