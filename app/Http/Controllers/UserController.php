<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Update user
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function update(Request $request) {
        $user = Auth::user();
        $user->update($request->all());

        return $user;
    }

    /**
     * Show user
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function show() {
        return Auth::user();
    }

    /**
     * Profile page
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function profile() {
        return view('users.profile', ['user' => Auth::user()]);
    }
}
