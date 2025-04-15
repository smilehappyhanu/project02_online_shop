<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index (Request $request) {
        $users = User::latest();
        $users = $users->where('name','like','%'.$request->keyword_search.'%');
        $users = $users->orWhere('email','like','%'.$request->keyword_search.'%');
        $users = $users->paginate(10);
        return view('admin.user.list',compact('users'));
    }

    public function create () {
        return view('admin.user.create');
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'phone' => 'required|numeric',
            'password' => 'required|min:8|max:24',
            'status' => 'required'
        ]);
        if($validator->passes()) {
            $user = new User;
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->phone = $request->phone;
            $user->status = $request->status;
            $user->save();

            session()->flash('success','User created successfully.');
            return response()->json([
                'status' => true,
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function edit ($userId) {
        $user = User::find($userId);
        if($user == null) {
            session()->flash('error','User not found.');
            return redirect()->route('users.index');
        }
        return view('admin.user.edit',compact('user'));
    }

    public function update (Request $request,$userId) {
        $user = User::find($userId);
        if($user == null) {
            session()->flash('error','User not found.');
            return response()->json([
                'status' => true,
            ]);
        }

        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$user->id.',id',
            'phone' => 'required|numeric',
            'password' => 'required|min:8|max:24',
            'status' => 'required'
        ]);
        if($validator->passes()) {
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->phone = $request->phone;
            $user->status = $request->status;
            $user->save();

            session()->flash('success','User updated successfully.');
            return response()->json([
                'status' => true,
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function destroy($id) {
        $user = User::find($id);
        if($user == null) {
            session()->flash('error','User not found.');
            return response()->json([
                'status' => true,
            ]);
        }
        $user->delete();
        session()->flash('success','User deleted successfully.');
            return response()->json([
                'status' => true,
        ]);
    }
}
