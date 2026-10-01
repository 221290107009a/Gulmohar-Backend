<?php

namespace Modules\User\Http\Controllers\Admin;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Modules\User\Entities\User;
use Modules\Admin\Traits\HasCrudActions;
use Modules\User\Http\Requests\SaveUserDataRequest;
use Cartalyst\Sentinel\Laravel\Facades\Activation;
use Modules\User\Entities\ProfilePictureHistory;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Modules\Address\Entities\Address;
use Modules\Account\Entities\DefaultAddress;
use Modules\User\Entities\ReferralUsage;
use Modules\User\Entities\UserData;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use DataTables;
use Carbon\Carbon;
use DB;
use PDF;


class MemberController extends Controller
{
    use HasCrudActions;

    /**
     * Model for the resource.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Label of the resource.
     *
     * @var string
     */
    protected $label = 'user::users.user';

    /**
     * View path of the resource.
     *
     * @var string
     */
    protected $viewPath = 'user::admin.member';

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
            $data = DB::table('users')
            ->select('users.*', 'user_roles.role_id')
            ->leftJoin('user_roles', 'users.id', '=', 'user_roles.user_id')
            ->whereNot('role_id', 1);
            return Datatables::of($data)->addIndexColumn()
            ->addColumn('checkbox', function ($entity) {
                return view('admin::partials.table.checkbox', compact('entity'))->render();
            })
            ->editColumn('created', function ($entity) {
                $created_at = Carbon::parse($entity->created_at);
                return view('admin::partials.table.date', ['date' => $created_at])->render();
            })
            ->rawColumns(['checkbox', 'last_login' ,'created'])
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

        return view("user::admin.member.index");
    }


    public function store(SaveUserDataRequest $request)
    {
        $allData = $request->all();
        $user = User::create($allData);

        if ($request->hasFile('profile')) {
            $file = $request->file('profile');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $image = Image::make($file)->encode($file->getClientOriginalExtension(), 50);
            
            Storage::put('profile_pictures/' . $filename, (string) $image);
            $profile = ProfilePictureHistory::create([
                'user_id' => $user->id,
                'image_url' => $filename,
                'active_profile' => 1,
            ]);        
        }

        $address = Address::create([
            'customer_id' => $user->id,
            'first_name' => $allData['fullname'],
            'last_name' => $allData['fullname'],
            'phone' => $allData['phone'],
            'address_1' => $allData['address_1'],
            'address_2' => $allData['address_2'],
            'area_type' => $allData['area_type'] ?? 'urban',
            'taluka' => $allData['taluka'] ?? null,
            'village' => ($allData['area_type'] ?? 'urban') === 'rural' ? ($allData['village'] ?? null) : null,
            'zip' => $allData['zip'] ?? '',
            'city' => $allData['city'],
            'state' => $allData['state'],
            'country' => 'IN',
        ]);
        DefaultAddress::create([
            'customer_id' => $user->id,
            'address_id' => $address->id,
        ]);

        $user->roles()->attach(2);

        Activation::complete($user, Activation::create($user)->code);

        return redirect()->route('admin.member.index')
            ->withSuccess(trans('admin::messages.resource_saved', ['resource' => trans('user::users.member')]));
    }

    public function update($id, SaveUserDataRequest $request)
    {        
        $user = User::findOrFail($id);        
        $user->update($request->all());

        if ($request->hasFile('profile')) {
            $file = $request->file('profile');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $image = Image::make($file)->encode($file->getClientOriginalExtension(), 50);
            $currentProfile = ProfilePictureHistory::where('user_id', $user->id)
                ->where('active_profile', 1)
                ->first();
            
            if ($currentProfile) {
                if ($currentProfile->image_url) {
                    Storage::delete('profile_pictures/' . $currentProfile->image_url);
                }
                $currentProfile->update([
                    'image_url' => $filename
                ]);
                $profile = $currentProfile;
            } else {
                $profile = ProfilePictureHistory::create([
                    'user_id' => $user->id,
                    'image_url' => $filename,
                    'active_profile' => 1,
                ]);
            }
            Storage::put('profile_pictures/' . $filename, (string) $image);
        }

        $defaultAddress = DefaultAddress::where('customer_id', $user->id)->first();
        $address = $defaultAddress ? Address::where('id', $defaultAddress->address_id)->first() : null;

        if ($address) {
            $address->update([
                'address_1' => $request['address_1'],
                'address_2' => $request['address_2'],
                'area_type' => $request['area_type'] ?? 'urban',
                'taluka' => $request['taluka'] ?? null,
                'village' => ($request['area_type'] ?? 'urban') === 'rural' ? ($request['village'] ?? null) : null,
                'zip' => $request['zip'],
                'city' => $request['city'],
                'state' => $request['state'],
            ]);
        } else {
            $address = Address::create([
                'customer_id' => $user->id,
                'first_name' => $request['fullname'] ?? $user->first_name ?? '',
                'last_name' => $request['fullname'] ?? $user->last_name ?? '',
                'phone' => $request['phone'] ?? $user->phone ?? '',
                'address_1' => $request['address_1'],
                'address_2' => $request['address_2'],
                'area_type' => $request['area_type'] ?? 'urban',
                'taluka' => $request['taluka'] ?? null,
                'village' => ($request['area_type'] ?? 'urban') === 'rural' ? ($request['village'] ?? null) : null,
                'zip' => $request['zip'] ?? '',
                'city' => $request['city'],
                'state' => $request['state'],
                'country' => 'IN',
            ]);

            if ($defaultAddress) {
                $defaultAddress->update([
                    'address_id' => $address->id,
                ]);
            } else {
                DefaultAddress::create([
                    'customer_id' => $user->id,
                    'address_id' => $address->id,
                ]);
            }
        }
        $request->roles = [2];
        $user->roles()->sync($request->roles);
        return redirect()->back()->withSuccess("member has been saved.");
    }

    public function generateICard($id)
    {
        if (isset($id) && $id != '') {
            $member = DB::table('users')
            ->select('users.id as main_id', 'users.*', 'user_datas.*')
            ->leftJoin('user_datas', 'users.id', '=', 'user_datas.user_id')
            ->where('users.id', $id)
            ->first();
            
            $data = ['member' => $member];
            $customPaper = array(0,0,243.00,153.00);
            $pdf = PDF::loadView('user::admin.member.icard', $data)->setPaper($customPaper, 'landscape');
            return $pdf->stream($member->first_name.'.pdf');
        }
    }

    public function downloadAll()
    {
        $members = DB::table('users')
            ->select('users.id as main_id', 'users.*', 'user_datas.*')
            ->leftJoin('user_datas', 'users.id', '=', 'user_datas.user_id')
            ->leftJoin('user_roles', 'users.id', '=', 'user_roles.user_id')
            ->where('user_roles.role_id', '2')
            ->get();       
    
        $pdf = PDF::loadView('user::admin.member.download-all', compact('members')); 
        return $pdf->stream('tets.pdf');
    }

}