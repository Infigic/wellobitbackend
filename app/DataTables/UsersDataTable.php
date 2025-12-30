<?php

namespace App\DataTables;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class UsersDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('profile_image', function ($query) {
                return $query->profile_image_url ? '<img src="' . $query->profile_image_url . '" alt="' . $query->name . '" class="rounded-circle" width="60" height="60">' : null;
            })
            ->editColumn('role', function ($query) {
                return ucfirst($query->role);
            })
            ->editColumn('is_active', function ($query) {
                return '<label class="badge ' . ($query->is_active ? 'badge-outline-success' : 'badge-outline-danger') . '">' . ($query->is_active ? 'Active' : 'Inactive') . '</label>';
            })
            ->rawColumns(['profile_image', 'role', 'is_active', 'action'])
            ->addColumn('action', function ($query) {
                return '<div class="d-flex gap-3 justify-content-center">
                    <a href="' . route('users.togglestatus', ['user' => $query->id]) . '" title="Toggle Status"
                       class="btn btn-sm btn-' . ($query->is_active ? 'danger' : 'success') . '"><i class="mdi mdi-' . $query->status_class_name . '"></i></a>
                    <a href="' . route('users.edit', ['user' => $query->id]) . '" title="Edit"
                       class="btn btn-sm btn-info"><i class="mdi mdi-pencil"></i></a>
                    <a href="javascript:void(0)" title="Soft Delete"
                       onclick="event.preventDefault(); document.getElementById(\'delete-form-' . $query->id . '\').submit()"
                       class="btn btn-sm btn-warning"><i class="mdi mdi-delete"></i></a>
                    <a href="javascript:void(0)" title="Hard Delete"
                       onclick="event.preventDefault(); document.getElementById(\'force-delete-form-' . $query->id . '\').submit()"
                       class="btn btn-sm btn-danger"><i class="mdi mdi-delete-forever"></i></a>
                    <form id="delete-form-' . $query->id . '"
                          action="' . route('users.destroy', ['user' => $query->id]) . '"
                          method="POST" style="display: none;">
                          ' . csrf_field() . '
                          ' . method_field('DELETE') . '
                    </form>
                    <form id="force-delete-form-' . $query->id . '"
                          action="' . route('users.forceDelete', ['user' => $query->id]) . '"
                          method="POST" style="display: none;">
                          ' . csrf_field() . '
                          ' . method_field('DELETE') . '
                    </form>
                    </div>
                ';
            })
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(User $model): QueryBuilder
    {
        return $model->newQuery();
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
            Column::make('profile_image')->title('Image'),
            Column::make('name'),
            Column::make('email'),
            Column::make('role'),
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
        return 'Users_' . date('YmdHis');
    }
}
