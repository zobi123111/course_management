<?php

namespace App\Console\Commands;

use App\Models\TrainingEvents;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class EndCourseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trainingevents:end';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'End course when validity expires';

    /**
     * Execute the console command.
     */
    // public function handle()
    // {
    //     $today = now();

    //     $events = TrainingEvents::with('course')
    //         ->where('is_locked', 0)
    //         ->get();

    //     foreach ($events as $event) {
    //         echo "<pre>";
            
    //         if ($event->course) {
    //             print_r($event->course->course_validity);
    //         } else {
    //             echo "No course found for Event ID {$event->id}";
    //         }

    //         echo "</pre>";

    //         Log::info("Course ended for Event ID {$event->id}");

    //     }
        
    //     dd($event); // stops on first iteration

    //     foreach ($events as $event) {

    //         if (
    //             $event->course &&
    //             $event->course->course_validity &&
    //             $event->course_end_validity
    //         ) {

    //             $validityEndDate = \Carbon\Carbon::parse($event->course_end_validity);

    //             if ($today->greaterThanOrEqualTo($validityEndDate)) {

    //                 $event->update([
    //                     'course_end_date' => now(),
    //                     'is_locked' => 1
    //                 ]);

    //                 log::info("Course ended for Event ID {$event->id}");
    //             }
    //         }
    //     }
    // }

    public function handle()
    {
        $today = now();

        $events = TrainingEvents::with('course')
            ->where('is_locked', 0)
            ->get();

        foreach ($events as $event) {

            if (
                $event->course &&
                $event->course->course_validity &&
                $event->course_end_validity
            ) {
                $baseDate = \Carbon\Carbon::parse($event->course_end_validity);

                $validityEndDate = $baseDate->copy()->addMonths($event->course->course_validity);

                if ($today->greaterThanOrEqualTo($validityEndDate)) {

                    $event->update([
                        'course_end_date' => now(),
                        'is_locked' => 1
                    ]);

                    Log::info("Course ended for Event ID {$event->id}");
                }
            }
        }
    }
}
