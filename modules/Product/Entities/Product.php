<?php

namespace Modules\Product\Entities;

use Illuminate\Http\Request;
use Spatie\Sitemap\Tags\Url;
use Illuminate\Support\Carbon;
use Modules\Support\Eloquent\Model;
use Modules\Media\Eloquent\HasMedia;
use Modules\Meta\Eloquent\HasMetaData;
use Modules\Support\Search\Searchable;
use Modules\Product\Admin\ProductTable;
use Modules\Support\Eloquent\Sluggable;
use Spatie\Sitemap\Contracts\Sitemapable;
use Modules\Support\Eloquent\Translatable;
use Modules\Product\Entities\Concerns\IsNew;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Product\Entities\Concerns\HasStock;
use Modules\Product\Entities\Concerns\Predicates;
use Modules\Product\Entities\Concerns\Filterable;
use Modules\Product\Entities\Concerns\QueryScopes;
use Modules\Product\Entities\Concerns\ModelMutators;
use Modules\Product\Entities\Concerns\ModelAccessors;
use Modules\Product\Entities\Concerns\HasSpecialPrice;
use Modules\Product\Entities\Concerns\EloquentRelations;
use Modules\Seller\Entities\Seller;

class Product extends Model implements Sitemapable
{
    use Translatable,
        Searchable,
        Filterable,
        Sluggable,
        HasMedia,
        HasMetaData,
        HasSpecialPrice,
        HasStock,
        SoftDeletes,
        IsNew,
        QueryScopes,
        ModelAccessors,
        ModelMutators,
        Predicates,
        EloquentRelations;

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
    protected $fillable = [
        'brand_id',
        'tax_class_id',
        'slug',
        'sku',
        'price',
        'special_price',
        'special_price_type',
        'special_price_start',
        'special_price_end',
        'selling_price',
        'manage_stock',
        'qty',
        'in_stock',
        'is_virtual',
        'is_active',
        'new_from',
        'new_to'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_virtual' => 'boolean',
        'is_active' => 'boolean',
        'special_price_start' => 'datetime',
        'special_price_end' => 'datetime',
        'new_from' => 'datetime',
        'new_to' => 'datetime',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'deleted_at' => 'datetime',
    ];


    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'base_image',
        'additional_images',
        'media',
        'formatted_price',
        'formatted_price_range',
        'has_percentage_special_price',
        'special_price_percent',
        'rating_percent',
        'does_manage_stock',
        'is_in_stock',
        'is_out_of_stock',
        'is_new',
        'variant',
    ];

    /**
     * The attributes that are translatable.
     *
     * @var array
     */
    protected array $translatedAttributes = [
        'name',
        'description',
        'short_description',
    ];


    /**
     * The attribute that will be slugged.
     *
     * @var string
     */
    protected string $slugAttribute = 'name';


    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::addActiveGlobalScope();

        static::saving(function ($product) {
            if (request()->has('brand_id')) {
                $brandVal = request('brand_id');
                if (!empty($brandVal)) {
                    if (!is_numeric($brandVal)) {
                        $brand = \Modules\Brand\Entities\Brand::withoutGlobalScope('active')
                            ->whereTranslation('name', trim($brandVal))
                            ->first();
                        if (!$brand) {
                            $brand = \Modules\Brand\Entities\Brand::create([
                                'is_active' => true,
                                'name' => trim($brandVal),
                            ]);
                        }
                        $product->brand_id = $brand->id;
                    }
                } else {
                    $product->brand_id = null;
                }
            }
        });

        static::saved(function ($product) {
            $attributes = request()->all();

            if (!empty($attributes)) {
                $product->categories()->sync(array_get($attributes, 'categories', []));
                $product->tags()->sync(array_get($attributes, 'tags', []));
                $product->upSellProducts()->sync(array_get($attributes, 'up_sells', []));
                $product->crossSellProducts()->sync(array_get($attributes, 'cross_sells', []));
                $product->relatedProducts()->sync(array_get($attributes, 'related_products', []));
                if (request()->has('sellers')) {
                    $rawSellers = (array) request('sellers');
                    $sellerIds = [];
                    foreach ($rawSellers as $item) {
                        if (empty($item)) continue;
                        if (is_numeric($item)) {
                            $sellerIds[] = (int) $item;
                        } else {
                            $seller = \Modules\Seller\Entities\Seller::withoutGlobalScope('active')
                                ->whereTranslation('shop_name', trim($item))
                                ->first();
                            if (!$seller) {
                                $seller = \Modules\Seller\Entities\Seller::create([
                                    'user_id' => auth()->id() ?: 1,
                                    'shop_name' => trim($item),
                                    'owner_name' => trim($item),
                                    'description' => trim($item),
                                    'address1' => 'Main Market',
                                    'address2' => 'Main Market',
                                    'city' => 'City',
                                    'state' => 'State',
                                    'phone' => '9999999999',
                                    'email' => 'seller_' . time() . '_' . rand(100, 999) . '@store.com',
                                    'category' => 'all',
                                    'current_status' => 'approved',
                                    'is_active' => true,
                                ]);
                            }
                            $sellerIds[] = $seller->id;
                        }
                    }
                    $product->sellers()->sync($sellerIds);
                }
                if (request()->has('size_inventory')) {
                    $rows = request()->input('size_inventory', []);
                    if (is_array($rows)) {
                        $product->sizeInventory()->delete();
                        foreach (array_values($rows) as $position => $row) {
                            $sizeName = trim($row['size'] ?? '');
                            if ($sizeName === '') continue;
                            \Modules\Product\Entities\ProductSizeInventory::create([
                                'product_id' => $product->id,
                                'size'       => $sizeName,
                                'qty'        => (int) ($row['qty'] ?? 0),
                                'in_stock'   => filter_var($row['in_stock'] ?? true, FILTER_VALIDATE_BOOLEAN),
                                'position'   => $position,
                            ]);
                        }
                    }
                }
            }

            $product->withoutEvents(function () use ($product) {
                $product->update([
                    'selling_price' => ($product->hasSpecialPrice() ? $product->getSpecialPrice() : $product->price)->amount(),
                ]);
            });
        });
    }


    /**
     * Get table data for the resource
     *
     * @param Request $request
     *
     * @return ProductTable
     */
    public function table(Request $request): ProductTable
    {
        $query = $this->newQuery()
            ->withoutGlobalScope('active')
            ->withName()
            ->withBaseImage()
            ->withPrice()
            ->addSelect(['id', 'is_active', 'created_at', 'updated_at'])
            ->when($request->has('except'), function ($query) use ($request) {
                $query->whereNotIn('id', explode(',', $request->except));
            });

        return new ProductTable($query);
    }


    public function clean(): array
    {
        $cleanExceptAttributes = [
            'description',
            'short_description',
            'translations',
            'categories',
            'files',
            'in_stock',
            'brand_id',
            'tax_class',
            'tax_class_id',
            'viewed',
            'is_active',
            'created_at',
            'updated_at',
            'deleted_at',
        ];

        return array_except(
            $this->toArray(),
            $cleanExceptAttributes
        );
    }


    public function url(): string
    {
        return route('products.show', ['slug' => $this->slug]);
    }


    /**
     * Get the indexable data array for the product.
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        # MySQL Full-Text search handles indexing automatically.
        if (config('scout.driver') === 'mysql') {
            return [];
        }

        $translations = $this->translations()
            ->withoutGlobalScope('locale')
            ->get(['name', 'description', 'short_description']);

        return [
            'id' => $this->id,
            'translations' => $translations,
        ];
    }


    public function searchTable(): string
    {
        return 'product_translations';
    }


    public function searchKey(): string
    {
        return 'product_id';
    }


    public function searchColumns(): array
    {
        return ['name'];
    }


    /**
     * Help HasMedia trait to extract media
     * for this model from the HTTP request.
     *
     * @return mixed
     */
    public function extractMediaFromRequest(): mixed
    {
        $media = collect(request('media', []));

        return [
            'base_image' => $media->first(),
            'additional_images' =>
                $media->except(
                    $media->keys()->first()
                )->toArray(),
            'downloads' => request('downloads', []),
        ];
    }


    public function toSitemapTag(): Url|string|array
    {
        return Url::create($this->url())
            ->setLastModificationDate(Carbon::create($this->updated_at))
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
            ->setPriority(0.1);
    }

    /**
     * Get the sellers for the product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function sellers()
    {
        return $this->belongsToMany(Seller::class, 'product_seller')
                    ->withTimestamps();
    }
}
