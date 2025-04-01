<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserActivityLog;
use Yajra\DataTables\DataTables;

class UserActivityLogController extends Controller
{
    public function showAll()
    {
        return view('activity_logs.index');
    }

    public function getLogs(Request $request)
    {
        $logs = UserActivityLog::with('user:id,fname,lname')
            ->select(['id', 'log_type', 'description', 'created_at', 'user_id'])
            ->orderBy('id', 'desc');

        if ($request->ajax()) {
            return DataTables::of($logs)
                ->addColumn('user_name', function ($log) {
                    return $log->user ? $log->user->fname . ' ' . $log->user->lname : 'N/A';
                })
                ->make(true);
        }

        return response()->json([
            'draw' => $request->draw,
            'recordsTotal' => $logs->count(),
            'recordsFiltered' => $logs->count(),
            'data' => $logs->get()
        ]);
    }
}
