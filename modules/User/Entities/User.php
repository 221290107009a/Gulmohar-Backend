<?php

namespace Modules\User\Entities;

use Modules\Order\Entities\Order;
use Modules\User\Admin\UserTable;
use Illuminate\Http\JsonResponse;
use Modules\Review\Entities\Review;
use Illuminate\Auth\Authenticatable;
use Modules\Address\Entities\Address;
use Modules\Product\Entities\Product;
use Modules\User\Repositories\Permission;
use Cartalyst\Sentinel\Users\EloquentUser;
use Modules\Address\Entities\DefaultAddress;
use Illuminate\Database\Eloquent\Collection;
use Cartalyst\Sentinel\Laravel\Facades\Activation;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Carbon\Carbon;
use Modules\User\Entities\UserData;
use Modules\User\Entities\UserPosition;
use Modules\User\Entities\ProfilePictureHistory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Modules\User\Entities\SubscriptionHistory;
use Modules\Service\Entities\Service;
use Modules\Media\Eloquent\HasMedia;
use Modules\Media\Entities\File;
use DB;

class User extends EloquentUser implements JWTSubject, AuthenticatableContract
{
    use Authenticatable, Notifiable, HasApiTokens, HasMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email',
        'phone',
        'password',
        'last_name',
        'first_name',
        'fullname',
        'address',
        'personal_details',
        'facebook',
        'instagram',
        'twitter',
        'linkedin',
        'dob',
        'gender',
        'education',
        'profession',
        'language',
        'permissions',
        'expo_notification_token',
        'fcm_token',
        'referral_code',
        'subscription_history_id',
        "state",
        "city",
        "notification_date",
        "registration_type"
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'permissions' => 'json',
        'last_login' => 'datetime',
    ];


    public static function registered($email)
    {
        return static::where('email', $email)->exists();
    }


    public static function findByEmail($email)
    {
        return static::where('email', $email)->first();
    }


    public static function totalCustomers()
    {
        return DB::table('users')
            ->leftJoin('user_roles', 'users.id', '=', 'user_roles.user_id')
            ->whereNot('role_id', 1)
            ->count();
        /* return Role::findOrNew(setting('customer_role'))->users()->count(); */
    }

    public static function totalActiveCustomers()
    {
        return DB::table('users')
            ->leftJoin('user_roles', 'users.id', '=', 'user_roles.user_id')
            ->where('users.is_subscribe', 1)
            ->whereNot('role_id', 1)
            ->count();
    }

    /**
     * Login the user.
     *
     * @return $this|bool
     */
    public function login()
    {
        return auth()->login($this);
    }


    /**
     * Determine if the user is a customer.
     *
     * @return bool
     */
    public function isCustomer()
    {
        if ($this->hasRoleName('admin')) {
            return false;
        }

        return $this->hasRoleId(setting('customer_role'));
    }


    /**
     * Checks if a user belongs to the given Role Name.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasRoleName($name)
    {
        return $this->roles()->whereTranslation('name', $name)->count() !== 0;
    }


    /**
     * Get the roles of the user.
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')->withTimestamps();
    }


    /**
     * Checks if a user belongs to the given Role ID.
     *
     * @param int $roleId
     *
     * @return bool
     */
    public function hasRoleId($roleId)
    {
        return $this->roles()->whereId($roleId)->count() !== 0;
    }


    /**
     * Check if the current user is activated.
     *
     * @return bool
     */
    public function isActivated()
    {
        return Activation::completed($this);
    }


    /**
     * Get the recent orders of the user.
     *
     * @param int $take
     *
     * @return Collection
     */
    public function recentOrders($take)
    {
        return $this->orders()->latest()->take($take)->get();
    }


    /**
     * Get the orders of the user.
     *
     * @return HasMany
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }


    /**
     * Get the default address of the user.
     *
     * @return HasMany
     */
    public function defaultAddress()
    {
        return $this->hasOne(DefaultAddress::class, 'customer_id')->withDefault();
    }


    /**
     * Get the addresses of the user.
     *
     * @return HasMany
     */
    public function addresses()
    {
        return $this->hasMany(Address::class, 'customer_id');
    }


    /**
     * Get the reviews of the user.
     *
     * @return HasMany
     */
    public function reviews()
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }


    /**
     * Get the full name of the user.
     *
     * @return string
     */
    public function getFullNameAttribute($value)
    {
        return $value ?: trim("{$this->first_name} {$this->last_name}");
    }

    public function getProfileBannerAttribute()
    {
        return $this->filterFiles('profile_banner', app()->getLocale())->first() ?: new File;
    }
    
    /**
     * Get the user's password.
     *
     * @param string|null $value
     * @return string
     */
    public function getPasswordAttribute($value)
    {
        return $value ?? '';
    }

    /**
     * Set user's permissions.
     *
     * @param array $permissions
     *
     * @return void
     */
    public function setPermissionsAttribute(array $permissions)
    {
        $this->attributes['permissions'] = Permission::prepare($permissions);
    }


    /**
     * Determine if the user has access to the given permissions.
     *
     * @param array|string $permissions
     *
     * @return bool
     */
    public function hasAccess($permissions)
    {
        $permissions = is_array($permissions) ? $permissions : func_get_args();

        return $this->getPermissionsInstance()->hasAccess($permissions);
    }


    /**
     * Determine if the user has access to any given permissions
     *
     * @param array|string $permissions
     *
     * @return bool
     */
    public function hasAnyAccess($permissions)
    {
        $permissions = is_array($permissions) ? $permissions : func_get_args();

        return $this->getPermissionsInstance()->hasAnyAccess($permissions);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
    
    public function wishlistHas($productId)
    {
        return self::wishlist()->where('product_id', $productId)->exists();
    }


    /**
     * Get the wishlist of the user.
     *
     * @return BelongsToMany
     */
    public function wishlist()
    {
        return $this->belongsToMany(Product::class, 'wish_lists')->withTimestamps();
    }

    /**
     * Get the services of the user.
     *
     * @return BelongsToMany
     */
    public function services()
    {
        return $this->belongsToMany(Service::class, 'user_services')
            ->withPivot('is_enabled')
            ->withTimestamps();
    }


    /**
     * Get table data for the resource
     *
     * @return JsonResponse
     */
    public function table()
    {
        return new UserTable($this->newQuery());
    }

    public function userData()
    {
        return $this->hasOne(UserData::class);
    }
    public function memberData()/*: BelongsToMany*/
    {
       
        return $this->hasOne(UserData::class,'user_id');
    }
   
    public function activeProfile($user_id)
    {       
        $profile = ProfilePictureHistory::where([
            'user_id' => $user_id, 
            'active_profile' => 1
        ])->value('image_url');
        return $profile;
    }

    public function userPositionData()/*: BelongsToMany*/
    {
        return $this->hasMany(UserPosition::class,'user_id');
    }

    public function saveUserData(array $allData)
    {
        if(!empty($allData['photo']))
        {
            $file = $allData['photo'];
            $extension = $file->getClientOriginalExtension();
            $namewithextension = $file->getClientOriginalName(); 
            $name = explode('.', $namewithextension)[0];
            $filename = time().'.'.$extension;
            $file->move(public_path('uploads/pictures'),$filename);
            $allData['photo'] = $filename;
        }
        
        $userId = $this->id;
        if ($userId) {
            $allData['country'] = "india";
            $allData['state'] = "Gujarat";
            $allData['user_id'] = $userId;
            $allData['join_date'] = Carbon::now()->toDateTimeString();
            UserData::create($allData);
        }
    }
    public function updateUserData(array $allData, $memberId)
    {
        $memberData = [];

        if(!empty($allData['photo']))
        {
            $file = $allData['photo'];
            $extension = $file->getClientOriginalExtension();
            $namewithextension = $file->getClientOriginalName(); 
            $name = explode('.', $namewithextension)[0];
            $filename = $name.'_'.time().'.'.$extension;
            $file->move(public_path('uploads/pictures'),$filename);
            $memberData['photo'] = $filename;
        }
     
        $memberData['dob'] = $allData['dob'];
        $memberData['whatsapp_mobile'] = $allData['whatsapp_mobile'];
        $memberData['address'] = $allData['address'];
        $memberData['city'] = $allData['city'];
        $memberData['education'] = $allData['education'];
        $memberData['category'] = $allData['category'];
        $memberData['religion'] = $allData['religion'];
        $memberData['cast'] = $allData['cast'];
        $memberData['gender'] = $allData['gender'];
        $memberData['country'] = $allData['country'];
        $memberData['state'] = $allData['state'];
        $memberData['join_date'] =  Carbon::now()->toDateTimeString();
    
        UserData::updateOrCreate(['id' => $memberId],$memberData);
    }

    public function storeUserPosition(array $allData)
    {
        if($allData['committee_id'] != '')
        {
            $return  = [];
            $ids= [];
            foreach ($allData['position_id'] as $key => $position) 
            { 
                $UserPosition = UserPosition::firstOrNew([
                        'user_id' => $this->id,
                        'position_id' => $position,
                        'committee_id' => $allData['committee_id']
                    ]);
                $UserPosition->save();
                $ids[] = $UserPosition->id;
            }
            UserPosition::where('user_id', $this->id)
             ->whereNotIn('id', $ids)
             ->delete();
       }
    }

    public function getSelectedPosition($k)
    {
        $selected = false;
        foreach($this->userPositionData()->get() as $key => $position){
            if($position->position_id == $k){
                $selected = true;
            }
        }
        return $selected;
    }
    
    public function subscriptionDetail($user_id)
    {       
        $subscriptionHistory = SubscriptionHistory::where(['user_id' => $user_id, ])->first();
        return $subscriptionHistory;
    }
    
    public function subscriptionHistories($user_id)
    {       
        $subscriptionHistories = SubscriptionHistory::where(['user_id' => $user_id, ])->get();
        return $subscriptionHistories;
    }

    /**
     * Get the matrimony profile associated with the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function matrimonyProfile()
    {
        return $this->hasOne(\Modules\Matrimony\Entities\Matrimony::class, 'user_id');
    }

    /**
     * Get the devices associated with the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function devices()
    {
        return $this->hasMany(UserDevice::class, 'user_id');
    }
}
