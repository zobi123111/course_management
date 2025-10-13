<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Models\TrainingEvents;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class UnarchiveUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unarchive:user';

    /**
     * The console command description. 
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $endedEvents = TrainingEvents::whereNotNull('course_end_date')
                        ->orderByDesc('course_end_date')
                        ->get();

        $today = now(); // current date

        foreach ($endedEvents as $event) {
            // Check if course_end_date is older than 1 month
            if ($event->course_end_date && $today->diffInMonths($event->course_end_date) >= 1) {
                $user = User::find($event->student_id);

                if ($user && $user->status != 0) {
                    $user->update(['status' => 0]);
                }
            }
        }
    }
}
