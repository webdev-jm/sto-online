<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleAddRequest;
use App\Http\Requests\RoleUpdateRequest;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $roles = Role::orderBy('id', 'DESC')->paginate(10);

        return view('pages.roles.index')->with([
            'roles' => $roles
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $permissions = Permission::all();
        $permissions_arr = [];
        foreach($permissions as $permission) {
            $permissions_arr[$permission->module][$permission->id] = [
                'name' => $permission->name,
                'description' => $permission->description
            ];
        }

        return view('pages.roles.create')->with([
            'permissions' => $permissions_arr
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RoleAddRequest $request)
    {
        $role = Role::create(['name' => $request->name])->givePermissionTo($request->permissions);

        // logs
        activity('create')
        ->performedOn($role)
        ->log(':causer.name has created a role [ :subject.name ]');

        return redirect()->route('role.index')->with([
            'message_success' => 'Role '.$role->name.' was created.'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $role = Role::findOrFail(decrypt($id));

        $permissions_arr = array();
        foreach($role->permissions as $permission) {
            $permissions_arr[$permission->module][$permission->id] = [
                'name' => $permission->name,
                'description' => $permission->description
            ];
        }

        return view('pages.roles.show')->with([
            'role' => $role,
            'permissions_arr' => $permissions_arr
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $id = decrypt($id);
        $role = Role::findOrFail($id);

        $permissions = Permission::all();
        $permissions_arr = [];
        foreach($permissions as $permission) {
            $permissions_arr[$permission->module][$permission->id] = [
                'name' => $permission->name,
                'description' => $permission->description
            ];
        }

        return view('pages.roles.edit')->with([
            'role' => $role,
            'permissions' => $permissions_arr
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(RoleUpdateRequest $request, $id)
    {
        $id = decrypt($id);
        $role = Role::findOrFail($id);

        $changes_arr['old'] = $role->getOriginal();
        $changes_arr['old']['arr'] = $role->permissions()->pluck('name');

        $role->update([
            'name' => $request->name
        ]);
        $role->syncPermissions($request->permissions);

        $changes_arr['changes'] = $role->getChanges();
        $changes_arr['changes']['arr'] = $role->permissions()->pluck('name');

        // logs
        activity('update')
        ->performedOn($role)
        ->withProperties($changes_arr)
        ->log(':causer.name has updated role :subject.name');

        return back()->with([
            'message_success' => 'Role '.$role->name.' was updated.'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
