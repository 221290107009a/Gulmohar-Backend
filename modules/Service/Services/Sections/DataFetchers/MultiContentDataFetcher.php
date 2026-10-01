<?php

namespace Modules\Service\Services\Sections\DataFetchers;

use Modules\AudioSong\Entities\AudioSongAlbum;
use Modules\Audiobook\Entities\Audiobook;

/**
 * Multi Content Data Fetcher
 * 
 * Handles fetching multi-content data (Songs, Books, Communities)
 */
class MultiContentDataFetcher
{
    /**
     * Fetch multi-content data for expandable cards section
     * 
     * @param int|null $userId
     * @return array
     */
    public static function fetch(?int $userId = null): array
    {
        try {
            $data = [];

            // 1. Audio Songs
            $albums = AudioSongAlbum::where('is_active', 1)
                ->inRandomOrder()
                ->limit(3)
                ->get();

            $songItems = [];
            foreach ($albums as $album) {
                $tracks = $album->tracks()->limit(3)->get();
                $listItems = [];
                foreach ($tracks as $track) {
                    $listItems[] = [
                        'id' => $track->id,
                        'title' => $track->title,
                        'subtitle' => $album->title,
                        'image' => $track->cover_image ?? '',
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

            // 2. Audiobooks
            $audiobooks = Audiobook::where('is_active', 1)
                ->inRandomOrder()
                ->limit(3)
                ->get();

            $bookItems = [];
            foreach ($audiobooks as $book) {
                $listItems = [];
                foreach ($book->rootChapters as $chapter) {
                    foreach ($chapter->subChapters as $subChapter) {
                        $listItems[] = [
                            'book_id' => $book->id,
                            'chapter_id' => $chapter->id,
                            'id' => $subChapter->id,
                            'title' => $chapter->title,
                            'subtitle' => $subChapter->title,
                            'image' => $book->logo->path ?? '',
                        ];
                    }
                }

                $listItems = array_slice($listItems, 0, 3);

                $bookItems[] = [
                    'id' => 'book-' . $book->id,
                    'title' => $book->name,
                    'description' => 'Top picks for you',
                    'stats' => $book->chapters()->count() . ' chapters',
                    'gridImages' => [$book->logo->path ?? ''],
                    'listItems' => $listItems
                ];
            }

            // Merge all items: Songs first, then Books
            return array_merge($songItems, $bookItems, $data);

        } catch (\Exception $e) {
            \Log::error('Error in MultiContentDataFetcher: ' . $e->getMessage());
            return [];
        }
    }
}
