<?php

namespace Modules\Service\Admin;

use Modules\Admin\Ui\AdminTable;
use Modules\Service\Entities\Service;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Exceptions\Exception;

class ServiceTable extends AdminTable
{
    protected array $rawColumns = ['status_web'];

    /**
     * Make table response for the resource.
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function make()
    {
        return $this->newTable()
            ->addColumn('logo', function (Service $service) {
                return view('admin::partials.table.image', [
                    'file' => $service->logo,
                ]);
            })
            ->addColumn('status_web', function (Service $service) {
                return $service->is_active_web
                    ? '<span class="badge badge-success">' . trans('admin::admin.table.active') . '</span>'
                    : '<span class="badge badge-danger">' . trans('admin::admin.table.inactive') . '</span>';
            });
    }
}
