<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Http\Response;
use Modules\User\Entities\Role;
use Modules\User\Entities\User;
use Modules\User\Entities\UserDevice;
use Modules\User\Entities\ProfilePictureHistory;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Mail;
use Modules\User\Mail\ResetPasswordEmail;
use Modules\User\Mail\EmailOtpSend;
use Modules\User\Contracts\Authentication;
use Modules\User\Events\CustomerRegistered;
use Modules\User\Entities\SubscriptionHistory;
use Modules\User\Entities\OtpVerification;
use Modules\MembershipFee\Entities\MembershipFee;
use Modules\User\Http\Requests\LoginRequest;
use Modules\User\Http\Requests\RegisterRequest;
use Modules\User\Http\Requests\PasswordResetRequest;
use Modules\User\Http\Requests\ResetCompleteRequest;
use Cartalyst\Sentinel\Checkpoints\ThrottlingException;
use Cartalyst\Sentinel\Checkpoints\NotActivatedException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Cartalyst\Sentinel\Laravel\Facades\Activation;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Carbon;
use Modules\Address\Entities\Address;
use Modules\Account\Entities\DefaultAddress;
use Modules\User\Entities\ReferralUsage;
use Illuminate\Support\Facades\Cache;
use Modules\Media\Entities\File;
use DB;
use Modules\ProgramNotification\Entities\ProgramShareMessage;

abstract class BaseAuthController extends Controller
{
    /**
     * The Authentication instance.
     *
     * @var Authentication
     */
    protected $auth;


    /**
     * @param Authentication $auth
     */
    public function __construct(Authentication $auth)
    {
        $this->auth = $auth;

        $this->middleware('guest')->except('getLogout');
    }


    /**
     * Show login form.
     *
     * @return Response
     */
    abstract public function getLogin();


    /**
     * Show reset password form.
     *
     * @return Response
     */
    abstract public function getReset();


    /**
     * Login a user.
     *
     * @param LoginRequest $request
     *
     * @return Response
     */
    public function postLogin(LoginRequest $request)
    {
        try {
            
            $loggedIn = $this->auth->login([
                'email' => $request->email,
                'password' => $request->password,
            ], (bool)$request->get('remember_me', false));
            
            if (!$loggedIn) {
                return back()->withInput()
                    ->withError(trans('user::messages.users.invalid_credentials'));
            }            
            return redirect()->intended($this->redirectTo());
        } catch (NotActivatedException $e) {
            return back()->withInput()
                ->withError(trans('user::messages.users.account_not_activated'));
        } catch (ThrottlingException $e) {
            return back()->withInput()
                ->withError(trans('user::messages.users.account_is_blocked', ['delay' => $e->getDelay()]));
        }
    }


    /**
     * Logout current user.
     *
     * @return void
     */
    public function getLogout()
    {
        $this->auth->logout();

        return redirect($this->loginUrl());
    }


    /**
     * Register a user.
     *
     * @param RegisterRequest $request
     *
     * @return Response
     */
    public function postRegister(RegisterRequest $request)
    {
        $data = array_merge([
            'registration_type' => $request->get('registration_type', 'web'),
        ], $request->only([
            'first_name',
            'last_name',
            'email',
            'phone',
            'password',
        ]));

        $user = $this->auth->registerAndActivate($data);

        $this->assignCustomerRole($user);

        event(new CustomerRegistered($user));

        return redirect($this->loginUrl())
            ->withSuccess(trans('user::messages.users.account_created'));
    }


    /**
     * Start the reset password process.
     *
     * @param PasswordResetRequest $request
     *
     * @return Response
     */
    public function postReset(PasswordResetRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (is_null($user)) {
            return back()->withInput()
                ->withError(trans('user::messages.users.no_user_found'));
        }

        $code = $this->auth->createReminderCode($user);

        Mail::to($user)
            ->send(new ResetPasswordEmail($user, $this->resetCompleteRoute($user, $code)));

        return back()->withSuccess(trans('user::messages.users.check_email_to_reset_password'));
    }


    /**
     * Show reset password complete form.
     *
     * @param string $email
     * @param string $code
     *
     * @return Response
     */
    public function getResetComplete($email, $code)
    {
        $user = User::where('email', $email)->firstOrFail();

        if ($this->invalidResetCode($user, $code)) {
            return redirect()->route('reset')
                ->withError(trans('user::messages.users.invalid_reset_code'));
        }

        return $this->resetCompleteView()->with(compact('user', 'code'));
    }


    /**
     * Complete the reset password process.
     *
     * @param string $email
     * @param string $code
     * @param ResetCompleteRequest $request
     *
     * @return Response
     */
    public function postResetComplete($email, $code, ResetCompleteRequest $request)
    {
        $user = User::where('email', $email)->firstOrFail();

        $completed = $this->auth->completeResetPassword($user, $code, $request->new_password);

        if (!$completed) {
            return back()->withInput()
                ->withError(trans('user::messages.users.invalid_reset_code'));
        }

        return redirect($this->loginUrl())
            ->withSuccess(trans('user::messages.users.password_has_been_reset'));
    }


    /**
     * Where to redirect users after login.
     *
     * @return string
     */
    abstract protected function redirectTo();


    /**
     * The login route.
     *
     * @return string
     */
    abstract protected function loginUrl();


    protected function assignCustomerRole($user)
    {
        $role = Role::findOrNew(setting('customer_role'));

        if ($role->exists) {
            $this->auth->assignRole($user, $role);
        }
    }


    /**
     * Reset complete form route.
     *
     * @param User $user
     * @param string $code
     *
     * @return string
     */
    abstract protected function resetCompleteRoute($user, $code);


    /**
     * Password reset complete view.
     *
     * @return string
     */
    abstract protected function resetCompleteView();


    /**
     * Determine the given reset code is invalid.
     *
     * @param User $user
     * @param string $code
     *
     * @return bool
     */
    private function invalidResetCode($user, $code)
    {
        return $user->reminders()->where('code', $code)->doesntExist();
    }
    
    public function apiGenerateOtp(Request $request)
    {
        $existingUser = $user = $user_type = '';
        $requestData = $request->all();
        if ($requestData) {            
            $requestData['registration_type'] = $requestData['registration_type'] ?? 'app';
            $existingUser = User::where('email', $requestData['email'])->first();
            if($requestData['type'] == 'email'){
                if ($existingUser) {
                    OtpVerification::where('email', $existingUser->email)->delete();
                    $user = $existingUser;
                } else {
                    $user = User::create($requestData);
                    $user->roles()->attach(2);
                    Activation::complete($user, Activation::create($user)->code);
                }
                $requestData['user_id'] = $user->id;
                $message = '';
                if($requestData['email'] == 'sanghotester@gmail.com'){
                    $requestData['code'] = '123456';
                    $message = "Please enter OTP 123456 for testing purpose";
                } else{
                    $requestData['code'] = sprintf('%06d', rand(0, 999999));
                    $message = "OTP has been sent to your email.";
                }
                $otpVerification = OtpVerification::create($requestData);
                Mail::to($user->email)->send(new EmailOtpSend($otpVerification->code));
                return response()->json([
                    "success" => true,
                    "message" => $message,
                ]);

            } elseif ($requestData['type'] == 'google' || $requestData['type'] == 'facebook') {
                if (!$existingUser) {
                    $user = User::create($requestData);             
                    $user->roles()->attach(2);
                    Activation::complete($user, Activation::create($user)->code);
                    $user_type = "new";
                }else{
                    $arrayUser = $existingUser->toArray();
                    if ($existingUser && $arrayUser['fullname'] != '' && ProfilePictureHistory::where('user_id', $existingUser['id'])->exists() == true) {
                        $user = $existingUser;
                        $user_type = "existing";
                    } else{
                        $user = $existingUser;
                        $user_type = "new";
                    }
                }
                return response()->json([
                    "success" => true,
                    "message" => "User Authentication successfully",
                    "data" => [
                        "user_id" => $user->id,
                        "user_type" => $user_type,
                    ]
                ]);
            } else{
                return response()->json([
                    "success" => false,
                    "message" => 'Invalide credentials',
                ]);
            }
        }

        return response()->json([
            "success" => false,
            "message" => 'Invalide credentials',
        ]);
    }
    
    public function apiVerifyEmailOtp(Request $request)
    {
        $existingUser = $user = $user_type = '';
        $requestData = $request->all();

        if ($requestData) {                      
            $existingUser = User::where('email', $requestData['email'])->first()->toArray();
            
            $otpVerification = OtpVerification::where('email', $requestData['email'])
                ->where('code', $requestData['code'])
                ->first();
            if (!empty($existingUser['fullname']) && ProfilePictureHistory::where('user_id', $existingUser['id'])->exists() == true) {
                $user_type = "existing";
            } else{
                $user_type = "new";
            }

            if ($otpVerification) {
                return response()->json([
                    "success" => true,
                    "message" => "OTP verification successful.",
                    "data" => [
                        "user_id" => $otpVerification->user_id,
                        "user_type" => $user_type,
                    ]
                ]);
            }
        }

        return response()->json([
            "success" => false,
            "message" => "Invalid OTP or email.",
        ]);
    }

    public function apiRegister(Request $request)
    {    
        $existingUser = $user = $user_type = '';
        $requestData = $request->all();
        if ($requestData) {
            $requestData['registration_type'] = $requestData['registration_type'] ?? 'app';
            $existingUser = User::where('phone', $requestData['phone'])->first();
            if ($existingUser) {
                $user_type = "new";
                $arrUser = $existingUser->toArray();
                if ($arrUser['fullname'] != '' && ProfilePictureHistory::where('user_id', $arrUser['id'])->exists() == true) {
                    $user_type = "existing";
                    $user = $existingUser;
                }
                $user = $existingUser;
            } else {
                $user_type = "new";
                $user = User::create($requestData);
                $user->roles()->attach(2);
                Activation::complete($user, Activation::create($user)->code);
            }
            return response()->json([
                "success" => true,                
                "data" => [
                    "user_id" => $user->id,
                    "user_type" => $user_type,
                ]
            ]);
        }
    
        return response()->json([
            "success" => false,
            "message" => 'Invalide credentials',
        ]);
    }

    public function apiSaveUserDetails(Request $request)
    {
        $requestData = $request->all();
        
        if (isset($requestData['id'])) {
            if (isset($requestData['facebook']) && $requestData['facebook'] == "null") {
                $requestData['facebook'] = '';
            }
            if (isset($requestData['instagram']) && $requestData['instagram'] == "null") {
                $requestData['instagram'] = '';
            }
            if (isset($requestData['twitter']) && $requestData['twitter'] == "null") {
                $requestData['twitter'] = '';
            }
            if (isset($requestData['linkedin']) && $requestData['linkedin'] == "null") {
                $requestData['linkedin'] = '';
            }
            if (isset($requestData['gender']) && $requestData['gender'] == "null") {
                $requestData['gender'] = 'male';
            }
            if (isset($requestData['personal_details']) && $requestData['personal_details'] == "null") {
                $requestData['personal_details'] = '';
            }
            $user = User::find($requestData['id']);
            
            if ($user) {
                if (!isset($requestData['phone']) || empty($requestData['phone']) || $requestData['phone'] == "null") {
                    $requestData['phone'] = $user->phone;
                }                
                if ($user->referral_code == '') {
                    do {
                        $referralCode = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'), 0, 5));
                    } while (User::where('referral_code', $referralCode)->exists());
                    
                    $requestData['referral_code'] = $referralCode;
                }
                $user->update($requestData);
                
                if (isset($requestData['requestType']) && $requestData['requestType'] == 'address' && ( isset($requestData['address_1']) || isset($requestData['address']) || isset($requestData['pincode']) || isset($requestData['city']) || isset($requestData['state']))) {
                    $defaultAddress = DefaultAddress::where('customer_id', $user->id)->first();            
                    $arrayUser = $user ? $user->toArray() : [];
                    $firstName = $requestData['first_name'] ?? ($arrayUser['first_name'] ?? ($arrayUser['fullname'] ?? 'Customer'));
                    $lastName = $requestData['last_name'] ?? ($arrayUser['last_name'] ?? '');
                    $addressLine1 = $requestData['address_1'] ?? ($requestData['address'] ?? '');
                    $zipCode = $requestData['pincode'] ?? ($requestData['zip'] ?? '');

                    if (!$defaultAddress) {
                        $address = Address::create([
                            'customer_id' => $user->id,
                            'first_name' => $firstName,
                            'last_name' => $lastName,
                            'phone' => $requestData['phone'] ?? ($arrayUser['phone'] ?? ''),
                            'address_1' => $addressLine1,
                            'address_2' => $requestData['address_2'] ?? '',
                            'area_type' => $requestData['area_type'] ?? '',
                            'taluka' => $requestData['taluka'] ?? '',
                            'village' => $requestData['village'] ?? '',
                            'zip' => $zipCode,
                            'city' => $requestData['city'] ?? '',
                            'state' => $requestData['state'] ?? '',
                            'country' => 'IN',
                        ]);
                        DefaultAddress::create([
                            'customer_id' => $user->id,
                            'address_id' => $address->id,
                        ]);
                    } else {
                        $address = Address::where('id', $defaultAddress->address_id)->first();
                        if ($address) {
                            $address->update([
                                'first_name' => $firstName,
                                'last_name' => $lastName,
                                'phone' => $requestData['phone'] ?? $address->phone,
                                'address_1' => $addressLine1 ?: $address->address_1,
                                'address_2' => $requestData['address_2'] ?? $address->address_2,
                                'area_type' => $requestData['area_type'] ?? $address->area_type,
                                'taluka' => $requestData['taluka'] ?? $address->taluka,
                                'village' => $requestData['village'] ?? $address->village,
                                'zip' => $zipCode ?: $address->zip,
                                'city' => $requestData['city'] ?? $address->city,
                                'state' => $requestData['state'] ?? $address->state,
                            ]);
                        }
                    }
                }

                if(isset($requestData['referrer_code']) && $requestData['referrer_code']){
                    $referrerUser = User::where('referral_code', $requestData['referrer_code'])->first();
                    
                    if ($referrerUser) {
                        ReferralUsage::create([
                            'referral_code' => $requestData['referrer_code'],
                            'referrer_id' => $referrerUser->id,
                            'referred_user_id' => $user->id,
                            'reward_point' => 100,
                            'referral_share_type' => $requestData['share_source'] ?? 'web',
                        ]);
                    }
                }

                return response()->json([
                    "success" => true,
                    "data" => [
                        "user_id" => $user->id,
                    ]
                ]);
            } else {
                return response()->json([
                    "success" => false,
                    "message" => 'User ID not found',
                ], 404);
            }
        }

        return response()->json([
            "success" => false,
            "message" => 'Invalid credentials',
        ], 400);
    }

    public function apiUserDetails($id)
    {
        $user = User::with('defaultAddress')->find($id);
        if ($user->defaultAddress && $user->defaultAddress->address) {
            $user->address = [
                'address_1' => $user->defaultAddress->address->address_1,
                'address_2' => $user->defaultAddress->address->address_2,
                'area_type' => $user->defaultAddress->address->area_type ?? '',
                'taluka' => $user->defaultAddress->address->taluka ?? '',
                'village' => $user->defaultAddress->address->village ?? '',
                'zip' => $user->defaultAddress->address->zip,
                'city' => $user->defaultAddress->address->city,
                'state' => $user->defaultAddress->address->state
            ];
        } else {
            $user->address = null;
        }
        unset($user->defaultAddress);
                
        if ($user) {
            $profilePictureUrl = '';
            $profilePicture = ProfilePictureHistory::where(['user_id' => $user->id, 'active_profile' => 1])->first();
            if ($profilePicture) {
                $profilePictureUrl = Storage::url('profile_pictures/' . $profilePicture->image_url);            
            }
            return response()->json([
                "success" => true,
                "data" => [
                    "user" => $user,
                    "profilePicture" => $profilePictureUrl
                ]
            ]);
        } else {
            return response()->json([
                "success" => false,
                "message" => 'User not found',
            ], 404);
        }
    }

    public function apiSaveUserProfiles(Request $request)
    {
        $user = User::find($request['user_id']);

        if (!$user) {
            return response()->json([
                "success" => false,
                "message" => 'User ID not found',
            ], 404);
        }

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
    
            return response()->json([
                "success" => true,
                "data" => [
                    "user_id" => $user->id,
                    "image_id" => $profile->id,
                    "image_url" => Storage::url('profile_pictures/' . $filename),
                ]
            ]);
        }

        return response()->json([
            "success" => false,
            "message" => 'No image provided',
        ], 400);
    }

    public function apiDeleteUserProfiles(Request $request)
    {
        $profilePicture = ProfilePictureHistory::find($request->input('id'));

        if (!$profilePicture) {
            return response()->json([
                "success" => false,
                "message" => 'Profile picture not found',
            ], 404);
        }

        $imagePath = public_path('storage/profile_pictures/' . $profilePicture->image_url);
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }

        $profilePicture->delete();

        return response()->json([
            "success" => true,
            "message" => 'Profile picture deleted successfully',
        ]);
    }
    
    public function apiUserSubscribe(Request $request)
    {
        $requestData = $request->all();
        
        $user = User::find($requestData['user_id']);
        if (isset($requestData['user_id'])) {
            $user->roles()->update(['role_id' => 5]);
            if ($user->is_subscribe == 0) {

                if ($user) {
                    /* $requestData['start_date'] = Carbon::now()->toDateTimeString();
                    $requestData['end_date'] = Carbon::now()->addMonth()->toDateTimeString();
                    
                    $subscriptionHistory = SubscriptionHistory::create($requestData);
                    $user->where('id', $user->id)->update(['is_subscribe' => 1, 'subscription_history_id' => $subscriptionHistory->id]);
                    
                    return response()->json([
                        "success" => true,
                        "data" => [
                            "user_id" => $user->id,
                            "start_date" => $requestData['start_date'],
                            "end_date" => $requestData['end_date']
                        ]
                    ]); */
                    $membershipFee = MembershipFee::find($requestData['subscription_fees_id']);
                    
                    if ($membershipFee) {
                        $requestData['subscription_fees_name'] = $membershipFee->name;
                        $requestData['subscription_fees_duration'] = $membershipFee->duration;
                        $requestData['subscription_fees'] = $membershipFee->membershipfee;
                        $requestData['start_date'] = Carbon::now()->toDateTimeString();
                        $requestData['end_date'] = Carbon::now()->addMonth()->toDateTimeString();
                        
                        $subscriptionHistory = SubscriptionHistory::create($requestData);
                        $user->where('id', $user->id)->update(['is_subscribe' => 1, 'subscription_history_id' => $subscriptionHistory->id]);
                        
                        return response()->json([
                            "success" => true,
                            "data" => [
                                "user_id" => $user->id,
                                "start_date" => $requestData['start_date'],
                                "end_date" => $requestData['end_date']
                            ]
                        ]);
                    }
                } else {
                    return response()->json([
                        "success" => false,
                        "message" => 'User ID not found',
                    ], 404);
                }
            } else{
                return response()->json([
                    "success" => true,
                    "message" => 'User already subscribed',
                ]);
            }
             
        }

        return response()->json([
            "success" => false,
            "message" => 'Invalid credentials',
        ], 400);
    }
    
    public function apiActiveSubscription($user_id){
        if ($user_id) {
            $user = User::find($user_id);
            $subscriptionHistory = SubscriptionHistory::find($user->subscription_history_id);
            return response()->json([
                "success" => true,
                "data" => [
                    "subscription_history" => $subscriptionHistory,
                ]
            ]);
        }
        return response()->json([
            "success" => false,
            "message" => 'Invalid credentials',
        ], 400);
    }
    
    public function apiUserSaveToken(Request $request)
    {
        $requestData = $request->all();

        if ($requestData) {
            $user = User::find($requestData['userId']);
            if ($user) {
                $user->where('id', $user->id)->update(['expo_notification_token' => $requestData['token']]);                
                return response()->json([
                    "success" => true,
                    "message" => 'Token saved successfully',
                    "data" => [
                        "user_token" => $user->expo_notification_token,
                    ]
                ], 200);
            } else {
                return response()->json([
                    "success" => false,
                    "message" => 'User ID not found',
                ], 404);
            }             
        }

        return response()->json([
            "success" => false,
            "message" => 'Invalid credentials',
        ], 400);
    }
    
    public function apiUserStoreFcmToken(Request $request)
    {
        $requestData = $request->all();

        if ($requestData) {
            $user = User::find($requestData['userId']);
            if ($user) {
                $user->where('id', $user->id)->update(['fcm_token' => $requestData['token']]);                
                return response()->json([
                    "success" => true,
                    "message" => 'Token saved successfully',
                    "data" => [
                        "user_token" => $user->fcm_token,
                    ]
                ], 200);
            } else {
                return response()->json([
                    "success" => false,
                    "message" => 'User ID not found',
                ], 404);
            }             
        }

        return response()->json([
            "success" => false,
            "message" => 'Invalid credentials',
        ], 400);
    }

    public function apiUserSaveDeviceInfo(Request $request)
    {
        $requestData = $request->all();

        if ($requestData) {
            $userId = $requestData['user_id'] ?? $requestData['userId'] ?? null;
            if (!$userId) {
                return response()->json([
                    "success" => false,
                    "message" => 'User ID is required',
                ], 400);
            }

            $user = User::find($userId);
            if ($user) {
                $uniqueDeviceId = $requestData['unique_device_id'] ?? $requestData['uniqueDeviceId'] ?? null;
                
                $device = UserDevice::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'unique_device_id' => $uniqueDeviceId,
                    ],
                    [
                        'device_name' => $requestData['device_name'] ?? $requestData['deviceName'] ?? null,
                        'device_model' => $requestData['device_model'] ?? $requestData['deviceModel'] ?? null,
                        'device_brand' => $requestData['device_brand'] ?? $requestData['deviceBrand'] ?? null,
                        'device_manufacturer' => $requestData['device_manufacturer'] ?? $requestData['deviceManufacturer'] ?? null,
                        'os_name' => $requestData['os_name'] ?? $requestData['osName'] ?? null,
                        'os_version' => $requestData['os_version'] ?? $requestData['osVersion'] ?? null,
                        'app_version' => $requestData['app_version'] ?? $requestData['appVersion'] ?? null,
                        'app_build' => $requestData['app_build'] ?? $requestData['appBuild'] ?? null,
                        'network_ip' => $requestData['network_ip'] ?? $requestData['networkIp'] ?? null,
                        'network_state' => $requestData['network_state'] ?? $requestData['networkState'] ?? null,
                    ]
                );

                return response()->json([
                    "success" => true,
                    "message" => 'Device information saved successfully',
                    "data" => [
                        "device" => $device,
                    ]
                ], 200);
            } else {
                return response()->json([
                    "success" => false,
                    "message" => 'User not found',
                ], 404);
            }
        }

        return response()->json([
            "success" => false,
            "message" => 'Invalid data',
        ], 400);
    }
    
    /**
     * Redirect to Play Store with referral code.
     *
     * @param string $referralCode
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function showReferralPage($referralCode, $shareSource)
    {
        
        $utmSource = 'app_share';
        $utmMedium = 'user_referral';
        $utmCampaign = $shareSource.'_'.'screen';
    
        
        $referrerParams = http_build_query([
            'utm_source' => $utmSource,
            'utm_medium' => $utmMedium,
            'utm_campaign' => $utmCampaign,
            'referral' => $referralCode,
            'share_source' => $shareSource
        ]);
        
        $applicationId = 'sangho.app';
        
        $deepLinkUrl = "sangho://app/referral/{$referralCode}/share_source/{$shareSource}";
        
        $playStoreUrl = "https://play.google.com/store/apps/details?id={$applicationId}&referrer=" . urlencode($referrerParams);
        
        $logo = $this->getMedia(setting('storefront_header_logo'));

        $webUrl = env('WEB_APP_URL', 'https://sangho-app-next.vercel.app');
        $webRedirectUrl = rtrim($webUrl, '/') . "/referral/{$referralCode}?share_source={$shareSource}";

        return view('user::referral', [
            'deepLinkUrl' => $deepLinkUrl,
            'playStoreUrl' => $playStoreUrl,
            'logo' => $logo,
            'webRedirectUrl' => $webRedirectUrl,
            'shareSource' => $shareSource
        ]);
    }
    
    private function getMedia($fileId)
    {
        return Cache::rememberForever(md5("files.{$fileId}"), function () use ($fileId) {
            return File::findOrNew($fileId);
        });
    }
    
    /**
     * Get total referrals by referrer_id
     * 
     * @param int $referrerId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTotalReferralsByReferrerId($referrerId)
    {
        try {
            if (!$referrerId || !is_numeric($referrerId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid referrer ID'
                ], 400);
            }

            $referrer = User::find($referrerId);
            if (!$referrer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Referrer not found'
                ], 404);
            }

            $totalReferrals = ReferralUsage::where('referrer_id', $referrerId)->count();
            
            $totalRewardPoints = ReferralUsage::where('referrer_id', $referrerId)
                ->sum('reward_point');

            $referralHistory = ReferralUsage::where('referrer_id', $referrerId)
                ->with('referredUser')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($referral) {
                    return [
                        'id' => $referral->id,
                        'referral_code' => $referral->referral_code,
                        'reward_point' => $referral->reward_point,
                        'referral_share_type' => $referral->referral_share_type,
                        'referred_user' => $referral->referredUser ? [
                            'id' => $referral->referredUser->id,
                            'name' => $referral->referredUser->first_name . ' ' . $referral->referredUser->last_name,
                            'email' => $referral->referredUser->email,
                            'phone' => $referral->referredUser->phone
                        ] : null,
                        'created_at' => $referral->created_at->format('Y-m-d H:i:s')
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'referrer_id' => $referrerId,
                    'total_referrals' => $totalReferrals,
                    'total_reward_points' => $totalRewardPoints,
                    'referral_history' => $referralHistory
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function apiAppShareMessages(Request $request)
    {   
        $programShareMessage = setting('program_share_message');
        if ($request->program_id) {
            $programMessage = ProgramShareMessage::where('program_notification_id', $request->program_id)
                ->with(['user'])
                ->latest()
                ->first();
            if ($programMessage) {
                $programShareMessage = $programMessage->message;
            }
            else {
                $programShareMessage = setting('program_share_message');
            }
        }
        return response()->json([
            'success' => true,
            'data' => [
                'messages' => [
                    'app_share_message' => setting('app_share_message'),
                    'quote_share_message' => setting('quote_share_message'),
                    'community_share_message' => setting('community_share_message'),
                    'community_post_share_message' => setting('community_post_share_message'),
                    'community_card_share_message' => setting('community_card_share_message'),
                    'program_share_message' => $programShareMessage,
                    'program_qr_share_message' => setting('program_qr_share_message'),
                    'program_registration_post_message' => setting('program_registration_post_message'),
                    'business_share_message' => setting('business_share_message')
                ]
            ]
        ]);
    }

    public function apiAddMobileNumber(Request $request)
    {
        $requestData = $request->all();
        if ($requestData) {
            $user = User::find($requestData['user_id']);
            if ($user) {
                $user->update(['phone' => $requestData['phone']]);
                $address = Address::where('customer_id', $user->id)->first();
                $address->update(['phone' => $requestData['phone']]);
                return response()->json([
                    "success" => true,
                ], 200);
            } else {
                return response()->json([
                    "success" => false,
                    "message" => 'User ID not found',
                ], 404);
            }             
        }

        return response()->json([
            "success" => false,
            "message" => 'Invalid credentials',
        ], 400);
    }

    public function getRefferedUsersList($referrerId)
    {
        $referrals = ReferralUsage::where('referrer_id', $referrerId)->with(['referredUser:id,fullname,email,dob,phone', 'referredUser.defaultAddress'])->latest()->get();
        if ($referrals->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No referrals found'
            ], 404);
        }

        foreach ($referrals as $referral) {
            $profilePicture = ProfilePictureHistory::where(['user_id' => $referral->referred_user_id, 'active_profile' => 1])->first();
            if ($profilePicture) {
                $profilePictureUrl = Storage::url('profile_pictures/' . $profilePicture->image_url);
            }
            $referral->referredUser->profile_picture = $profilePictureUrl ?? '';            
        }

        return response()->json([
            'success' => true,
            'data' => $referrals
        ]);

    }

    public function apiGetUserReferralInfo(Request $request)
    {
        try {            
            $userId = $request->user_id;
 
            if (!$userId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User ID are required'
                ], 400);
            }
 
            $referralInfo = ReferralUsage::where([                
                'referred_user_id' => $userId
            ])
            ->with(['referrer'])
            ->first();
 
            $joinDate = User::where('id', $userId)->first();
 
            if (!$referralInfo && !$joinDate) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No referral information found for this user'
                ], 404);
            }
            
            if (!$referralInfo && $joinDate) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'User Join information retrieved successfully',
                    'data' => [
                        'joined_at' => $joinDate->created_at
                    ]
                ], 200);
            }
            
            $profilePicture = $referralInfo->referrer->activeProfile($referralInfo->referrer->id);
            if ($profilePicture) {
                $referralInfo->referrer->user_profile = asset('storage/profile_pictures/' . $profilePicture);
            }
            
            return response()->json([
                'status' => 'success',
                'message' => 'Referral information retrieved successfully',
                'data' => [
                    'referral_code' => $referralInfo->referral_code,
                    'referrer' => $referralInfo->referrer,
                    'referral_share_type' => $referralInfo->referral_share_type,
                    'joined_at' => $referralInfo->created_at
                ]
            ], 200);
 
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve referral information',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get all states
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiGetStates()
    {
        try {
            $states = DB::table('location_master')
                ->select('state_name')
                ->distinct()
                ->orderBy('state_name')
                ->get();

            if ($states->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No states found',
                    'data' => []
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'States fetched successfully',
                'data' => $states
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch states: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }
    
    /**
     * Get districts by state name
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiGetDistrictsByState(Request $request)
    {
        try {
            $stateName = $request->get('state');
            
            if (!$stateName) {
                return response()->json([
                    'success' => false,
                    'message' => 'State name is required',
                    'data' => []
                ], 400);
            }

            $districts = DB::table('location_master')
                ->where('state_name', 'LIKE', '%' . $stateName . '%')
                ->select('district_name')
                ->distinct()
                ->orderBy('district_name')
                ->get();

            if ($districts->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No districts found for the given state',
                    'data' => []
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Districts fetched successfully',
                'data' => $districts
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch districts: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }
    
    /**
     * Get talukas (subdistricts) by district name
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiGetTalukasByDistrict(Request $request)
    {
        $district = $request->input('district');
        
        if (!$district) {
            return response()->json([
                'success' => false,
                'message' => 'District name is required',
                'data' => []
            ], 400);
        }

        try {
            $talukas = DB::table('location_master')
                ->where(DB::raw('REPLACE(LOWER(district_name), " ", "")'), 'like', '%' . str_replace(' ', '', strtolower($district)) . '%')
                ->select('subdistrict_name')
                ->distinct()
                ->orderBy('subdistrict_name')
                ->pluck('subdistrict_name')
                ->toArray();
            
            return response()->json([
                'success' => true,
                'message' => 'Talukas fetched successfully',
                'data' => $talukas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch talukas: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Get villages by taluka (subdistrict) name
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiGetVillagesByTaluka(Request $request)
    {
        $taluka = $request->input('taluka');
        
        if (!$taluka) {
            return response()->json([
                'success' => false,
                'message' => 'Taluka name is required',
                'data' => []
            ], 400);
        }

        try {
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
                })
                ->toArray();
            
            return response()->json([
                'success' => true,
                'message' => 'Villages fetched successfully',
                'data' => $villages
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch villages: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Get pincode information by state, district, and village
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiGetPincodeInfo(Request $request)
    {
        $state = $request->input('state');
        $district = $request->input('district');
        $taluka = $request->input('taluka');
        $villageCode = $request->input('village_code');
        
        if (!$state || !$district) {
            return response()->json([
                'success' => false,
                'message' => 'State and district are required',
                'data' => []
            ], 400);
        }

        try {
            $csvPath = base_path('public/storage/all-state-pincode.csv');
            
            if (!File::exists($csvPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'CSV file not found',
                    'data' => []
                ], 500);
            }

            $result = null;
            $handle = fopen($csvPath, 'r');
            
            // Skip header row
            fgetcsv($handle);
            
            while (($data = fgetcsv($handle)) !== false) {
                $dataState = strtolower(trim($data[3])); // statename
                $dataDistrict = strtolower(trim($data[2])); // district
                $dataOfficeName = trim(preg_replace('/ (BO|SO|HO|GPO|B.O|S.O|H.O|G.P.O |G.P.O.|B.O.|S.O.|H.O.)$/i', '', trim($data[0]))); // officename with removed suffixes

                // First check if state and district match
                if (strcasecmp($dataState, strtolower($state)) === 0 && 
                    strcasecmp($dataDistrict, strtolower($district)) === 0) {
                    
                    // If village is provided, get pincode from database using village_code
                    if ($villageCode) {
                        $villageData = DB::table('location_master')
                            ->where('village_code', $villageCode)
                            ->where('district_name', $district)
                            ->where('state_name', $state)
                            ->first(['village_name', 'village_code', 'pincode', 'district_name', 'state_name']);

                        if ($villageData) {
                            $result = [
                                'officename' => $villageData->village_name,
                                'pincode' => $villageData->pincode,
                                'district' => $villageData->district_name,
                                'statename' => $villageData->state_name
                            ];
                            break;
                        }
                    }
                    // If taluka is provided (but no village), look for matching officename
                    elseif ($taluka && !$villageCode && (
                        stripos($dataOfficeName, $taluka) !== false || 
                        similar_text($dataOfficeName, $taluka, $percent) && $percent >= 90
                    )) {
                        $result = [
                            'officename' => $dataOfficeName,
                            'pincode' => trim($data[1]),
                            'district' => trim($data[2]),
                            'statename' => trim($data[3])
                        ];
                        break;
                    }
                    // If no taluka and no village, look for district name in officename
                    elseif (!$taluka && !$villageCode && (
                        strcasecmp($dataOfficeName, strtolower($district)) === 0 || 
                        similar_text($dataOfficeName, $district, $percent) && $percent >= 90
                    )) {
                        $result = [
                            'officename' => $dataOfficeName,
                            'pincode' => trim($data[1]),
                            'district' => trim($data[2]),
                            'statename' => trim($data[3])
                        ];
                        break;
                    }
                }
            }
            
            fclose($handle);
            
            if ($result === null) {
                $searchTerm = $villageCode ? 'village' : ($taluka ? 'taluka' : 'district');
                $searchValue = $villageCode ? $villageCode : ($taluka ? $taluka : $district);
                return response()->json([
                    'success' => false,
                    'message' => "No pincode found for {$searchTerm} '{$searchValue}' in {$district} district of {$state}",
                    'data' => []
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Pincode information fetched successfully',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch pincode information: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }
    
    public function uploadLocationData(Request $request)
    {
        try {
            if (!$request->hasFile('csv_file')) {
                return response()->json(['error' => 'CSV file is required'], 400);
            }

            $file = $request->file('csv_file');
            $handle = fopen($file->getPathname(), 'r');
            
            // Skip header row
            $header = fgetcsv($handle);
            
            DB::beginTransaction();
            
            while (($row = fgetcsv($handle)) !== false) {
                DB::insert('INSERT INTO location_master (state_code, state_name, district_code, district_name, 
                    subdistrict_code, subdistrict_name, village_code, village_name, pincode, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())', [
                    $row[0], // state_code
                    $row[1], // state_name
                    $row[2], // district_code
                    $row[3], // district_name
                    $row[4], // subdistrict_code
                    $row[5], // subdistrict_name
                    $row[6], // village_code
                    $row[7], // village_name_english
                    $row[8]  // pincode
                ]);
            }
            
            fclose($handle);
            DB::commit();
            
            return response()->json(['message' => 'Location Master Data uploaded successfully']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    public function uploadPincodeLocationData(Request $request)
    {
        try {
            if (!$request->hasFile('csv_file')) {
                return response()->json(['error' => 'CSV file is required'], 400);
            }

            $file = $request->file('csv_file');
            $handle = fopen($file->getPathname(), 'r');
            
            // Skip header row
            $header = fgetcsv($handle);
            
            DB::beginTransaction();
            
            while (($row = fgetcsv($handle)) !== false) {
                DB::table('pincode_office_locations')->insert([
                    'officename' => $row[0],
                    'pincode' => $row[1],
                    'district' => $row[2],
                    'state' => $row[3],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            
            fclose($handle);
            DB::commit();
            
            return response()->json(['message' => 'Data uploaded successfully']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
