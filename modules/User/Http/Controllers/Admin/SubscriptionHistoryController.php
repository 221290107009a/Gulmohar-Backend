<?php

namespace Modules\User\Http\Controllers\Admin;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Modules\User\Entities\User;
use Modules\User\Entities\SubscriptionHistory;
use Modules\Admin\Traits\HasCrudActions;
use Modules\User\Http\Requests\SaveUserDataRequest;
use Cartalyst\Sentinel\Laravel\Facades\Activation;
use Modules\User\Entities\UserData;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use DataTables;
use Carbon\Carbon;
use DB;
use PDF;


class SubscriptionHistoryController extends Controller
{
    use HasCrudActions;

    /**
     * Model for the resource.
     *
     * @var string
     */
    protected $model = SubscriptionHistory::class;

    /**
     * Label of the resource.
     *
     * @var string
     */
    protected $label = 'SubscriptionHistory';

    /**
     * View path of the resource.
     *
     * @var string
     */
    protected $viewPath = 'user::admin.subscription_histories';

    /**
     * Form requests for the resource.
     *
     * @var array|string
     */
    protected $validation = SaveUserDataRequest::class;

    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * 
     */
    
    public function table(Request $request)
    {
        if ($request->ajax()) {
            $data = DB::table('subscription_histories')->get();
            return Datatables::of($data)->addIndexColumn()
            ->editColumn('user_id', function ($entity) {
                $memberlistUrl = route('admin.member.edit', $entity->user_id);        
                return "<a href='{$memberlistUrl}'>{$entity->user_id}</a>";
            })
            ->editColumn('status', function ($entity) {
                $start_date = Carbon::parse($entity->start_date);
                $end_date = Carbon::parse($entity->end_date);
                $current_date_time = Carbon::now();
                
                if ($current_date_time->between($start_date, $end_date)) {
                    return '<span class="badge badge-success">Active</span>';
                }
                return '<span class="badge badge-warning">In-Active</span>';
            })
            ->rawColumns(['user_id' ,'status'])
            ->make(true);
        }

        if ($request->has('query')) {
            return $this->getModel()
                ->search($request->get('query'))
                ->query()
                ->limit($request->get('limit', 10))
                ->get();
        }

        if ($request->has('table')) {
            return $this->getModel()->table($request);
        }

        return view("user::admin.subscription_histories.index");
    }
    
    public function show($id)
    {
        $subscriptionHistory = SubscriptionHistory::find($id);

        return view('user::admin.subscription_histories.show', compact('subscriptionHistory'));
    }
    
}