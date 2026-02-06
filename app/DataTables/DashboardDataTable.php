<?php

namespace App\DataTables;

use App\Models\User;
use App\Models\UserTracking;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
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
                return $userTracking->first_name ?
                    '<div class="user-cell">
                    <div class="user-name">' . $userTracking->first_name . '</div>
                    <div class="user-email">' . $userTracking->email . '</div>
                    </div>'
                    : '';
            })
            ->editColumn('installed_at', function ($userTracking) {
                return $userTracking->installed_at ?
                    '<div class="date-cell">' . $userTracking->installed_at->format('M d, Y H:i') . '</div>
                    <div class="date-label">' . $userTracking->installed_at->diffForHumans() . '</div>'
                    : '';
            })
            ->editColumn('first_breath_session_at', function ($userTracking) {
                return $userTracking->first_breath_session_at ?
                    '<div class="date-cell">' . $userTracking->first_breath_session_at->format('M d, Y H:i') . '</div>
                    <div class="date-label">' . $userTracking->first_breath_session_at->diffForHumans() . '</div>'
                    : '<div class="date-cell" style="color: #ef4444;">-</div>
                    <div class="date-label" style="color: #ef4444;">Never</div>';
            })
            ->editColumn('last_active_at', function ($userTracking) {
                return $userTracking->last_active_at ?
                    '<div class="date-cell">' . $userTracking->last_active_at->diffForHumans() . '</div>'
                    : '';
            })
            ->editColumn('is_paid', function ($userTracking) {
                if ($userTracking->is_paid == true) {
                    return '<span class="badge badge-green">' . 'Paid' . '</span>';
                } else {
                    if ($userTracking->trial_started_at != null && $userTracking->trial_ends_at != null && $userTracking->trial_ends_at > now()) {
                        return '<span class="badge badge-orange">' . 'Trial' . '</span>';
                    } else
                        return '<span class="badge badge-red">' . 'Expired' . '</span>';
                }
            })
            ->editColumn('primary_reason_to_use', function ($userTracking) {
                return $userTracking->primary_reason_to_use ?
                    '<span class="badge badge-orange">' . $userTracking->primary_reason_to_use . '</span>'
                    : '';
            })
            ->addColumn('channel', function ($userTracking) {
                return $userTracking->acquisition && $userTracking->acquisition->acquisition_channel ?
                    '<div class="channel-source">
                        <div class="channel">' . $userTracking->acquisition->acquisition_channel . '</div>
                        <div class="source">' . $userTracking->acquisition->acquisition_source  . '</div>
                    </div>'
                    : '<div class="date-cell" style="color: #9ca3af;">-</div>';
            })
            ->addColumn('watch_model', function ($userTracking) {
                return $userTracking->has_apple_watch ?
                    '<span class="badge badge-purple">⌚' . $userTracking->device->apple_watch_model . '</span>'
                    : '<span class=" badge badge-gray">Not Connected</span>';
            })
            ->addColumn('total_session', function ($userTracking) {
                $sessionCount = $userTracking->user ? $userTracking->user->sessions->count() : 0;
                return $sessionCount > 0 ?
                    '<span class="badge badge-info">' . $sessionCount . '</span>'
                    : '<div class="date-cell" style="color: #9ca3af;">-</div>';
            })
            ->addColumn('stage', function ($userTracking) {
                if (($userTracking->first_breath_session_at) && ($userTracking->device->apple_watch_model)) {
                    return '<span class="badge badge-blue">' . 'Completed' . '</span>';
                } else {
                    return '<span class="badge badge-red">' . 'Registered' . '</span>';
                }
            })
            // ->editColumn('is_active', function ($query) {
            //     return '<label class="badge ' . ($query->is_active ? 'badge-outline-success' : 'badge-outline-danger') . '">' . ($query->is_active ? 'Active' : 'Inactive') . '</label>';
            // })
            ->rawColumns(['first_name', 'installed_at', 'first_breath_session_at', 'last_active_at', 'channel', 'watch_model', 'is_paid', 'total_session', 'primary_reason_to_use', 'stage'])

            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(UserTracking $model): QueryBuilder
    {
        return $model->newQuery()
            ->with(['device', 'user.sessions', 'acquisition']);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('users-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('rtip')
            ->orderBy(0, 'desc')
            ->pageLength(10)
            ->selectStyleSingle();
        // ->buttons([
        //     // Button::make('excel'),
        //     Button::make('csv'),
        //     Button::make('pdf'),
        //     // Button::make('print'),
        // ]);
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
