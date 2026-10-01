<?php
namespace Modules\Country\Admin;

use Modules\Admin\Ui\AdminTable;
use Modules\Country\Entities\Country;

class CountryTable extends AdminTable
{
    /**
     * Make table response for the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function make()
    {
        return $this->newTable()
        ->addColumn('logo', function (Country $country) {
            return view('admin::partials.table.image', [
                'file' => $country->logo,
            ]);
        });
    }
}
