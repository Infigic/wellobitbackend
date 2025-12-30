<?php

namespace App\DataTables;

use App\Models\Quote;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class QuoteDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('is_active', function ($query) {
                return '<label class="badge ' . ($query->is_active ? 'badge-outline-success' : 'badge-outline-danger') . '">' . ($query->is_active ? 'Active' : 'Inactive') . '</label>';
            })
            ->editColumn('type', function ($query) {
                return '<label class="badge badge-pill badge-' . $query->type_class_name . '">' . $query->type_name . '</label>';
            })
            // ->editColumn('state', function ($query) {
            //     $stateTypeClass = $query->state == 'calm' ? 'success' : ($query->state == 'balanced' ? 'warning' : ($query->state == 'recovery' ? 'secondary' : 'danger'));
            //     return '<label class="badge badge-pill badge-' . $stateTypeClass . '">' . $query->state . '</label>';
            // })
            ->addColumn('action', function ($query) {
                return '<div class="d-flex gap-3 justify-content-center">
                    <a href="' . route('quotes.togglestatus', ['quote' => $query->id]) . '" title="Toggle Status"
                       class="btn btn-sm btn-' . ($query->is_active ? 'danger' : 'success') . '"><i class="mdi mdi-' . $query->status_class_name . '"></i></a>
                    <a href="' . route("quotes.edit", ["quote" => $query->id]) . '"
                       class="btn btn-sm btn-info"><i class="mdi mdi-pencil"></i></a>
                    <a href=""
                       onclick="event.preventDefault(); document.getElementById(\'delete-form-' . $query->id . '\').submit()"
                       class="btn btn-sm btn-danger"><i class="mdi mdi-delete"></i></a>
                    <form id="delete-form-' . $query->id . '"
                          action="' . route("quotes.destroy", ["quote" => $query->id]) . '"
                          method="POST">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                    </form>
                    </div>
                ';
            })
            ->rawColumns(['is_active', 'type', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Quote $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('quote-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            // ->dom('Bfrtip')
            ->orderBy(0, 'desc')
            ->selectStyleSingle();
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id')->title('ID'),
            Column::make('quote'),
            Column::make('type'),
            Column::make('is_active')->title('Status'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(60)
                ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Quote_' . date('YmdHis');
    }
}
