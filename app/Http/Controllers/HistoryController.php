<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{
    /**
     * Get current user history
     *
     * @return mixed
     */
    public function index() {
        return Auth::user()->notifications()->paginate(50);
    }
}
