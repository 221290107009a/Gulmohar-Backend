<?php

namespace Modules\Service\Services\Sections\DataFetchers;

use Modules\Template\Entities\Template;
use Modules\Badge\Entities\Badge;

/**
 * Template Data Fetcher
 * 
 * Handles fetching and formatting template/quote data
 */
class TemplateDataFetcher
{
    /**
     * Get home templates (random quotes)
     * 
     * @return array
     */
    public static function fetchHomeTemplates(?int $userId = null): array
    {
        try {
            $templates = Template::with(['templateCategory'])
                ->where('is_active', 1)
                ->whereRaw("EXISTS (SELECT 1 FROM template_categories WHERE FIND_IN_SET(id, templates.template_category_id) AND is_active = 1)")
                ->inRandomOrder()
                ->limit(10)
                ->get();
            
            $data = self::formatTemplates($templates, $userId);
            $titleData = self::generateDynamicTitle('home');
            $data['title'] = 'Daily Quotes';
            $data['subtitle'] = 'Wisdom and inspiration daily';
            $data['categoryId'] = $templates->first() ? self::extractFirstCategoryId($templates->first()->template_category_id) : '';
            return $data;
        } catch (\Exception $e) {
            return ['templates' => [], 'title' => 'Daily Quotes', 'subtitle' => 'Wisdom and inspiration daily'];
        }
    }

    /**
     * Get day special templates
     * 
     * @return array
     */
    public static function fetchDaySpecialTemplates(?int $userId = null): array
    {
        try {
            $today = date('m-d');
            $title = 'Day Special';
            
            // Priority 1: Special day matching today's date (Category ID 1)
            $templates = Template::with(['templateCategory'])
                ->where('is_active', 1)
                ->whereRaw("EXISTS (SELECT 1 FROM template_categories WHERE FIND_IN_SET(id, templates.template_category_id) AND is_active = 1)")
                ->whereRaw("FIND_IN_SET(1, template_category_id)")
                ->whereRaw("DATE_FORMAT(day_special_date, '%m-%d') = ?", [$today])
                ->inRandomOrder()
                ->get();

           /*  if (!$templates->isEmpty()) {
                // If we found specific templates for today, try to get a better title
                $firstTemplate = $templates->first();
                if ($firstTemplate && $firstTemplate->name) {
                    $title = strip_tags($firstTemplate->name);
                }
            } */

            // Priority 2: If no specific date matches, any random quote from category 1
            if ($templates->isEmpty()) {
                $templates = Template::with(['templateCategory'])
                    ->where('is_active', 1)
                    ->whereRaw("EXISTS (SELECT 1 FROM template_categories WHERE FIND_IN_SET(id, templates.template_category_id) AND is_active = 1)")
                    ->whereRaw("FIND_IN_SET(1, template_category_id)")
                    ->inRandomOrder()
                    ->get();
            }

            // Priority 3: Fallback to general templates
            if ($templates->isEmpty()) {
                return self::fetchHomeTemplates($userId);
            }

            $data = self::formatTemplates($templates, $userId);
            $titleData = self::generateDynamicTitle('day_special');
            $data['title'] = $titleData['title'] ?? 'Day Special';
            $data['subtitle'] = $titleData['subtitle'] ?? 'Special quotes only for you';
            $data['categoryId'] = '1';
            return $data;
        } catch (\Exception $e) {
            return ['templates' => [], 'title' => $title, 'subtitle' => 'Special quotes only for you'];
        }
    }

    /**
     * Get quotes by random category
     * 
     * @return array
     */
    public static function fetchQuotesByRandomCategory(?int $userId = null): array
    {
        try {
            // Get category IDs that actually have at least one active template (prevents title/template mismatch)
            $usedCategoryIds = \DB::table('templates')
                ->where('is_active', 1)
                ->pluck('template_category_id')
                ->flatMap(fn($ids) => array_map('intval', array_map('trim', explode(',', (string)$ids))))
                ->filter()
                ->unique()
                ->toArray();

            $categories = \Modules\TemplateCategory\Entities\TemplateCategory::where('is_active', 1)
                ->whereIn('id', $usedCategoryIds)
                ->pluck('id')
                ->toArray();
            
            if (empty($categories)) {
                \Log::warning('[TemplateDataFetcher] No categories found, using fallback', ['userId' => $userId]);
                return self::fetchHomeTemplates($userId);
            }
            
            // Pick a random category (guaranteed to have active templates)
            $randomCategoryId = $categories[array_rand($categories)];
            $categoryName = \Modules\TemplateCategory\Entities\TemplateCategory::find($randomCategoryId)?->name ?? 'Quotes';
            
            // Fetch templates from random category
            $templates = Template::with(['templateCategory'])
                ->where('is_active', 1)
                ->whereRaw("EXISTS (SELECT 1 FROM template_categories WHERE FIND_IN_SET(id, templates.template_category_id) AND is_active = 1)")
                ->whereRaw("FIND_IN_SET(?, template_category_id)", [$randomCategoryId])
                ->inRandomOrder()
                ->limit(10)
                ->get();
            
            // Fallback if no templates found - ensure title/subtitle preserved
            if ($templates->isEmpty()) {
                $fallback = self::fetchHomeTemplates($userId);
                $titleData = self::generateDynamicTitle('category', ['category_name' => $categoryName]);
                $fallback['title'] = $titleData['title'] ?? $categoryName . '';
                $fallback['subtitle'] = $titleData['subtitle'] ?? 'Inspiration from ' . $categoryName;
                return $fallback;
            }
            
            $data = self::formatTemplates($templates, $userId);
            $titleData = self::generateDynamicTitle('category', ['category_name' => $categoryName]);
            $data['title'] = $titleData['title'] ?? $categoryName . '';
            $data['subtitle'] = $titleData['subtitle'] ?? 'Inspiration from ' . $categoryName;
            $data['selected_category_id'] = $randomCategoryId;
            $data['categoryId'] = $randomCategoryId;
            return $data;
        } catch (\Exception $e) {
            return ['templates' => [], 'title' => 'Quotes', 'subtitle' => 'Daily wisdom and inspiration'];
        }
    }

    /**
     * Get most liked quotes
     * 
     * @return array
     */
    public static function fetchMostLikedQuotes(?int $userId = null): array
    {
        try {
            // Get template IDs ordered by most likes
            $templateIds = \DB::table('templates')
                ->leftJoin('template_user_likes', 'templates.id', '=', 'template_user_likes.template_id')
                ->where('templates.is_active', 1)
                ->selectRaw('templates.id, COUNT(template_user_likes.id) as likes_count_calculated')
                ->groupBy('templates.id')
                ->orderBy('likes_count_calculated', 'DESC')
                ->limit(10)
                ->pluck('id');
            
            // Now fetch full template data with relationships
            $templates = Template::with(['templateCategory'])
                ->whereIn('id', $templateIds)
                ->whereRaw("EXISTS (SELECT 1 FROM template_categories WHERE FIND_IN_SET(id, templates.template_category_id) AND is_active = 1)")
                ->get();
            
            // Fallback to random templates if none found
            if ($templates->isEmpty()) {
                return self::fetchHomeTemplates($userId);
            }
            
            $data = self::formatTemplates($templates, $userId);
            $titleData = self::generateDynamicTitle('most_liked');
            $data['title'] = $titleData['title'] ?? 'Most Liked Quotes';
            $data['subtitle'] = $titleData['subtitle'] ?? 'Popular wisdom from community';
            $data['categoryId'] = $templates->first() ? self::extractFirstCategoryId($templates->first()->template_category_id) : '';
            return $data;
        } catch (\Exception $e) {
            return ['templates' => [], 'title' => 'Most Liked Quotes', 'subtitle' => 'Popular wisdom from community'];
        }
    }

    /**
     * Get quotes with listing type support (all, random, most-liked)
     * 
     * @param string $listingType Either 'all', 'random' for random category, or 'most-liked'
     * @return array
     */
    public static function fetchQuotes(string $listingType = 'all', ?int $userId = null): array
    {
        if ($listingType === 'random') {
            return self::fetchQuotesByRandomCategory($userId);
        } elseif ($listingType === 'most-liked') {
            return self::fetchMostLikedQuotes($userId);
        } else {
            return self::fetchHomeTemplates($userId);
        }
    }

    /**
     * Get Audio Song Albums for home grid
     * 
     * @return array
     */
    public static function fetchAudioSongsForGrid(): array
    {
        try {
            // Get a single random album with its tracks
            $album = \Modules\AudioSong\Entities\AudioSongAlbum::where('is_active', 1)
                ->with('artist', 'tracks')
                ->inRandomOrder()
                ->first();
            
            if (!$album) {
                return ['templates' => [], 'title' => 'Audio Songs', 'subtitle' => 'Top tracks collection'];
            }
            
            $items = $album->tracks->take(15)->map(function($track) use ($album) {
                // Get artist name
                $artistName = $album->artist ? $album->artist->name : 'Unknown Artist';
                
                // Get track image or fallback to album cover
                $trackImage = $track->cover_image ?? $album->cover_image ?? '';
                
                return [
                    'id' => $track->id,
                    'album_id' => $album->id,
                    'title' => $track->title,
                    'subtitle' => $artistName,
                    'template_url' => $trackImage,
                    'service_type' => 'audiosong'
                ];
            });

            // Generate dynamic title based on album name
            $titleData = self::generateDynamicAudioTitle($album->title);
            
            return [
                'templates' => $items,
                'title' => $titleData['title'],
                'subtitle' => $titleData['subtitle']
            ];
        } catch (\Exception $e) {
            return ['templates' => [], 'title' => 'Audio Songs', 'subtitle' => 'Top tracks collection'];
        }
    }

    /**
     * Get political templates
     * 
     * @return array
     */
    public static function fetchPoliticalTemplates(?int $userId = null): array
    {
        try {
            $templates = \Modules\PoliticalTemplate\Entities\PoliticalTemplate::with(['political'])
                ->where('is_active', 1)
                ->inRandomOrder()
                ->get();
            
            foreach ($templates as $template) {
                // Template logo URL
                $template->template_url = $template->logo->path ?? null;

                // User like status
                $template->user_like = \Modules\PoliticalTemplate\Entities\PoliticalTemplateUserLikes::where([
                    'user_id' => $userId,
                    'political_template_id' => $template->id
                ])->exists() ? 1 : 0;

                // Badge URL
                if (!empty($template->badge_id)) {
                    $badge = Badge::find($template->badge_id);
                    $template->badge_url = $badge->logo->path ?? null;
                }

                // Political Party Name
                $template->political_name = $template->political->name ?? 'N/A';

                // Share Message
                $template->share_message = $template->message ?? setting('quote_share_message');
            }

            return [
                'templates' => $templates,
                'title' => 'Political Quotes',
                'subtitle' => 'Latest political wisdom',
                'categoryId' => ''
            ];
        } catch (\Exception $e) {
            return ['templates' => [], 'title' => 'Political Quotes', 'subtitle' => 'Latest political wisdom', 'categoryId' => ''];
        }
    }

    /**
     * Get Audio Song Albums for home grid
     * 
     * @return array
     */
    public static function fetchAudioBooksForGrid(): array
    {
        try {
            $audiobooks = \Modules\Audiobook\Entities\Audiobook::where('is_active', 1)
                ->inRandomOrder()
                ->limit(5)
                ->get();
            
            $items = [];
            foreach ($audiobooks as $book) {
                foreach ($book->rootChapters as $chapter) {
                    foreach ($chapter->subChapters as $subChapter) {
                        $items[] = [
                            'book_id' => $book->id,
                            'chapter_id' => $chapter->id,
                            'id' => $subChapter->id,
                            'title' => $chapter->title,
                            'subtitle' => $subChapter->title,
                            'template_url' => $book->logo->path ?? '',
                            'service_type' => 'audiobook'
                        ];
                    }
                }
            }

            // Limit items for grid
            $items = array_slice($items, 0, 15);

            $featuredBook = $audiobooks->first();
            $sectionTitle = $featuredBook ? $featuredBook->name : 'Audiobooks';
            $sectionSubtitle = $featuredBook ? ($featuredBook->author ?? 'Must listen collections') : 'Must listen collections';

            return [
                'templates' => $items,
                'title' => $sectionTitle,
                'subtitle' => $sectionSubtitle
            ];
        } catch (\Exception $e) {
            return ['templates' => [], 'title' => 'Audiobooks', 'subtitle' => 'Must listen collections'];
        }
    }

    /**
     * Get quotes by specific category
     * 
     * @param int $categoryId
     * @param string $title
     * @return array
     */
    public static function fetchQuotesByCategory(int $categoryId, string $title = '', ?int $userId = null): array
    {
        try {
            $templates = Template::with(['templateCategory'])
                ->where('is_active', 1)
                ->whereRaw("EXISTS (SELECT 1 FROM template_categories WHERE FIND_IN_SET(id, templates.template_category_id) AND is_active = 1)")
                ->whereRaw("FIND_IN_SET(?, template_category_id)", [$categoryId])
                ->inRandomOrder()
                ->get();
            
            // Fallback to random templates if none found in category
            if ($templates->isEmpty()) {
                $fallback = self::fetchHomeTemplates($userId);
                return array_merge($fallback, [
                    'title' => $title ?: 'Shrestha Vichar',
                    'subtitle' => 'Daily wisdom and inspiration'
                ]);
            }

            $data = self::formatTemplates($templates, $userId);
            $data['title'] = $title ?: 'Shrestha Vichar';
            $data['subtitle'] = 'Daily wisdom and inspiration';
            $data['categoryId'] = $categoryId;
            return $data;
        } catch (\Exception $e) {
            return ['templates' => [], 'title' => $title ?: 'Shrestha Vichar', 'subtitle' => 'Daily wisdom and inspiration'];
        }
    }

    /**
     * Format templates for standardized output
     * 
     * @param \Illuminate\Database\Eloquent\Collection $templates
     * @return array
     */
    private static function formatTemplates($templates, ?int $userId = null): array
    {
        // Pre-fetch liked template IDs for the user to avoid N+1 queries
        $likedTemplateIds = [];
        if ($userId) {
            $templateIds = $templates->pluck('id')->toArray();
            
            $likedTemplateIds = \DB::table('template_user_likes')
                ->where('user_id', $userId)
                ->whereIn('template_id', $templateIds)
                ->pluck('template_id')
                ->toArray();
        } else {
            // Guest user, no need to pre-fetch likes
            // \Log::info('[TemplateDataFetcher] formatTemplates called for guest user');
        }

        // Transform to array format to ensure is_liked is properly included in JSON response
        $formattedTemplates = [];
        foreach ($templates as $template) {
            $templateUrl = '';
            if ($template->logo && $template->logo->path) {
                $templateUrl = $template->logo->path;
            }
            
            $badgeUrl = null;
            if (!empty($template->badge_id)) {
                $badge = Badge::find($template->badge_id);
                if ($badge && $badge->logo && $badge->logo->path) {
                    $badgeUrl = $badge->logo->path;
                }
            }
            
            // Set share message
            $shareMessage = '';
            if (isset($template->message) && $template->message != '') {
                if ($template->is_default_message) {
                    if (isset($template->templateCategory->message) && $template->templateCategory->message != '') {
                        if ($template->templateCategory && $template->templateCategory->is_default_message) {
                            $shareMessage = setting('quote_share_message');
                        } else {
                            $shareMessage = $template->templateCategory->message;
                        }
                    } else {
                        $shareMessage = setting('quote_share_message');
                    }
                } else {
                    $shareMessage = $template->message;
                }
            } elseif (isset($template->templateCategory->message) && $template->templateCategory->message != '') {
                if ($template->templateCategory && $template->templateCategory->is_default_message) {
                    $shareMessage = setting('quote_share_message');
                } else {
                    $shareMessage = $template->templateCategory->message;
                }
            } else {
                $shareMessage = setting('quote_share_message');
            }
            
            $formattedTemplates[] = [
                'id' => $template->id,
                'name' => $template->name,
                'message' => $template->message,
                'template_url' => $templateUrl,
                'badge_url' => $badgeUrl,
                'badge_id' => $template->badge_id,
                'badge_position' => $template->badge_position,
                'footer_color' => $template->footer_color,
                'footer_text_color' => $template->footer_text_color,
                'profile_border_color' => $template->profile_border_color,
                'footer_id' => $template->footer_id,
                'share_message' => $shareMessage,
                'likes' => $template->likes ?? 0,
                'likes_count' => $template->likes ?? 0,
                'share' => $template->share ?? 0,
                'download' => $template->download ?? 0,
                'created_at' => $template->created_at,
                'is_active' => $template->is_active,
                'is_default_message' => $template->is_default_message,
                'is_liked' => in_array($template->id, $likedTemplateIds),
                'user_like' => in_array($template->id, $likedTemplateIds) ? 1 : 0,
            ];
        }
        
        return ['templates' => $formattedTemplates];
    }

    /**
     * Extract first category ID from comma-separated template_category_id
     * 
     * @param mixed $categoryId
     * @return string
     */
    private static function extractFirstCategoryId($categoryId): string
    {
        if (empty($categoryId)) {
            return '';
        }
        $ids = explode(',', (string)$categoryId);
        return trim($ids[0]);
    }

    /**
     * Generate dynamic title for audio songs based on album name
     *
     * @param string $albumName
     * @return array
     */
    private static function generateDynamicAudioTitle(string $albumName): array
    {
        $titles = [
            ['title' => '🎵 ' . $albumName, 'subtitle' => 'Best tracks from this album'],
            ['title' => '🎶 ' . $albumName, 'subtitle' => 'Listen to amazing tracks'],
            ['title' => '♪ ' . $albumName . ' Special', 'subtitle' => 'Curated tracks for you'],
            ['title' => '🎧 ' . $albumName, 'subtitle' => 'Top picks from album'],
            ['title' => '🎼 ' . $albumName . ' Collection', 'subtitle' => 'Must listen tracks'],
        ];

        return $titles[array_rand($titles)];
    }

    /**
     * Generate dynamic title for quote sections
     *
     * @param string $context
     * @param array $meta
     * @return array
     */
    private static function generateDynamicTitle(string $context, array $meta = []): array
    {
        $contextTitles = [
            'home' => [
                ['title' => '✨ Dil Se Likhe Alfaaz', 'subtitle' => 'Quotes that touch your heart'],
                ['title' => '📖 Aaj Ke Best Quotes', 'subtitle' => 'Wisdom for everyday life'],
                ['title' => '💭 Soch Vichar', 'subtitle' => 'Daily inspiration for you'],
                ['title' => '🌟 Aman Ke Alfaaz', 'subtitle' => 'Words that bring peace'],
                ['title' => '📚 Khud Ko Samajhe', 'subtitle' => 'Know yourself better'],
            ],
            'most_liked' => [
                ['title' => '❤️ Sabse Zyada Pasand Kiye Gaye', 'subtitle' => 'Loved by Sangho community'],
                ['title' => '🔥 Popular on Sangho', 'subtitle' => 'Trending thoughts'],
                ['title' => '⭐ Janta Ke Pasand', 'subtitle' => 'Community favorites'],
                ['title' => '💖 Sabka Dil Jeetne Wale', 'subtitle' => 'Everyone\'s favorite quotes'],
                ['title' => '🌈 Sabse Achche Saffed', 'subtitle' => 'The best of the best'],
            ],
            'category' => [
                ['title' => ($meta['category_name'] ?? 'Quotes') . ' Ke Quotes', 'subtitle' => 'Specially for you'],
                ['title' => ($meta['category_name'] ?? 'Quotes') . ' Quotes Aapke Liye', 'subtitle' => 'Just for you'],
                ['title' => '✨ ' . ($meta['category_name'] ?? 'Quotes') . ' Ke Vichar', 'subtitle' => 'Chosen especially'],
            ],
            'day_special' => [
                ['title' => '🎉 Aaj Ka Vishesh Quote', 'subtitle' => 'Only for today'],
                ['title' => '🎊 Aaj Iska Din Hai', 'subtitle' => 'Special for today'],
                ['title' => '🌟 Aaj Ka Khass Alfaaz', 'subtitle' => 'Special message for today'],
            ],
        ];

        $pool = $contextTitles[$context] ?? [];

        return $pool[array_rand($pool)] ?? ['title' => 'Quote', 'subtitle' => 'Daily wisdom'];
    }
}
