<?php

namespace Modules\User\Http\Controllers\Admin;

use Illuminate\Http\Response;
use Modules\User\Entities\User;
use Modules\Admin\Traits\HasCrudActions;
use Modules\User\Http\Requests\SaveUserRequest;
use Cartalyst\Sentinel\Laravel\Facades\Activation;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use DataTables;
use DB;
use Modules\User\Entities\UserService;
use Modules\Service\Entities\Service;
use Modules\Address\Entities\Address;
use Modules\Account\Entities\DefaultAddress;

class UserController
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
    protected $viewPath = 'user::admin.users';

    /**
     * Form requests for the resource.
     *
     * @var array|string
     */
    protected $validation = SaveUserRequest::class;


    /**
     * Store a newly created resource in storage.
     *
     * @param SaveUserRequest $request
     *
     * @return Response
     */
    public function table(Request $request)
    {
        if ($request->ajax()) {
            $data = DB::table('users')
            ->leftJoin('user_roles', 'users.id', '=', 'user_roles.user_id')
            ->where('role_id',1)
            ->get();
            return Datatables::of($data)->addIndexColumn()
            ->addColumn('checkbox', function ($entity) {
                return view('admin::partials.table.checkbox', compact('entity'));
            })
            ->editColumn('last_login', function ($entity) {
                $last_login = $entity->last_login;
                if (is_string($last_login)){
                    $last_login = Carbon::parse($last_login);
                }
                return view('admin::partials.table.date')->with('date', $last_login);
            })
            ->editColumn('created', function ($entity) {
                $created_at = $entity->created_at;
                if (is_string($created_at)){
                    $created_at = Carbon::parse($created_at);
                }
                return view('admin::partials.table.date')->with('date', $created_at);
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

        return view("user::admin.users.index");
    }
    public function store(SaveUserRequest $request)
    {
        $request->merge(['password' => bcrypt($request->password)]);

        $user = User::create($request->all());

        $user->roles()->attach($request->roles);

        Activation::complete($user, Activation::create($user)->code);

        if ($request->has('address_1')) {
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

            DefaultAddress::create([
                'customer_id' => $user->id,
                'address_id' => $address->id,
            ]);
        }

        return redirect()->route('admin.users.index')
            ->withSuccess(trans('admin::messages.resource_created', ['resource' => trans('user::users.user')]));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     * @param SaveUserRequest $request
     *
     * @return Response
     */
    public function update($id, SaveUserRequest $request)
    {
        $user = User::findOrFail($id);

        if (is_null($request->password)) {
            unset($request['password']);
        } else {
            $request->merge(['password' => bcrypt($request->password)]);
        }

        $user->update($request->all());

        $user->roles()->sync($request->roles);

        if (!Activation::completed($user) && $request->activated === '1') {
            Activation::complete($user, Activation::create($user)->code);
        }

        if (Activation::completed($user) && $request->activated === '0') {
            Activation::remove($user);
        }

        if ($request->has('address_1')) {
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
        }

        return redirect()->route('admin.users.index')
            ->withSuccess(trans('admin::messages.resource_updated', ['resource' => trans('user::users.user')]));
    }
    
    /**
     * Toggle service status for a user.
     *
     * @param Request $request
     * @param int $userId
     * @param int $serviceId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleService(Request $request, $userId, $serviceId)
    {
        try {
            $user = User::findOrFail($userId);
            $service = Service::findOrFail($serviceId);

            $isEnabled = $request->input('is_enabled', true);
            
            if (is_string($isEnabled)) {
                $isEnabled = filter_var($isEnabled, FILTER_VALIDATE_BOOLEAN);
            }

            $userService = UserService::where('user_id', $userId)
                ->where('service_id', $serviceId)
                ->first();

            if ($userService) {
                $userService->is_enabled = $isEnabled;
                $userService->save();
            } else {
                UserService::create([
                    'user_id' => $userId,
                    'service_id' => $serviceId,
                    'is_enabled' => $isEnabled,
                ]);
            }

            return response()->json([
                'success' => true,
                'is_enabled' => $isEnabled,
                'message' => $isEnabled ? 'Service enabled successfully' : 'Service disabled successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error toggling service: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search users for autocomplete selectize.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $query = $request->get('query');

        if (empty($query)) {
            return response()->json([]);
        }

        $users = User::select('id', 'first_name', 'last_name', 'fullname', 'email')
            ->where(function ($q) use ($query) {
                $q->where('fullname', 'like', "%{$query}%")
                  ->orWhere('first_name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->limit(20)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->fullname . ' (' . $user->email . ')',
                ];
            });

        return response()->json($users);
    }

    public function getDistricts(Request $request)
    {
        $state = $request->get('state');
        if (!$state) {
            return response()->json([]);
        }
        $districts = DB::table('location_master')
            ->where('state_name', 'LIKE', '%' . $state . '%')
            ->select('district_name')
            ->distinct()
            ->orderBy('district_name')
            ->pluck('district_name');

        return response()->json($districts);
    }

    public function getTalukas(Request $request)
    {
        $district = $request->get('district');
        if (!$district) {
            return response()->json([]);
        }
        $talukas = DB::table('location_master')
            ->where(DB::raw('REPLACE(LOWER(district_name), " ", "")'), 'like', '%' . str_replace(' ', '', strtolower($district)) . '%')
            ->select('subdistrict_name')
            ->distinct()
            ->orderBy('subdistrict_name')
            ->pluck('subdistrict_name');

        return response()->json($talukas);
    }

    public function getVillages(Request $request)
    {
        $taluka = $request->get('taluka');
        if (!$taluka) {
            return response()->json([]);
        }
        $villages = DB::table('location_master')
            ->where('subdistrict_name', $taluka)
            ->select('village_name', 'village_code')
            ->distinct()
            ->orderBy('village_name')
            ->get()
            ->map(function($item) {
                return [
                    'code' => $item->village_code,
                    'name' => $item->village_name
                ];
            });

        return response()->json($villages);
    }

    public function getPincode(Request $request)
    {
        $state = $request->input('state');
        $district = $request->input('district');
        $taluka = $request->input('taluka');
        $villageCode = $request->input('village_code');

        if (!$state || !$district) {
            return response()->json(['success' => false, 'message' => 'State and district are required'], 400);
        }

        try {
            $result = null;

            if ($villageCode) {
                $villageData = DB::table('location_master')
                    ->where('village_code', $villageCode)
                    ->where('district_name', $district)
                    ->where('state_name', $state)
                    ->first(['pincode']);

                if ($villageData) {
                    return response()->json([
                        'success' => true,
                        'pincode' => $villageData->pincode
                    ]);
                }
            }

            $csvPath = base_path('public/storage/all-state-pincode.csv');
            if (File::exists($csvPath)) {
                $handle = fopen($csvPath, 'r');
                fgetcsv($handle); // Skip header

                while (($data = fgetcsv($handle)) !== false) {
                    $dataState = strtolower(trim($data[3]));
                    $dataDistrict = strtolower(trim($data[2]));
                    $dataOfficeName = trim(preg_replace('/ (BO|SO|HO|GPO|B.O|S.O|H.O|G.P.O |G.P.O.|B.O.|S.O.|H.O.)$/i', '', trim($data[0])));

                    if (strcasecmp($dataState, strtolower($state)) === 0 && 
                        strcasecmp($dataDistrict, strtolower($district)) === 0) {
                        
                        if ($taluka && (stripos($dataOfficeName, $taluka) !== false || similar_text($dataOfficeName, $taluka, $percent) && $percent >= 90)) {
                            $result = trim($data[1]);
                            break;
                        } elseif (!$taluka && (strcasecmp($dataOfficeName, strtolower($district)) === 0 || similar_text($dataOfficeName, $district, $percent) && $percent >= 90)) {
                            $result = trim($data[1]);
                            break;
                        }
                    }
                }
                fclose($handle);
            }

            if ($result !== null) {
                return response()->json([
                    'success' => true,
                    'pincode' => $result
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Pincode not found'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}