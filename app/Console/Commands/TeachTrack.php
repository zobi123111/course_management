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

        Log::info('TeachTrack command STARTED at ' . now());

        $users = User::with(['roles', 'TeachTrack'])
            ->whereHas('roles', function ($q) {
                $q->whereIn('role_name', ['Instructor', 'Examiner']);
            })
            ->get();

        foreach ($users as $user) {

            $ouSetting = OuSetting::where('organization_id', $user->ou_id)->first();

            if (!$ouSetting || !$ouSetting->teachtrack_enabled) {
                continue;
            }

            // -- Get the latest training record for the user
            $latestTraining = $user->TeachTrack()
                ->orderByDesc('created_at')
                ->first();

            $validityMonths = $ouSetting->teachtrack_validity_months ?? 12;
            $alertDays = $ouSetting->teachtrack_alert_days ?? 30;

            $status = null;

            if (!$latestTraining || !$latestTraining->validation_date) {

                $status = 'lapsed';

            } else {

                $lastTrainingDate = Carbon::parse($latestTraining->validation_date);

                // 1️⃣ LAPSED: no training in last 12 months
                if ($lastTrainingDate->diffInMonths(now()) >= $validityMonths) {

                    $status = 'lapsed';

                } else {

                    // 2️⃣ EXPIRY BASED DIRECTLY ON VALIDATION DATE
                    $expiryDate = $lastTrainingDate;

                    $daysLeft = now()->diffInDays($expiryDate, false);

                    if ($daysLeft < 0) {

                        $status = 'lapsed';

                    } elseif ($daysLeft <= $alertDays) {

                        $status = 'expiring_soon';

                    } else {

                        $status = 'valid';
                    }
                }
            }

            // --

            Log::info('User Name: ' . $user->name . ' | Email: ' . $user->email . ' | Status: ' . $status);
            $ouAdmins = User::where('ou_id', $user->ou_id)
                        ->where('is_admin', 1)
                        ->pluck('email')
                        ->toArray();

            if ($ouSetting->teachtrack_email_enabled) {

                if (in_array($status, ['lapsed', 'expiring_soon'])) {

                    try {
                        Mail::to($user->email)
                            ->send(new TeachTrackAlertMail($user, $status, 'user', $latestTraining));

                        if (!empty($ouAdmins)) {
                            Mail::to($ouAdmins)
                                ->send(new TeachTrackAlertMail($user, $status, 'admin', $latestTraining));
                        }

                        Log::info("TeachTrack email sent to user + admins for {$user->email}");

                    } catch (\Exception $e) {
                        Log::error("MAIL FAILED for {$user->email}: " . $e->getMessage());
                    }
                }
            }
        }

        Log::info('TeachTrack command COMPLETED at ' . now());
        $this->info('TeachTrack cron executed successfully.');
    }
}
