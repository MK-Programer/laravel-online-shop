<?php

namespace App\Http\Controllers\Customer\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function index()
    {
        return view('customer.auth.login');
    }

    public function authenticate(LoginRequest $request)
    {
        try
        {
            if(Auth::attempt(['email' => $request->email, 'password' => $request->password], $request->remember))
            {
                return redirect()
                    ->route('customer.profile');
            }
            else
            {
                return redirect()
                    ->route('customer.login')
                    ->with('error', 'Either Email/Password is incorrect.')
                    ->withInput($request->only('email'));
            }
        }
        catch(ValidationException $e)
        {
            return redirect()
                ->route('customer.login')
                ->withErrors($e->errors())
                ->withInput($request->only('email'));
        }
    }
}
