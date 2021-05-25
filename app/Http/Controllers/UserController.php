<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function update(Request $request) {
        $user = Auth::user();
        $user->update($request->all());

        return $user;
    }

    public function show() {
        return Auth::user();
    }
}
