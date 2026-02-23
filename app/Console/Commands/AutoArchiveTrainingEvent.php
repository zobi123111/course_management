<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\TrainingEvents;

class AutoArchiveTrainingEvent extends Command 
{
 
    protected $signature = 'auto-archive-training-event';
    protected $description = 'Command description';

public function handle()
{
    try {

        $training_events = TrainingEvents::with('course')->get();

        foreach ($training_events as $row) {
            if ($row->course && $row->course->auto_archive == 1 && $row->is_locked == 1) {
                $row->update(['archive' => 1]);
              //  \Log::info('Event archived', ['event_id' => $row->id]);
            }
        }

        $this->info('Auto archive completed');

    } catch (\Exception $e) {
      //  \Log::error('Auto archive error', ['message' => $e->getMessage()]);
    }
}
}
