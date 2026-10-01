<?php

namespace Modules\MembershipFee\Http\Controllers\Admin;

use Modules\Admin\Traits\HasCrudActions;
use Modules\MembershipFee\Entities\MembershipFee;
use Modules\MembershipFee\Http\Requests\SaveMembershipFeeRequest;
use Illuminate\Http\Request;

class MembershipFeeController
{
    use HasCrudActions;

    /**
     * Model for the resource.
     *
     * @var string
     */
    protected $model = MembershipFee::class;

    /**
     * Label of the resource.
     *
     * @var string
     */
    protected $label = 'membershipfee::membership_fees.membership_fee';

    /**
     * View path of the resource.
     *
     * @var string
     */
    protected $viewPath = 'membershipfee::admin.membership_fees';

    /**
     * Form requests for the resource.
     *
     * @var array
     */
    protected $validation = SaveMembershipFeeRequest::class;

    public function apiSubscriptionPlans()
    {   
        $subscriptionPlans = MembershipFee::where('is_active', 1)->get()->toArray();
        
        return response()->json([
            "success" => true,
            "data" => [
                "subscription_plans" => $subscriptionPlans
            ]
        ]);
    }
    
    public function apiConfirm(Request $request)
    {   
       return;
        
    }
}
