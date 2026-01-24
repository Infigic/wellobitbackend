<?php

namespace App\DataTables;

use App\Models\Faq;
use App\Models\Quote;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class FaqDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($query) {
                return '<div class="d-flex gap-3 justify-content-center">
                    
                    <a href="' . route("faqs.edit", ["faq" => $query->id]) . '"
                       class="btn btn-sm btn-info"><i class="mdi mdi-pencil"></i></a>
                    <a href=""
                       onclick="event.preventDefault(); document.getElementById(\'delete-form-' . $query->id . '\').submit()"
                       class="btn btn-sm btn-danger"><i class="mdi mdi-delete"></i></a>
                    <form id="delete-form-' . $query->id . '"
                          action="' . route("faqs.destroy", ["faq" => $query->id]) . '"
                          method="POST" style="display: none;">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                    </form>
                    </div>
                ';
            })
            ->editColumn('category_id', function ($query) {
                return $query->category ? $query->category->name : '-';
            })
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Faq $model): QueryBuilder
    {
        return $model->newQuery()->with('category');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('faq-table')
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
            Column::make('title'),
            Column::make('subtitle'),
            Column::make('category_id')->title('Category'),
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
        return 'Faq_' . date('YmdHis');
    }
}
