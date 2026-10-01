<?php

namespace Modules\Page\Entities;

use Spatie\Sitemap\Tags\Url;
use Illuminate\Support\Carbon;
use Modules\Admin\Ui\AdminTable;
use Illuminate\Http\JsonResponse;
use Modules\Support\Eloquent\Model;
use Modules\Meta\Eloquent\HasMetaData;
use Modules\Support\Eloquent\Sluggable;
use Spatie\Sitemap\Contracts\Sitemapable;
use Modules\Support\Eloquent\Translatable;

class Page extends Model implements Sitemapable
{
    use Translatable, Sluggable, HasMetaData;

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
    protected $fillable = ['slug', 'is_active'];

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
    protected $translatedAttributes = ['name', 'body'];

    /**
     * The attribute that will be slugged.
     *
     * @var string
     */
    protected $slugAttribute = 'name';


    public static function urlForPage($id)
    {
        return static::select('slug')->firstOrNew(['id' => $id])->url();
    }


    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addActiveGlobalScope();

        static::saving(function ($page) {
            $defaultLocale = 'en';

            if (locale() === $defaultLocale) {
                $translation = $page->translateOrNew($defaultLocale);
                $name = $translation->name;
                $body = $translation->body;

                if (!empty($name) || !empty($body)) {
                    try {
                        $locales = supported_locale_keys();
                        $targetLocales = array_filter($locales, function ($loc) use ($defaultLocale) {
                            return $loc !== $defaultLocale;
                        });

                        if (!empty($targetLocales)) {
                            $googleTranslate = resolve(\Modules\Translation\Services\GoogleTranslateService::class);

                            foreach ($targetLocales as $targetLocale) {
                                $texts = [
                                    $name ?? '',
                                    $body ?? '',
                                ];

                                $translated = $googleTranslate->translateBatch($texts, $defaultLocale, $targetLocale, 'html');

                                if ($translated && count($translated) === 2) {
                                    $targetTranslation = $page->translations->firstWhere('locale', $targetLocale);

                                    if (!$targetTranslation) {
                                        $targetTranslation = $page->translations()
                                            ->withoutGlobalScope('locale')
                                            ->where('locale', $targetLocale)
                                            ->first();

                                        if ($targetTranslation) {
                                            $page->translations->add($targetTranslation);
                                        } else {
                                            $targetTranslation = $page->getTranslationOrNew($targetLocale);
                                        }
                                    }

                                    $targetTranslation->name = $translated[0];
                                    $targetTranslation->body = $translated[1];
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('Auto-translation failed for Page: ' . $e->getMessage(), [
                            'exception' => $e,
                            'page_id' => $page->id,
                        ]);
                    }
                }
            }
        });
    }


    public function url()
    {
        if (is_null($this->slug)) {
            return '#';
        }

        return localized_url(locale(), $this->slug);
    }


    /**
     * Get table data for the resource
     *
     * @return JsonResponse
     */
    public function table()
    {
        return new AdminTable($this->newQuery()->withoutGlobalScope('active'));
    }


    public function toSitemapTag(): Url|string|array
    {
        return Url::create($this->slug)
            ->setLastModificationDate(Carbon::create($this->updated_at))
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
            ->setPriority(0.1);
    }

}
