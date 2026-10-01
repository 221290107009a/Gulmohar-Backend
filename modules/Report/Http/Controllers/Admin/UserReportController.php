<?php

namespace Modules\Report\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Modules\Report\UsersReport;

class UserReportController
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return (new UsersReport)->render($request);
    }
}
