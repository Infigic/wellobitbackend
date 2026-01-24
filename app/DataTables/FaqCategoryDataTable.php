<?php

namespace App\DataTables;

use App\Models\FaqCategory;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class FaqCategoryDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($category) {
                return '
                    <a href="' . route('faq-categories.edit', $category->id) . '" 
                    class="btn btn-sm btn-info"><i class="mdi mdi-pencil"></i></a>

                    <form action="' . route('faq-categories.destroy', $category->id) . '" 
                        method="POST" style="display:inline"
                        onsubmit="return confirm(\'Delete this category?\')">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button class="btn btn-sm btn-danger">
                            <i class="mdi mdi-delete"></i>
                        </button>
                    </form>
                ';
            })
            ->rawColumns(['action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(FaqCategory $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('faq-category-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0, 'desc');
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id')->title('ID'),
            Column::make('name')->title('Category Name'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(120)
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
