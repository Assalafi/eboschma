<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LoginController extends Controller
{
    //
    public function sessionUpdate(Request $request)
    {
       session()->put('session', $request->session);
       session()->put('sector', $request->sector);

       return redirect()->back()->with('success', 'Session Updated');
    }
}
