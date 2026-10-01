<?php

namespace Modules\Service\Services\Sections;

use Modules\Service\Services\Sections\Contracts\SectionHandlerInterface;
use Modules\Community\Entities\Community;
use Modules\Community\Entities\CommunityMembers;
use Modules\Audiobook\Entities\Audiobook;
use Modules\AudioSong\Entities\AudioSongAlbum;

/**
 * Album Quick Picks Section Handler
 * 
 * Handles album quick picks for multiple services
 */
class AlbumQuickPicksSectionHandler implements SectionHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handle(string $sectionId, ?int $userId = null): ?array
    {
        return [
            'id' => $sectionId,
            'type' => $this->getSectionType(),
            'data' => [], // Frontend handles this with local state
        ];
    }

    /**
     * Create specific quick picks section for a service type
     * 
     * @param string $id
     * @param int|null $userId
     * @param string $serviceType
     * @return array
     */
    public function createSpecificQuickPicks(string $id, ?int $userId, string $serviceType): array
    {
        $data = match($serviceType) {
            'community' => $this->fetchCommunities($userId),
            'audiobook' => $this->fetchAudiobooks(),
            'audiosong' => $this->fetchAudioSongs(),
            default => ['items' => [], 'title' => 'Quick Picks', 'subtitle' => 'For you']
        };

        return [
            'id' => $id,
            'type' => $this->getSectionType(),
            'service_type' => $serviceType,
            'data' => $data,
        ];
    }

    /**
     * Fetch communities for quick picks
     * 
     * @return array
     */
    private function fetchCommunities($userId): array
    {
        try {
            // Get all unique categories from active communities
            $categories = Community::where('is_active', 1)
                ->select('category')
                ->distinct()
                ->pluck('category')
                ->filter(function ($category) {
                    return !empty($category);
                })
                ->toArray();
            
            $selectedCategory = null;
            $title = 'Vibrant Communities';
            $subtitle = 'Connect with like-minded people';
            
            // Pick a random category if available
            if (!empty($categories)) {
                $selectedCategory = $categories[array_rand($categories)];
                
                // Generate attractive titles based on category
                $titlePairs = $this->getAttractiveTitle($selectedCategory);
                $title = $titlePairs['title'];
                $subtitle = $titlePairs['subtitle'];
                
                // Get communities from selected category
                $communities = Community::where('is_active', 1)
                    ->where('category', $selectedCategory)
                    ->inRandomOrder()
                    ->limit(15)
                    ->get();
            } else {
                // Fallback to all communities if no categories found
                $communities = Community::where('is_active', 1)
                    ->inRandomOrder()
                    ->limit(15)
                    ->get();
            }
            
            $items = $communities->map(function($community) use ($userId) {
                // Check if user has joined this community
                $isJoined = 0;
                if ($userId) {
                    $isJoined = CommunityMembers::where('community_id', $community->id)
                        ->where('user_id', $userId)
                        ->where('is_active', 1)
                        ->where('approval_status', 'approved')
                        ->exists() ? 1 : 0;
                }
                
                return [
                    'id' => $community->id,
                    'title' => $community->name,
                    'subtitle' => $community->description ?? 'Community',
                    'image' => asset('storage/community/' . $community->image) ?? '',
                    'members_count' => $community->members_count ?? 0,
                    'service_type' => 'community',
                    'is_joined' => $isJoined,
                    'is_verified' => $community->is_verified ?? 0,
                ];
            });

            return [
                'items' => $items,
                'title' => $title,
                'subtitle' => $subtitle
            ];
        } catch (\Exception $e) {
            \Log::error('Error fetching Communities for quick picks: ' . $e->getMessage());
            return [
                'items' => [],
                'title' => 'Communities',
                'subtitle' => 'Join and connect'
            ];
        }
    }

    /**
     * Fetch audiobooks for quick picks
     * 
     * @return array
     */
    private function fetchAudiobooks(): array
    {
        try {
            // Get a single random audiobook
            $book = Audiobook::where('is_active', 1)
                ->inRandomOrder()
                ->first();
            
            if (!$book) {
                return [
                    'items' => [],
                    'title' => 'Audiobooks',
                    'subtitle' => 'Listen and learn'
                ];
            }
            
            $items = [];
            foreach ($book->rootChapters as $chapter) {
                $firstSubChapter = $chapter->subChapters->first();
                $items[] = [
                    'book_id' => $book->id,
                    'chapter_id' => $chapter->id,
                    'subchapter_id' => $firstSubChapter->id,
                    'title' => $chapter->title,
                    'subtitle' => $firstSubChapter->title,
                    'image' => $book->logo->path ?? '',
                    'duration' => $firstSubChapter->duration ?? '',
                    'service_type' => 'audiobook'
                ];
            }

            // Limit to 15 items total
            $items = array_slice($items, 0, 15);

            $sectionTitle = $book->name;
            $sectionSubtitle = $book->author ?? 'Listen and learn';

            return [
                'items' => $items,
                'title' => $sectionTitle,
                'subtitle' => $sectionSubtitle
            ];
        } catch (\Exception $e) {
            \Log::error('Error fetching Audiobooks for quick picks: ' . $e->getMessage());
            return [
                'items' => [],
                'title' => 'Audiobooks',
                'subtitle' => 'Listen and learn'
            ];
        }
    }

    /**
     * Fetch audio songs for quick picks
     * 
     * @return array
     */
    private function fetchAudioSongs(): array
    {
        try {
            // Get a single random album with its tracks
            $album = AudioSongAlbum::where('is_active', 1)
                ->with('artist', 'tracks')
                ->inRandomOrder()
                ->first();
            
            if (!$album) {
                return [
                    'items' => [],
                    'title' => 'Audio Songs',
                    'subtitle' => 'Top picks for you'
                ];
            }
            
            // Get artist name
            $artistName = 'Unknown Artist';
            if ($album->artist) {
                $artistName = $album->artist->name ?? 'Unknown Artist';
            }
            
            // Get tracks from the album (limit to 15)
            $items = $album->tracks->take(15)->map(function($track) use ($album) {
                // Get artist name from track or fallback to album artist
                $trackArtistName = 'Unknown Artist';
                if ($track->artist) {
                    $trackArtistName = $track->artist->name ?? 'Unknown Artist';
                } elseif ($album->artist) {
                    $trackArtistName = $album->artist->name ?? 'Unknown Artist';
                }
                
                // Get track image (cover image)
                $trackImage = $album->cover_image ?? '';
                
                return [
                    'id' => $track->id,
                    'title' => $track->title,
                    'subtitle' => $trackArtistName,
                    'image' => $trackImage,
                    'duration' => $track->duration_ms ?? '',
                    'service_type' => 'audiosong'
                ];
            })->values()->toArray();

            return [
                'items' => $items,
                'title' => $album->title,
                'subtitle' => 'By ' . $artistName
            ];
        } catch (\Exception $e) {
            \Log::error('Error fetching Audio Songs for quick picks: ' . $e->getMessage());
            return [
                'items' => [],
                'title' => 'Audio Songs',
                'subtitle' => 'Top picks for you'
            ];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionType(): string
    {
        return 'album-quick-picks';
    }

    /**
     * Generate attractive titles and subtitles based on category
     * 
     * @param string $category
     * @return array
     */
    private function getAttractiveTitle(string $category): array
    {
        $category = strtolower(trim($category));
        
        $attractiveTitles = [
            'religious' => [
                'title' => '🙏 Sacred Communities',
                'subtitle' => 'Connect through faith and spirituality'
            ],
            'political' => [
                'title' => '🗳️ Political Voices',
                'subtitle' => 'Engage in meaningful discussions'
            ],
            'social' => [
                'title' => '👥 Social Connections',
                'subtitle' => 'Build friendships and networks'
            ],
            'profession' => [
                'title' => '💼 Professional Network',
                'subtitle' => 'Grow your career together'
            ],
            'educational' => [
                'title' => '📚 Learning Communities',
                'subtitle' => 'Expand your knowledge'
            ],
            'business' => [
                'title' => '🏢 Business Network',
                'subtitle' => 'Create opportunities and partnerships'
            ],
            'entertainment' => [
                'title' => '🎉 Entertainment Hub',
                'subtitle' => 'Have fun and enjoy together'
            ],
            'ngo' => [
                'title' => '❤️ Community Impact',
                'subtitle' => 'Make a difference together'
            ],
            'other' => [
                'title' => '🌟 More Communities',
                'subtitle' => 'Discover unique communities'
            ],
        ];

        // Return specific title if category matches, otherwise generate generic one
        if (isset($attractiveTitles[$category])) {
            return $attractiveTitles[$category];
        }

        // Generate generic attractive title for unknown categories
        return [
            'title' => '🌟 ' . ucfirst($category) . ' Communities',
            'subtitle' => 'Join our vibrant ' . strtolower($category) . ' community'
        ];
    }
}
