<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/',
    function (){
        return redirect()->route('filament.admin.pages.dashboard');
    });


Route::get('/demo-login',
    function (){
        // abort_if(App::environment('production'), 404);
        $user = User::where('email',
            'demo@topall.sn')
            ->firstOrFail();
        Auth::login($user,
            true);
        return redirect('/admin');
    })
    ->name('demo.login');


Route::get('/demo-logout',
    function (Request $request){
        Auth::logout();
        $request->session()
            ->invalidate();
        $request->session()
            ->regenerateToken();

        return redirect('/admin/login'); // or route('filament.admin.auth.login')
    })
    ->name('demo.logout');
