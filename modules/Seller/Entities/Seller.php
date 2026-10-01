<?php

namespace Modules\Seller\Entities;

// use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\Factories\HasFactory;

use Modules\Media\Entities\File;
use Modules\Media\Eloquent\HasMedia;
use Illuminate\Support\Facades\Cache;
use Modules\Admin\Ui\AdminTable;
use Modules\Support\Eloquent\Model;
use Modules\Meta\Eloquent\HasMetaData;
use Modules\Support\Eloquent\Translatable;
use Modules\Seller\Admin\SellerTable;


class Seller extends Model
{
    use Translatable, HasMetaData, HasMedia;

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['translations', 'files'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['logo', 'image'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'is_active', 'phone', 'email', 'category', 'gst_number', 'pan_number', 'current_status', 'pincode'];

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_IN_REVIEW = 'in_review';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    /**
     * Get available statuses
     *
     * @return array
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_IN_REVIEW => 'In Review',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
        ];
    }

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * The attributes that are translatable.
     *
     * @var array
     */
    protected $translatedAttributes = ['shop_name', 'owner_name', 'description', 'address1', 'category', 'address2', 'city', 'state'];

    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addActiveGlobalScope();

        static::saved(function () {
            Cache::tags('sellers')->flush();
        });

        static::deleted(function () {
            Cache::tags('sellers')->flush();
        });
    }

    /**
     * Get the brand's logo.
     *
     * @return \Modules\Media\Entities\File
     */
    public function getLogoAttribute()
    {
        return $this->files->where('pivot.zone', 'logo')->first() ?: new File;
    }

    public function getImageAttribute()
    {
        return $this->files->where('pivot.zone', 'image')->first() ?: new File;
    }
    /**
     * Get table data for the resource
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function table()
    {
        return new SellerTable($this->newQuery()->withoutGlobalScope('active'));
    }

    public function getSellerTranslationById($id)
    {
        return SellerTranslation::where('seller_id', $id)->first();
    }

    /**
     * Get products for this seller
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function products()
    {
        return $this->belongsToMany(\Modules\Product\Entities\Product::class, 'product_seller', 'seller_id', 'product_id');
    }

    /**
     * Get a list of sellers for dropdown.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function list()
    {
        return Cache::tags('sellers')->rememberForever('sellers.list', function () {
            return static::withoutGlobalScope('active')
                ->where('is_active', 1)
                ->get()
                ->mapWithKeys(function ($seller) {
                    $name = $seller->shop_name ?: ($seller->owner_name ?: "Seller #{$seller->id}");
                    return [$seller->id => $name];
                });
        });
    }
}
