<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Upload;

class DashboardController extends Controller
{
    public function index()
    {
        $uploads = Upload::orderBy('id','desc')->get();
        return view('dashboard', compact('uploads'));
    }

    public function logs(Upload $upload)
    {
        $logs = $upload->importLogs()->with('product')->get();
        return view('logs', compact('upload', 'logs'));
    }
}
