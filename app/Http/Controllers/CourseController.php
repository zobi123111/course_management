<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index()
    {
        return view('Courses.all_courses');
    }

    public function create_course()
    {
        return view('Courses.create_course');
    }

    public function store_course(Request $request)
    {
       dd($request->all());  
    }

}
