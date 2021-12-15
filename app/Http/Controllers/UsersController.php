<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Support\Facades\Hash;
use Validator;
use Image;

class UsersController extends Controller
{
    /**
     * Display all users
     * 
     * @return \Illuminate\Http\Response
     */
    public function index() 
    {
        $users = User::latest()->paginate(10);

        return view('users.index', compact('users'));
    }

    /**
     * Show form for creating user
     * 
     * @return \Illuminate\Http\Response
     */
    public function create() 
    {
        return view('users.create');
    }

    /**
     * Store a newly created user
     * 
     * @param User $user
     * @param StoreUserRequest $request
     * 
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email:rfc,dns|unique:users,email',
            'username' => 'required|unique:users,username',
            'password' => 'required',
        ]);
        if ($validator->fails()) { 
            return redirect()->route('users.store')
                                ->withErrors($validator)
                                ->withInput();
        }

        if($request->hasFile('profile_image')) {
            $image = $request->file('profile_image');
            $imagename = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/users/thumbnail');
            
            $img = Image::make($image->getRealPath());

            // crop image
            $img->crop($request->input('w'), $request->input('h'), $request->input('x1'), $request->input('y1'));
            $img->save($destinationPath.'/'.$imagename);

            $destinationPath = public_path('/users/original');
            $image->move($destinationPath, $imagename);
     
            // you can save crop image path below in database
            $path = asset('public/users/thumbnail/'.$imagename);

        }

        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->username = $request->username;
        $user->password = Hash::make($request->password);
        $user->image = $imagename;
        $user->save();


        return redirect()->route('users.index')
            ->withSuccess(__('User created successfully.'));
    }

    /**
     * Show user data
     * 
     * @param User $user
     * 
     * @return \Illuminate\Http\Response
     */
    public function show(User $user) 
    {
        return view('users.show', [
            'user' => $user
        ]);
    }

    /**
     * Edit user data
     * 
     * @param User $user
     * 
     * @return \Illuminate\Http\Response
     */
    public function edit($id) 
    {
        $user = User::find($id);

        return view('users.edit', [
            'user' => $user,
            'userRole' => $user->roles->pluck('name')->toArray(),
            'roles' => Role::latest()->get()
        ]);
    }

    /**
     * Update user data
     * 
     * @param User $user
     * @param UpdateUserRequest $request
     * 
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email:rfc,dns|unique:users,email,'.$request->id,
            'username' => 'required|unique:users,username,'.$request->id,
            'password' => 'required',
        ]);
        if ($validator->fails()) { 
            return redirect()->route('users.edit'.'/'.$request->id)
                                ->withErrors($validator)
                                ->withInput();
        }
        $user = User::find($request->id);

        if($request->hasFile('profile_image')) {
            if (!empty($user->image)) {
                $file1 = public_path().'/users/thumbnail/'.$user->image;
                $file2 = public_path().'/users/original/'.$user->image;
                File::delete($file1, $file2);
            }

            $image = $request->file('profile_image');
            $imagename = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/users/thumbnail');
            
            $img = Image::make($image->getRealPath());

            // crop image
            $img->crop($request->input('w'), $request->input('h'), $request->input('x1'), $request->input('y1'));
            $img->save($destinationPath.'/'.$imagename);

            $destinationPath = public_path('/users/original');
            $image->move($destinationPath, $imagename);
     
            // you can save crop image path below in database
            $path = asset('public/users/thumbnail/'.$imagename);

        }else{
            $imagename = $user->image;
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->username = $request->username;
        $user->password = $request->password;
        $user->image = $imagename;
        $user->save();

        $user->syncRoles($request->get('role'));

        return redirect()->route('users.index')
            ->withSuccess(__('User updated successfully.'));
    }

    /**
     * Delete user data
     * 
     * @param User $user
     * 
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user) 
    {
        $user->delete();

        return redirect()->route('users.index')
            ->withSuccess(__('User deleted successfully.'));
    }

    public function logout(User $user)
    {
        User::where('id',$user->id)->update(['login_status'=>'0']);

        return redirect()->route('users.index')->withSuccess(__('User logged out successfully.'));

    }
}