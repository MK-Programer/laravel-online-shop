<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AdminLoginController extends Controller
{
    public function index()
    {
        return view('admin.auth.login');
    }

    public function authenticate(LoginRequest $request)
    {
        try
        {
            if(Auth::guard('admin')->attempt(['email' => $request->email, 'password' => $request->password], $request->get('remember')))
            {
                $admin = Auth::guard('admin')->user();
                if($admin->role_id == 2)
                {
                    return redirect()
                        ->route('admin.dashboard');
                }
                else
                {
                    Auth::guard('admin')->logout();
                    return redirect()
                        ->route('admin.login')
                        ->with('error', 'You are not authorized to access admin panel.');
                }
            }
            else
            {
                return redirect()
                    ->route('admin.login')
                    ->with('error', 'Either Email/Password is incorrect.')
                    ->withInput($request->only('email'));
            }
            
        }
        catch(ValidationException $e)
        {
            return redirect()
                ->route('admin.login')
                ->withErrors($e->errors())
                ->withInput($request->only('email'));
        }
    }
}
