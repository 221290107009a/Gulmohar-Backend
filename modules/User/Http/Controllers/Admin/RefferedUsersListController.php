<?php

namespace Modules\User\Http\Controllers\Admin;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Modules\User\Entities\User;
use Modules\User\Entities\ReferralUsage;
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


class RefferedUsersListController extends Controller
{
    use HasCrudActions;

    /**
     * Model for the resource.
     *
     * @var string
     */
    protected $model = ReferralUsage::class;

    /**
     * Label of the resource.
     *
     * @var string
     */
    protected $label = 'ReferralUsage';

    /**
     * View path of the resource.
     *
     * @var string
     */
    protected $viewPath = 'user::admin.reffered_users_list';

    public function table(Request $request)
    {
        if ($request->ajax()) {
            $data = ReferralUsage::with(['referredUser','referrer'])->latest();

            return Datatables::of($data)
                ->addIndexColumn()
                ->filterColumn('user_id', function($query, $keyword) {
                    $query->where('referred_user_id', 'like', "%{$keyword}%");
                })
                ->editColumn('user_id', function ($entity) {
                    $memberlistUrl = route('admin.member.edit', $entity->referred_user_id);        
                    return "<a href='{$memberlistUrl}'>{$entity->referred_user_id}</a>";
                })
                ->editColumn('referral_share_type', function ($entity) {
                    if (!$entity->referral_share_type) {
                        return '';
                    }
                    $formatted = collect(explode('-', $entity->referral_share_type))
                        ->map(function ($word) {
                            return ucfirst($word);
                        })
                        ->join(' ');
                    return $formatted;
                })
                ->addColumn('referred_by', function ($entity) {
                    $arrayReferrerUser = $entity->referrer->toArray();
                    return $arrayReferrerUser['fullname'];
                })
                ->addColumn('referred_fullname', function ($entity) {
                    if ($entity->referredUser) {                        
                        $arrayUser = $entity->referredUser->toArray();
                        return $arrayUser['fullname'];
                    }
                    return '';
                })
                ->addColumn('email', function ($entity) {
                    if ($entity->referredUser) {
                        return $entity->referredUser->email;
                    }
                    return '';
                })
                ->addColumn('dob', function ($entity) {
                    if ($entity->referredUser) {
                        return date('d M Y', strtotime($entity->referredUser->dob));
                    } 
                    return '';
                })
                ->addColumn('gender', function ($entity) {
                    if ($entity->referredUser) {
                        return $entity->referredUser->gender;
                    }
                    return '';
                })
                ->editColumn('created', function ($entity) {
                    $created_at = $entity->created_at;
                    if (is_string($created_at)){
                        $created_at = Carbon::parse($created_at);
                    }
                    return view('admin::partials.table.date')->with('date', $created_at);
                })
                ->rawColumns(['user_id','created'])
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

        return view("user::admin.reffered_users_list.index");
    }
    
    public function show($id)
    {
        $referralUsage = ReferralUsage::find($id);
        return redirect()->route('admin.member.edit', $referralUsage->referred_user_id);
    }
    
}