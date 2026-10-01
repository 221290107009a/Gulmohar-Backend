<?php
namespace Modules\Seller\Admin;

use Modules\Admin\Ui\AdminTable;
use Modules\Seller\Entities\Seller;

class SellerTable extends AdminTable
{
    /**
     * Make table response for the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function make()
    {
        return $this->newTable()
        ->addColumn('logo', function (Seller $seller) {
            return view('admin::partials.table.image', [
                'file' => $seller->logo,
            ]);
        });
    }
}
