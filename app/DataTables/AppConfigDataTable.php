<?php 

namespace App\DataTables;

use App\Models\AppConfig;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class AppConfigDataTable extends DataTable
{
       /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($config) {
                return '
                     <a href="' . route('app-configs.togglestatus', ['app_config' => $config->id]) . '" title="Toggle Status"
                       class="btn btn-sm btn-' . ($config->is_active ? 'danger' : 'success') . '"><i class="mdi mdi-' . ($config->is_active ? 'toggle-switch' : 'toggle-switch-off') . '"></i></a>
                    <a href="' . route('app-configs.edit', $config->id) . '" 
                       class="btn btn-sm btn-info"><i class="mdi mdi-pencil"></i></a>
                    <form action="' . route('app-configs.destroy', $config->id) . '" 
                        method="POST" style="display:inline"
                        onsubmit="return confirm(\'Delete this config?\')">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button class="btn btn-sm btn-danger">
                            <i class="mdi mdi-delete"></i>
                        </button>
                    </form>
                ';
            })
            ->editColumn('is_active', function ($query) {
                return '<label class="badge ' . ($query->is_active ? 'badge-outline-success' : 'badge-outline-danger') . '">' . ($query->is_active ? 'Active' : 'Inactive') . '</label>';
            })
            ->rawColumns(['action', 'is_active'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(AppConfig $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('app-config-table')
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
            Column::make('config_key')->title('Config Name'),
            Column::make('config_value')->title('Config Value'),
            Column::make('value_type')->title('Value Type'),
            Column::make('description')->title('Description'),
            Column::make('is_active')->title('Active'),
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
        return 'AppConfig_' . date('YmdHis');
    }
}