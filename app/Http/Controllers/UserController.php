<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\UserAddRequest;
use App\Http\Requests\UserEditRequest;

use Spatie\Permission\Models\Role;

use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = trim($request->get('search'));

        $users = User::orderBy('id', 'DESC')
        ->when(!empty($search), function($query) use($search) {
            $query->where('name', 'like', '%'.$search.'%')
                ->orWhere('email', 'like', '%'.$search.'%');
        })
        ->paginate(10)->onEachSide(1)
        ->appends(request()->query());

        return view('pages.users.index')->with([
            'users' => $users,
            'search' => $search
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        // ROLES
        $roles = Role::orderBy('name', 'ASC')
        ->get();

        return view('pages.users.create')->with([
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserAddRequest $request)
    {
        $password = Hash::make($request->password);

        $user = new User([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'password' => $password,
        ]);
        $user->save();

        $role_ids = explode(',', $request->role_ids);
        $user->assignRole($role_ids);

        // logs
        activity('create')
        ->performedOn($user)
        ->log(':causer.name has created user :subject.name');

        return redirect()->route('user.index')->with([
            'message_success' => 'User '.$user->name.' was created'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::findOrFail(decrypt($id));

        return view('pages.users.show')->with([
            'user' => $user
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $id = decrypt($id);
        $user = User::findOrFail($id);

        // ROLES
        $roles = Role::orderBy('name', 'ASC')
            ->get();

        $user_roles = $user->roles->pluck('id')->toArray();

        return view('pages.users.edit')->with([
            'roles' => $roles,
            'user' => $user,
            'user_roles' => $user_roles,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(UserEditRequest $request, $id)
    {
        $id = decrypt($id);
        $user = User::findOrFail($id);
        $user_roles = $user->roles->pluck('id')->toArray();

        $changes_arr['old'] = $user->getOriginal();
        $changes_arr['old']['arr'] = $user->roles->pluck('name');

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        $role_ids = explode(',', $request->role_ids);
        $user->syncRoles($role_ids);

        $changes_arr['changes'] = $user->getChanges();
        $changes_arr['changes']['arr'] = $user->roles->pluck('name');

        // logs
        activity('update')
        ->performedOn($user)
        ->withProperties($changes_arr)
        ->log(':causer.name has updated user :subject.name');

        return back()->with([
            'message_success' => 'User '.$user->name.' was updated'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
    }
}
