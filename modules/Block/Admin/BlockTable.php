<?php
namespace Modules\Block\Admin;

use Modules\Admin\Ui\AdminTable;
use Modules\Block\Entities\Block;

class BlockTable extends AdminTable
{
    /**
     * Make table response for the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function make()
    {
        return $this->newTable()
        ->addColumn('logo', function (Block $block) {
            return view('admin::partials.table.image', [
                'file' => $block->logo,
            ]);
        });
    }
}
