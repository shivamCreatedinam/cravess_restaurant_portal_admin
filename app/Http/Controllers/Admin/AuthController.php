<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    public function __construct()
    {
        // Redirect to dashboard if the user is already authenticated
        $this->middleware(function ($request, $next) {
            if (Auth::check()) {
                return redirect()->route('dashboard');
            }
            return $next($request);
        })->except('logout'); // Exclude the logout method
    }

    public function loginView()
    {
        return view("auth.login");
    }
    public function loginPost(Request $request)
    {
        $request->validate([
            "email" => "required|email|exists:users,email",
            "password" => "required",
        ]);
        $credentials = $request->only(["email", "password"]);
        try {
            if (Auth::attempt($credentials)) {
                $user = Auth::user();
                if ($user->role == "store") {
                    return redirect()->route('dashboard')->with("success", "Login Successfull.");
                } else {
                    Auth::logout();
                    return redirect()->back()->with("error", "Unauthorized Access.");
                }
            } else {
                return redirect()->back()->with("error", "Password Incorrect or Some Error Occured.");
            }
        } catch (Exception $e) {
            return redirect()->back()->with("error", $e->getMessage());
        }
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login')->with("success", "Logout Successfully!");
    }
}
