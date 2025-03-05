<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TrainingEventsController extends Controller
{
    public function index()
    {
        return view('trainings.index');
    }
}
