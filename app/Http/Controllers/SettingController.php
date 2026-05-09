<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->groupBy('group');
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->except('_token', '_method');
        foreach ($data as $key => $value) {
            Setting::setValue($key, $value);
        }
        return redirect()->route('settings.index')->with('success', 'Settings updated successfully!');
    }
}
