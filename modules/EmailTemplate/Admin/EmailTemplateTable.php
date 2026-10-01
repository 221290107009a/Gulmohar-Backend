<?php
namespace Modules\EmailTemplate\Admin;

use Modules\Admin\Ui\AdminTable;
use Modules\EmailTemplate\Entities\EmailTemplate;

class EmailTemplateTable extends AdminTable
{
    /**
     * Make table response for the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function make()
    {
        return $this->newTable()
            ->editColumn('thumbnail', function ($file) {
                return view('emailtemplate::admin.emailtemplates.create', compact('file'));
            })
            ->addColumn('action', function ($file) {
                return view('emailtemplate::admin.emailtemplates.create', compact('file'));
            });
    }
}
