<?php

namespace App\DataTables;

use App\Models\UserTracking;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class DashboardDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('first_name', function ($userTracking) {
                return $userTracking->first_name
                    ? '<div class="user-cell">
                            <div class="user-name">' . e($userTracking->first_name) . '</div>
                            <div class="user-email">' . e($userTracking->email) . '</div>
                       </div>'
                    : '';
            })
            ->editColumn('installed_at', function ($userTracking) {
                return $userTracking->installed_at
                    ? '<div class="date-cell">' . $userTracking->installed_at->format('M d, Y H:i') . '</div>
                       <div class="date-label">' . $userTracking->installed_at->diffForHumans() . '</div>'
                    : '';
            })
            ->editColumn('first_breath_session_at', function ($userTracking) {
                return $userTracking->first_breath_session_at
                    ? '<div class="date-cell">' . $userTracking->first_breath_session_at->format('M d, Y H:i') . '</div>
                       <div class="date-label">' . $userTracking->first_breath_session_at->diffForHumans() . '</div>'
                    : '<div class="date-cell" style="color:#ef4444;">-</div>
                       <div class="date-label" style="color:#ef4444;">Never</div>';
            })
            ->editColumn('last_active_at', function ($userTracking) {
                return $userTracking->last_active_at
                    ? '<div class="date-cell">' . $userTracking->last_active_at->diffForHumans() . '</div>'
                    : '';
            })
            ->editColumn('is_paid', function ($userTracking) {
                if ($userTracking->is_paid) {
                    return '<span class="badge badge-green">Paid</span>';
                }

                if (
                    $userTracking->trial_started_at &&
                    $userTracking->trial_ends_at &&
                    $userTracking->trial_ends_at > now()
                ) {
                    return '<span class="badge badge-orange">Trial</span>';
                }

                return '<span class="badge badge-red">Expired</span>';
            })
            ->editColumn('primary_reason_to_use', function ($userTracking) {
                return $userTracking->primary_reason_to_use
                    ? '<span class="badge badge-orange">' . e($userTracking->primary_reason_to_use) . '</span>'
                    : '';
            })
            ->addColumn('channel', function ($userTracking) {
                if ($userTracking->acquisition && $userTracking->acquisition->acquisition_channel) {
                    return '<div class="channel-source">
                                <div class="channel">' . e($userTracking->acquisition->acquisition_channel) . '</div>
                                <div class="source">' . e($userTracking->acquisition->acquisition_source) . '</div>
                            </div>';
                }

                return '<div class="date-cell" style="color:#9ca3af;">-</div>';
            })
            ->addColumn('watch_model', function ($userTracking) {
                if ($userTracking->has_apple_watch && $userTracking->device) {
                    return '<span class="badge badge-purple">⌚ '
                        . e($userTracking->device->apple_watch_model)
                        . '</span>';
                }

                return '<span class="badge badge-gray">Not Connected</span>';
            })
            ->addColumn('total_session', function ($userTracking) {
                $count = $userTracking->user?->sessions?->count() ?? 0;

                return $count > 0
                    ? '<span class="badge badge-info">' . $count . '</span>'
                    : '<div class="date-cell" style="color:#9ca3af;">-</div>';
            })
            ->addColumn('stage', function ($userTracking) {
                if ($userTracking->first_breath_session_at) {
                    return '<span class="badge badge-blue">Activated</span>';
                }

                return '<span class="badge badge-red">Registered</span>';
            })

            ->rawColumns([
                'first_name',
                'installed_at',
                'first_breath_session_at',
                'last_active_at',
                'channel',
                'watch_model',
                'is_paid',
                'total_session',
                'primary_reason_to_use',
                'stage',
            ])

            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(UserTracking $model): QueryBuilder
    {
        $query = $model->newQuery()
            ->with(['device', 'user.sessions', 'acquisition']);

        $request = request();

        /*
        |------------------------------------------------------------------
        | Acquisition Channel
        |------------------------------------------------------------------
        */
        $query->when($request->filled('acquisition_channel'), function ($q) use ($request) {
            $channel = $request->acquisition_channel;

            if ($channel === 'unknown') {
                $q->where(function ($sub) {
                    $sub->whereDoesntHave('acquisition')
                        ->orWhereHas('acquisition', fn ($a) =>
                            $a->whereNull('acquisition_channel')
                        );
                });
            } else {
                $q->whereHas('acquisition', fn ($a) =>
                    $a->where('acquisition_channel', $channel)
                );
            }
        });

        /*
        |------------------------------------------------------------------
        | Subscription Status
        |------------------------------------------------------------------
        */
        $query->when($request->filled('subscription_status'), function ($q) use ($request) {
            match ($request->subscription_status) {

                'paid' => $q->where('is_paid', true),

                'trial' => $q->where('is_paid', false)
                    ->whereNotNull('trial_started_at')
                    ->whereNotNull('trial_ends_at')
                    ->where('trial_ends_at', '>', now()),

                'expired' => $q->where('is_paid', false)
                    ->whereNotNull('trial_ends_at')
                    ->where('trial_ends_at', '<=', now()),

                'free' => $q->where('is_paid', false)
                    ->whereNull('trial_started_at'),

                default => null,
            };
        });

        /*
        |------------------------------------------------------------------
        | Onboarding Stage
        |------------------------------------------------------------------
        */
        $query->when($request->filled('onboarding_stage'), function ($q) use ($request) {
            match ($request->onboarding_stage) {

                // User registered but NOT activated
                'registered' => $q->whereNull('first_breath_session_at'),

                // User activated (first session completed)
                'activated' => $q->whereNotNull('first_breath_session_at'),

                default => null,
            };
        });

        /*
        |------------------------------------------------------------------
        | Apple Watch
        |------------------------------------------------------------------
        */
        $query->when($request->filled('apple_watch'), function ($q) use ($request) {
            match ($request->apple_watch) {
                'connected'     => $q->where('has_apple_watch', true),
                'not_connected' => $q->where('has_apple_watch', false),
                default         => null,
            };
        });

        /*
        |------------------------------------------------------------------
        | Activity Status
        |------------------------------------------------------------------
        */
        $query->when($request->filled('activity_status'), function ($q) use ($request) {
            match ($request->activity_status) {
                'active_today' => $q->activateToday(),
                'active_7d'    => $q->where('last_active_at', '>=', now()->subDays(7)),
                'inactive_7d'  => $q->inActiveForDays(7),
                'inactive_30d' => $q->inActiveForDays(30),
                default        => null,
            };
        });

        /*
        |------------------------------------------------------------------
        | Primary Reason
        |------------------------------------------------------------------
        */
        $query->when($request->filled('primary_reason'), function ($q) use ($request) {
            $q->where('primary_reason_to_use', $request->primary_reason);
        });

        return $query;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('users-table')
            ->columns($this->getColumns())
            ->ajax([
                'data' => 'function (d) {
                    document.querySelectorAll(".dashboard-filter").forEach(el => {
                        if (el.value) {
                            d[el.name] = el.value;
                        }
                    });
                }'
            ])
            ->dom('rtip')
            ->orderBy(0, 'desc')
            ->pageLength(10);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('first_name')->title('USER'),
            Column::make('installed_at')->title('INSTALLED'),
            Column::make('first_breath_session_at')->title('FIRST SESSION'),
            Column::make('channel')->title('CHANNEL'),
            Column::make('watch_model')->title('WATCH MODEL'),
            Column::make('last_active_at')->title('LAST ACTIVE'),
            Column::make('total_session')->title('SESSIONS'),
            Column::make('is_paid')->title('PLAN'),
            Column::make('stage')->title('STAGE'),
            Column::make('primary_reason_to_use')->title('REASON'),

            // Column::computed('action')
            //     ->exportable(false)
            //     ->printable(false)
            //     ->width(60)
            //     ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Users_' . date('YmdHis');
    }
}
