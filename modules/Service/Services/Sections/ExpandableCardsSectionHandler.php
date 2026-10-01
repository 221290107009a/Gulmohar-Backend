<?php

namespace Modules\Service\Services\Sections;

use Modules\Service\Services\Sections\Contracts\SectionHandlerInterface;
use Modules\Service\Services\Sections\DataFetchers\MultiContentDataFetcher;
use Modules\AudioSong\Entities\AudioSongAlbum;
use Modules\Audiobook\Entities\Audiobook;

/**
 * Expandable Cards Section Handler
 * 
 * Handles expandable content cards for songs, books, and communities
 */
class ExpandableCardsSectionHandler implements SectionHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handle(string $sectionId, ?int $userId = null): ?array
    {
        return [
            'id' => $sectionId,
            'type' => $this->getSectionType(),
            'data' => MultiContentDataFetcher::fetch($userId),
        ];
    }

    /**
     * Create specific expandable cards for a content type
     * 
     * @param string $sectionId
     * @param int|null $userId
     * @param string $contentType One of: audiobook, audiosong
     * @return array|null
     */
    public function createSpecificExpandableCards(string $sectionId, ?int $userId, string $contentType): ?array
    {
        $data = [];
        
        switch ($contentType) {
            case 'audiosong':
                $data = $this->fetchAudioSongs($userId);
                break;
            case 'audiobook':
                $data = $this->fetchAudioBooks($userId);
                break;
            default:
                return null;
        }

        return [
            'id' => $sectionId,
            'type' => $this->getSectionType(),
            'service_type' => $contentType,
            'data' => $data,
        ];
    }

    /**
     * Fetch Audio Songs for expandable cards
     * 
     * @param int|null $userId
     * @return array
     */
    private function fetchAudioSongs(?int $userId = null): array
    {
        try {
            $albums = AudioSongAlbum::where('is_active', 1)
                ->inRandomOrder()
                ->limit(6)
                ->get();

            $songItems = [];
            foreach ($albums as $album) {
                $tracks = $album->tracks()->limit(5)->get();
                $listItems = [];
                
                foreach ($tracks as $track) {
                    $listItems[] = [
                        'id' => $track->id,
                        'title' => $track->title,
                        'subtitle' => $album->title,
                        'image' => $track->cover_image ?? $album->cover_image ?? '',
                    ];
                }

                $songItems[] = [
                    'id' => 'song-' . $album->id,
                    'title' => $album->title,
                    'description' => 'Trending Songs',
                    'stats' => $album->tracks()->count() . ' tracks',
                    'gridImages' => [$album->cover_image ?? ''],
                    'listItems' => $listItems
                ];
            }

            return $songItems;

        } catch (\Exception $e) {
            \Log::error('Error in fetchAudioSongs (ExpandableCards): ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetch Audio Books for expandable cards
     * 
     * @param int|null $userId
     * @return array
     */
    private function fetchAudioBooks(?int $userId = null): array
    {
        try {
            // Get a single random audiobook
            $book = Audiobook::where('is_active', 1)
                ->inRandomOrder()
                ->first();

            if (!$book) {
                return [];
            }

            $chapters = $book->rootChapters()->limit(5)->get();
            $listItems = [];
            
            foreach ($chapters as $chapter) {
                $firstSubChapter = $chapter->subChapters->first();
                $listItems[] = [
                    'book_id' => $book->id,
                    'chapter_id' => $chapter->id,
                    'subchapter_id' => $firstSubChapter->id,
                    'title' => $chapter->title,
                    'subtitle' => $firstSubChapter->title,
                    'image' => $book->logo->path ?? '',
                ];
            }
            
            $listItems = array_slice($listItems, 0, 5);

            return [
                [
                    'id' => 'book-' . $book->id,
                    'title' => $book->name,
                    'description' => 'Top picks for you',
                    'stats' => $book->chapters()->count() . ' chapters',
                    'gridImages' => [$book->logo->path ?? ''],
                    'listItems' => $listItems
                ]
            ];

        } catch (\Exception $e) {
            \Log::error('Error in fetchAudioBooks (ExpandableCards): ' . $e->getMessage());
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionType(): string
    {
        return 'expandable-cards';
    }
}
