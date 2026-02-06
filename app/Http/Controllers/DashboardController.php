<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\DataTables\DashboardDataTable;
use App\Models\User;
use App\Models\UserTracking;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index(DashboardDataTable $dataTable)
    {
        // ===== Counters =====
        $totalUserTrackings = UserTracking::count();
        $activatedUsers     = UserTracking::activated()->count();
        $usersThisWeek      = UserTracking::thisWeek()->count();
        $activeToday        = UserTracking::activateToday()->count();

        $trialUsers         = UserTracking::trial()->count();
        $paidUsers          = UserTracking::paid()->count();
        $convertedUsers     = UserTracking::convertedToPaid()->count();

        $expiredTrialUsersButActive = UserTracking::trialExpiredButActive()
            ->where('last_active_at', '>=', now()->subDays(config('dashboard.day.ending_soon')))
            ->count();

        $trialEndingSoon = UserTracking::expiredTriallsSoon(
            config('dashboard.day.ending_soon')
        )->count();

        $inactive7Days = UserTracking::inActiveForDays(
            config('dashboard.day.at_risk'),
            config('dashboard.day.high_at_risk')
        )->count();

        $inactive30Days = UserTracking::inActiveForDays(
            config('dashboard.day.high_at_risk')
        )->count();

        $inCompleteSetup = UserTracking::incompletedSetup()->count();

        // ===== Rates =====
        $activationRate = $totalUserTrackings > 0
            ? round(($activatedUsers / $totalUserTrackings) * 100, 2)
            : 0;

        $noFirstSessionUsers = $totalUserTrackings - $activatedUsers;

        // ===== Totals for dashboard =====
        $totals = [
            'total_users_trackings'        => $totalUserTrackings,
            'users_this_week'              => $usersThisWeek,
            'total_activated_users'        => $activatedUsers,
            'activation_rate'              => $activationRate,
            'active_today'                 => $activeToday,
            'total_trial_users'            => $trialUsers,
            'total_paid_users'             => $paidUsers,
            'trial_to_paid_conversion_rate'=> $convertedUsers,
            'no_first_session_users'       => $noFirstSessionUsers,
            'expired_trial_users'          => $expiredTrialUsersButActive,
            'trial_ending_soon'            => $trialEndingSoon,
            'inactive_7_days'              => $inactive7Days,
            'inactive_30_days'             => $inactive30Days,
            'incomplete_setup_users'       => $inCompleteSetup,
        ];

        // ===== Filters (display only, no logic yet) =====
        $acquisitionChannels  = config('acquisition.channels');
        $subscriptionStatuses = config('subscription.statuses', []);
        $onboardingStages     = config('onboarding.stages', []);
        $appleWatchStatuses = config('apple_watch.statuses');
        $activityStatuses = config('activity.statuses');
        $primaryReasons = config('primary_reason.reasons');

        return $dataTable->render(
            'dashboard.index',
            compact(
                'totals',
                'acquisitionChannels',
                'subscriptionStatuses',
                'onboardingStages',
                'appleWatchStatuses',
                'activityStatuses',
                'primaryReasons'
            )
        );
    }

    /**
     * Export user tracking data to CSV.
     */
    public function export(Request $request)
    {
        $segment = $request->query('segment');

        // Build query based on segment
        $query = UserTracking::with(['device', 'user.sessions', 'acquisition']);

        switch ($segment) {
            case 'new-this-week':
                $query->thisWeek();
                break;
            case 'no-first-session':
                $query->whereNull('first_breath_session_at');
                break;
            case 'trial-ending':
                $query->expiredTriallsSoon(config('dashboard.day.ending_soon'));
                break;
            case 'inactive-7':
                $query->inActiveForDays(config('dashboard.day.at_risk'), config('dashboard.day.high_at_risk'));
                break;
            case 'inactive-30':
                $query->inActiveForDays(config('dashboard.day.high_at_risk'));
                break;
            case 'incomplete-setup':
                $query->incompletedSetup();
                break;
            case 'trial':
                $query->trial();
                break;
            case 'paid':
                $query->paid();
                break;
            case 'expired-trial':
                $query->trialExpiredButActive();
                break;
        }

        $users = $query->get();

        // Generate CSV
        $filename = 'user_tracking_' . ($segment ?? 'all') . '_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($users) {
            $file = fopen('php://output', 'w');

            // headers
            fputcsv($file, [
                'USER',
                'EMAIL',
                'INSTALLED',
                'FIRST SESSION',
                'CHANNEL',
                'SOURCE',
                'WATCH MODEL',
                'LAST ACTIVE',
                'SESSIONS',
                'PLAN',
                'STAGE',
                'REASON',
            ]);

            //  rows
            foreach ($users as $tracking) {
                // Determine plan status
                if ($tracking->is_paid) {
                    $plan = 'Paid';
                } elseif ($tracking->trial_started_at && $tracking->trial_ends_at && $tracking->trial_ends_at > now()) {
                    $plan = 'Trial';
                } else {
                    $plan = 'Expired';
                }

                // Determine stage
                if ($tracking->first_breath_session_at && $tracking->device && $tracking->device->apple_watch_model) {
                    $stage = 'Completed';
                } else {
                    $stage = 'Registered';
                }

                // Watch model
                $watchModel = $tracking->has_apple_watch && $tracking->device
                    ? $tracking->device->apple_watch_model ?? 'Connected'
                    : 'Not Connected';

                fputcsv($file, [
                    $tracking->first_name ?? 'N/A',
                    $tracking->email ?? 'N/A',
                    $tracking->installed_at ? $tracking->installed_at->format('M d, Y H:i') : 'N/A',
                    $tracking->first_breath_session_at ? $tracking->first_breath_session_at->format('M d, Y H:i') : 'Never',
                    $tracking->acquisition->acquisition_channel ?? '-',
                    $tracking->acquisition->acquisition_source ?? '-',
                    $watchModel,
                    $tracking->last_active_at ? $tracking->last_active_at->format('M d, Y H:i') . ' - ' . $tracking->last_active_at->diffForHumans() : 'N/A',
                    $tracking->user && $tracking->user->sessions ? $tracking->user->sessions->count() : 0,
                    $plan,
                    $stage,
                    $tracking->primary_reason_to_use ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
