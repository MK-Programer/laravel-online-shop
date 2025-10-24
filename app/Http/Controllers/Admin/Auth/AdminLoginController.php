<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdminLoginController extends Controller
{
    public function index()
    {
        return view('admin.auth.login');
    }

    public function authenticate(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'email' => 'required|email',
                'password' => 'required',
            ],
        );

        if($validator->passes())
        {
            if(Auth::guard('admin')->attempt(['email' => $request->email, 'password' => $request->password], $request->get('remember')))
            {
                $admin = Auth::guard('admin')->user();
                if($admin->role == 2)
                {
                    return redirect()
                        ->route('admin.dashboard');
                }
                else
                {
                    Auth::guard('admin')->logout();
                    return redirect()
                        ->back()
                        ->with('error', 'You are not authorized to access admin panel.');
                }
            }
            else
            {
                return redirect()
                    ->back()
                    ->with('error', 'Either Email/Password is incorrect.');
            }
        }
        else
        {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput($request->only('email'));
        }
    }
}
