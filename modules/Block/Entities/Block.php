<?php

namespace Modules\Block\Entities;

// use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\Factories\HasFactory;

use Modules\Media\Entities\File;
use Modules\Media\Eloquent\HasMedia;
use Illuminate\Support\Facades\Cache;
use Modules\Admin\Ui\AdminTable;
use Modules\Support\Eloquent\Model;
use Modules\Meta\Eloquent\HasMetaData;
use Modules\Support\Eloquent\Translatable;
use Modules\Block\Admin\BlockTable;


class Block extends Model
{
    use Translatable, HasMetaData, HasMedia;

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
    protected $fillable = ['identifier','is_active'];

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
    protected $translatedAttributes = ['name', 'content'];

    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addActiveGlobalScope();

        static::saving(function ($block) {
            $defaultLocale = 'en';

            if (locale() === $defaultLocale) {
                $translation = $block->translateOrNew($defaultLocale);
                $name = $translation->name;
                $content = $translation->content;

                if (!empty($name) || !empty($content)) {
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
                                    $content ?? '',
                                ];

                                $translated = $googleTranslate->translateBatch($texts, $defaultLocale, $targetLocale, 'html');

                                if ($translated && count($translated) === 2) {
                                    $targetTranslation = $block->translations->firstWhere('locale', $targetLocale);

                                    if (!$targetTranslation) {
                                        $targetTranslation = $block->translations()
                                            ->withoutGlobalScope('locale')
                                            ->where('locale', $targetLocale)
                                            ->first();

                                        if ($targetTranslation) {
                                            $block->translations->add($targetTranslation);
                                        } else {
                                            $targetTranslation = $block->getTranslationOrNew($targetLocale);
                                        }
                                    }

                                    $targetTranslation->name = $translated[0];
                                    $targetTranslation->content = $translated[1];
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('Auto-translation failed for Block: ' . $e->getMessage(), [
                            'exception' => $e,
                            'block_id' => $block->id,
                        ]);
                    }
                }
            }
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

    /**
     * Get table data for the resource
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function table()
    {
        return new BlockTable($this->newQuery()->withoutGlobalScope('active'));
    }

    public function getBlockTranslationById($id)
    {
        return BlockTranslation::where('block_id', $id)->first();
    }

}
