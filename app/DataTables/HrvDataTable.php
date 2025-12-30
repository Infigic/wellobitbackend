<?php

namespace App\DataTables;

use App\Models\Hrv;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class HrvDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->filterColumn('customer', function ($query, $keyword) {
                $query->whereHas('user', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('status', function ($query, $keyword) {
                $query->where('status', $keyword);
            })
            ->filterColumn('datetime', function ($query, $keyword) {
                $dates = explode('|', $keyword);
                if (count($dates) === 2) {
                    $query->whereBetween('datetime', [$dates[0], $dates[1]]);
                }
            })
            ->editColumn('status', function ($query) {
                return '<label class="badge badge-pill badge-' . $query->status_class_name . '">' . $query->status_name . '</label>';
            })
            ->editColumn('datetime', function ($query) {
                return $query->datetime->format('F j, Y \a\t g:i A');
            })
            // ->addColumn('customer', function ($query) {
            //     return $query->user->name;
            // })
            ->rawColumns(['status', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Hrv $model): QueryBuilder
    {
        return $model->newQuery()->with(['user']);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('hrv-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            //->dom('Bfrtip')
            ->orderBy(0, 'desc')
            ->selectStyleSingle();
        // ->buttons([
        //     Button::make('excel'),
        //     Button::make('csv'),
        //     Button::make('pdf'),
        //     Button::make('print'),
        //     Button::make('reset'),
        //     Button::make('reload')
        // ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id')->title('ID'),
            Column::make('customer')->data('user.name')->name('user.name'),
            Column::make('sample_id')->title('Sample ID'),
            Column::make('device_timestamp')->title('Device Timestamp'),
            Column::make('hrv')->title('HRV'),
            Column::make('sdnn')->title('SDNN'),
            Column::make('status'),
            Column::make('datetime')->title('DateTime'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Hrv_' . date('YmdHis');
    }
}
