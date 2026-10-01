<?php

namespace Modules\Service\Services\Sections;

use Modules\Service\Services\Sections\Contracts\SectionHandlerInterface;
use Modules\Service\Services\Sections\DataFetchers\BusinessDataFetcher;
use Modules\Service\Services\Sections\DataFetchers\TemplateDataFetcher;
use Modules\AudioSong\Entities\AudioSongAlbum;
// use Modules\Audiobook\Entities\Audiobook; // Audiobooks only allowed in AlbumQuickPicksSectionHandler and ExpandableCardsSectionHandler

/**
 * Business Cards Section Handler
 * 
 * Handles business card grid layout with multiservice support
 */
class BusinessCardsSectionHandler implements SectionHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handle(string $sectionId, ?int $userId = null): ?array
    {
        return $this->createSpecificCards($sectionId, $userId, 'business');
    }

    /**
     * Create a specific card type (used by multi-service sections)
     * 
     * @param string $sectionId
     * @param int|null $userId
     * @param string $cardType One of: business, audiosong
     * @return array|null
     */
    public function createSpecificCards(string $sectionId, ?int $userId, string $cardType): ?array
    {
        $data = null;
        $serviceType = $cardType;

        switch ($cardType) {
            case 'business':
                $data = BusinessDataFetcher::fetch();
                break;
            case 'audiosong':
                $data = $this->fetchAudioSongs();
                break;
            // case 'audiobook':
            //     $data = $this->fetchAudioBooks();
            //     break;
            default:
                return null;
        }

        return [
            'id' => $sectionId,
            'type' => $this->getSectionType(),
            'service_type' => $serviceType,
            'data' => $data,
        ];
    }

    /**
     * Fetch Audio Songs for cards
     * 
     * @return array
     */
    private function fetchAudioSongs(): array
    {
        try {
            $albums = AudioSongAlbum::where('is_active', 1)
                ->with('artist')
                ->inRandomOrder()
                ->limit(10)
                ->get();
            
            $items = $albums->map(function($album) {
                // Handle artist name safely
                $artistName = 'Unknown Artist';
                if ($album->artist) {
                    $artistName = $album->artist->name ?? 'Unknown Artist';
                }

                return [
                    'id' => $album->id,
                    'album_id' => $album->id,
                    'slug' => $album->slug,
                    'title' => $album->title,
                    'artist' => $artistName,
                    'subtitle' => $artistName,
                    'template_url' => $album->cover_image ?? '',
                    'image' => $album->cover_image ?? '',
                    'album' => $album->title,
                    'service_type' => 'audiosong'
                ];
            });

            return [
                'audiosongs' => $items,
                'title' => 'Audio Songs',
                'subtitle' => 'Top tracks collection'
            ];
        } catch (\Exception $e) {
            \Log::error('Error fetching Audio Songs: ' . $e->getMessage());
            return [
                'audiosongs' => [],
                'title' => 'Audio Songs',
                'subtitle' => 'Top tracks collection'
            ];
        }
    }

    /**
     * Fetch Audio Books for cards
     * 
     * @return array
     */
    // Audiobooks only allowed in AlbumQuickPicksSectionHandler and ExpandableCardsSectionHandler
    /*
    private function fetchAudioBooks(): array
    {
        try {
            $audiobooks = Audiobook::where('is_active', 1)
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
                            'author' => $book->author,
                            'duration' => $subChapter->duration ?? 'N/A',
                            'template_url' => $book->logo->path ?? '',
                            'image' => $book->logo->path ?? '',
                            'service_type' => 'audiobook'
                        ];
                    }
                }
            }

            // Limit items for cards
            $items = array_slice($items, 0, 10);

            $featuredBook = $audiobooks->first();
            $sectionTitle = $featuredBook ? $featuredBook->name : 'Audiobooks';
            $sectionSubtitle = $featuredBook ? ($featuredBook->author ?? 'Must listen collections') : 'Must listen collections';

            return [
                'audiobooks' => $items,
                'title' => $sectionTitle,
                'subtitle' => $sectionSubtitle
            ];
        } catch (\Exception $e) {
            return [
                'audiobooks' => [],
                'title' => 'Audio Books',
                'subtitle' => 'Must listen collections'
            ];
        }
    }
    */

    /**
     * {@inheritdoc}
     */
    public function getSectionType(): string
    {
        return 'business-cards';
    }
}
