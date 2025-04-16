<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SettingController extends Controller
{
    public function showChangePasswordForm() {
        return view('admin.change-password');
    }

    public function handleChangePw(Request $request) {
        $validator = Validator::make($request->all(),[
            'old_password' => 'required|min:8|max:24',
            'new_password' => 'required|min:8|max:24',
            'confirm_password' => 'required|same:new_password'
        ]);

        $admin = User::where('id',Auth::guard('admin')->user()->id)->first();
        if($validator->passes()) {
            if(!Hash::check($request->old_password,$admin->password)) {
                session()->flash('error','Old password is not correct');
                return response()->json([
                    'status' => true,
                ]);
            }
            $admin->password = Hash::make($request->new_password);
            $admin->save();
            session()->flash('success','Password updated successfully.');
            return response()->json([
                'status' => true,
                'message' => 'Password updated successfully.'
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }
}
