<?php

namespace Modules\MembershipFee\Entities;

use Modules\Support\Money;
use Modules\Admin\Ui\AdminTable;
use Modules\Support\Eloquent\Model;
use Modules\Product\Entities\Product;
use Modules\Support\Eloquent\Translatable;
use Illuminate\Support\Facades\Cache;
use Modules\MembershipFee\Entities\MembershipFee;

class MembershipFee extends Model
{
    use Translatable;

    /**
     * Active flash sale.
     *
     * @var self
     */
    private static $active;
    /**
     * The attributes that are translatable.
     *
     * @var array
     */
    public $translatedAttributes = ['name'];
    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['translations'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['is_active', 'duration', 'membershipfee'];

     /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get brand list.
     *
     * @return Collection
     */
    public static function list()
    {
        return self::where('is_active', 1)->get()->sortBy('name')->pluck('name', 'id');
    }
    
    public function table()
    {
        return new AdminTable($this->query());
    }

}
