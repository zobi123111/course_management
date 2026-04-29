<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Models\TrainingEvents;
use App\Models\User;
use App\Models\OuSetting;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\TeachTrackAlertMail;

class TeachTrack extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'teachtrack:check-completion';

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

        $users = User::with(['roles', 'TeachTrack'])
            ->whereHas('roles', function ($q) {
                $q->whereIn('role_name', ['Instructor', 'Examiner']);
            })
            ->get();

        foreach ($users as $user) {

            $ouSetting = OuSetting::where('ou_id', $user->ou_id)->first();

            if (!$ouSetting || !$ouSetting->teachtrack_enabled) {
                continue;
            }

            $validityMonths = $ouSetting->teachtrack_validity_months ?? 12;
            $alertDays = $ouSetting->teachtrack_alert_days ?? 30;

            $latestTraining = $user->TeachTrack()
                ->orderByDesc('created_at')
                ->first();

            $hasInitial = $user->TeachTrack()
                ->whereRaw('LOWER(TRIM(training_type)) = ?', ['initial'])
                ->exists();

            $status = null;

            if (!$hasInitial) {
                $status = 'no_initial';

            } elseif ($latestTraining) {
                $lastDate = Carbon::parse($latestTraining->validation_date);
                $lapseDate = $lastDate->copy()->addMonths($validityMonths);

                if ($today->greaterThan($lapseDate)) {
                    $status = 'lapsed';

                } elseif ($today->diffInDays($lapseDate, false) <= $alertDays) {
                    $status = 'expiring_soon';

                } else {
                    $status = 'valid';
                }

            } else {
                $status = 'no_training';
            }

            if ($ouSetting->teachtrack_email_enabled) {

                if (in_array($status, ['lapsed', 'expiring_soon'])) {

                    // OPTIONAL: store last_sent_at in DB to prevent spam
                    // if ($user->last_teachtrack_email_sent_at == today()) continue;

                    Mail::to($user->email)->send(new TeachTrackAlertMail($user, $status));

                    Log::info("TeachTrack email sent to {$user->email} - Status: {$status}");
                }
            }
        }

        $this->info('TeachTrack cron executed successfully.');
    }
}
