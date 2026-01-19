<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Models\TrainingEvents;
use App\Models\User;
use App\Models\OuSetting;
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
        $today = now();
        // Group training events by student_id
        $students = TrainingEvents::select('student_id')
            ->groupBy('student_id')
            ->get();

        foreach ($students as $student) {
           
            $events = TrainingEvents::where('student_id', $student->student_id)->get();

            // Skip if no events found
            if ($events->isEmpty()) continue;

            $totalEvents = $events->count();
            $completedEvents = $events->whereNotNull('course_end_date')->count();

            // Proceed only if all events are completed
            if ($completedEvents === $totalEvents) {

                // Get the latest (most recent) course_end_date
                $latestEndDate = $events->max('course_end_date'); 

                // Check if last event ended at least 1 month ago
                $ou_id =  User::where('id', $student->student_id)->pluck('ou_id')->first();
                
                $ou_setting = OuSetting::where('organization_id', $ou_id)->first();

                
                if($ou_setting->auto_archive == 1){    
                    $archive_after_months = $ou_setting->archive_after_months;
                  
                    if ($latestEndDate && $today->diffInMonths($latestEndDate) >= $archive_after_months) {
                    $user = User::find($student->student_id);
                    if ($user && $user->role == 3 && $user->unarchived_by ==NULL) {   
                        $user->update(['is_activated' => 1]);
                    }
                }
                }

             
            }
        }
    }
}
