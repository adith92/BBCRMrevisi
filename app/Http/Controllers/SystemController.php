<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class SystemController extends Controller
{
    public function seedDemo(Request $request)
    {
        $request->validate([
            'amount' => ['required', 'integer', 'min:1', 'max:100000'],
        ]);

        $lock = Cache::lock('seed_demo_data', 60);

        if (!$lock->get()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Seeding is currently in progress. Please wait.'
            ], 423);
        }

        try {
            config(['app.demo_append_amount' => (int) $request->amount]);
            \Illuminate\Support\Facades\Artisan::call('db:seed', [
                '--class' => 'DemoMassiveSeeder'
            ]);
        } finally {
            $lock->release();
        }

        return response()->json(['status' => 'success']);
    }

    public function switchDemoUser(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $user = User::whereKey($request->integer('user_id'))
            ->whereIn('role', ['gm', 'manager', 'sales', 'operational', 'pool', 'finance'])
            ->firstOrFail();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Demo role switched to ' . $user->name . '.');
    }
}
