<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::first();
        return view('settings.index', compact('settings'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'text' => 'nullable|string',
            'font_type' => 'nullable|string',
            'font_size' => 'nullable|integer|min:8|max:72',
            'site_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'login_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $settings = Setting::firstOrNew();

        if ($request->hasFile('site_image')) {
            $file = $request->file('site_image');
            $originalName = time() . '_' . $file->getClientOriginalName(); // Prefixing with timestamp to avoid conflicts
            $filePath = $file->storeAs('settings', $originalName, 'public');
            $settings->site_image = $filePath;
        }

        if ($request->hasFile('login_image')) {
            $file = $request->file('login_image');
            $originalName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('settings', $originalName, 'public');
            $settings->login_image = $filePath;
        }

        $settings->text = $request->text;
        $settings->font_type = $request->font_type;
        $settings->font_size = $request->font_size;
        $settings->save();

        return redirect()->back()->with('success', 'Settings updated successfully!');
    }

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'text' => 'nullable|string',
    //         'font_type' => 'nullable|string',
    //         'font_size' => 'nullable|integer|min:8|max:72',
    //         'site_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    //         'login_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
    //     ]);

    //     $settings = Setting::firstOrNew();

    //     if ($request->hasFile('site_image')) {
    //         $SiteimagePath = $request->file('site_image')->store('settings', 'public');
    //         $settings->site_image = $SiteimagePath;
    //     }

    //     if ($request->hasFile('login_image')) {
    //         $LoginimagePath = $request->file('login_image')->store('settings', 'public');
    //         $settings->login_image = $LoginimagePath;
    //     }

    //     $settings->text = $request->text;
    //     $settings->font_type = $request->font_type;
    //     $settings->font_size = $request->font_size;
    //     $settings->save();

    //     return redirect()->back()->with('success', 'Settings updated successfully!');
    // }
}
