<?php

namespace Modules\Service\Entities;

use TypiCMS\NestableTrait;
use Spatie\Sitemap\Tags\Url;
use Illuminate\Support\Carbon;
use Modules\Media\Entities\File;
use Modules\Support\Eloquent\Model;
use Modules\Media\Eloquent\HasMedia;
use Illuminate\Support\Facades\Cache;
use Spatie\Sitemap\Contracts\Sitemapable;
use Modules\Support\Eloquent\Translatable;
use Modules\Service\Admin\ServiceTable;

class Service extends Model implements Sitemapable
{
    use Translatable, HasMedia, NestableTrait;

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
    protected $fillable = ['position', 'link_url', 'webpage_url', 'gradient_colors', 'is_active', 'is_active_web'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = ['translations'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [        
        'is_active' => 'boolean',
        'is_active_web' => 'boolean',
    ];

    /**
     * The attributes that are translatable.
     *
     * @var array
     */
    protected $translatedAttributes = ['name','description'];

    /**
     * The attribute that will be slugged.
     *
     * @var string
     */

    public function url()
    {
        return route('services.products.index', ['service' => $this->slug]);
    }

    public function getLogoAttribute()
    {
        return $this->files->where('pivot.zone', 'logo')->first() ?: new File;
    }

    public function table()
    {
        return new ServiceTable($this->newQuery()->withoutGlobalScope('active'));
    }

    public function toSitemapTag(): Url|string|array
    {
        return Url::create(route('services.products.index', $this->slug))
            ->setLastModificationDate(Carbon::create($this->updated_at))
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
            ->setPriority(0.1);
    }
}
