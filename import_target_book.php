<?php

// Bootstrap Laravel
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Modules\Ebook\Entities\Ebook;
use Modules\Audiobook\Entities\Audiobook;
use Modules\Ebook\Entities\EbookPageAudioMap;
use Modules\Book\Entities\Book;
use Modules\Book\Entities\BookChapter;
use Modules\Book\Entities\BookDetail;
use Modules\Book\Entities\BookPublisher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

$targetEbookId = 1;
$targetAudiobookId = 5;

echo "Starting Book Migration for Ebook ID $targetEbookId...\n";

// 1. Fetch Ebook and Audiobook
$ebook = Ebook::find($targetEbookId);
if (!$ebook) {
    die("Error: Ebook ID $targetEbookId not found.\n");
}

$audiobook = Audiobook::find($targetAudiobookId);
if (!$audiobook) {
    die("Error: Audiobook ID $targetAudiobookId not found.\n");
}

echo "Found Ebook: {$ebook->title}\n";
echo "Found Audiobook: {$audiobook->name}\n";

DB::transaction(function() use ($ebook, $audiobook) {
    // 2. Manage Publisher
    // Ebook publisher is: बी. एल. पारस (बुद्ध अलंकार बौद्ध)
    $publisherName = "बी. एल. पारस (बुद्ध अलंकार बौद्ध)";
    $publisher = DB::table('book_publisher_translations')
        ->where('name', $publisherName)
        ->first();
        
    $publisherId = null;
    if ($publisher) {
        $publisherId = $publisher->book_publisher_id;
        echo "Found existing publisher ID: $publisherId\n";
    } else {
        echo "Creating new publisher...\n";
        $newPub = BookPublisher::create([
            'slug' => Str::slug($publisherName) ?: 'publisher-' . time(),
            'is_active' => true,
            'en' => [
                'name' => $publisherName,
                'address' => ''
            ]
        ]);
        $publisherId = $newPub->id;
        echo "Created publisher ID: $publisherId\n";
    }

    // 3. Clean up existing book if it exists
    $existingBook = Book::where('slug', $ebook->slug)->first();
    if ($existingBook) {
        echo "Found existing Book record (ID: {$existingBook->id}). Deleting for clean import...\n";
        
        $chapterIds = BookChapter::where('book_id', $existingBook->id)->pluck('id')->toArray();
        
        // Delete detail translations
        DB::table('book_detail_translations')->whereIn('book_detail_id', function($query) use ($chapterIds) {
            $query->select('id')->from('book_details')->whereIn('book_chapter_id', $chapterIds);
        })->delete();
        
        // Delete details
        BookDetail::whereIn('book_chapter_id', $chapterIds)->delete();
        
        // Delete chapters
        BookChapter::where('book_id', $existingBook->id)->delete();
        
        // Delete book translations
        DB::table('book_translations')->where('book_id', $existingBook->id)->delete();
        
        // Delete media associations
        DB::table('entity_files')
            ->where('entity_type', 'Modules\Book\Entities\Book')
            ->where('entity_id', $existingBook->id)
            ->delete();
            
        // Delete book
        $existingBook->delete();
        echo "Deletion complete.\n";
    }

    // 4. Create Book
    $bookData = [
        'author_id' => $ebook->author_id,
        'book_publisher_id' => $publisherId,
        'book_translator_id' => null,
        'category_ids' => $ebook->category_ids,
        'total_likes' => $ebook->total_likes ?: 0,
        'upload_status' => 'completed',
        'slug' => $ebook->slug,
        'language' => $ebook->language ?: 'Hindi',
        'religion' => $ebook->relegion ?: 'buddhist', // map relegion -> religion
        'price' => $ebook->price,
        'is_free' => (bool)$ebook->is_free,
        'is_active' => (bool)$ebook->is_active,
        'published_at' => $ebook->published_at,
        'en' => [
            'title' => $ebook->title,
            'description' => $ebook->description ?: '',
            'notes' => $ebook->notes ?: '',
            'versions' => '[]'
        ]
    ];
    
    $book = Book::create($bookData);
    echo "Created Book ID: {$book->id}\n";

    // 5. Copy Media Associations (Cover / Logo)
    $ebookLogo = DB::table('entity_files')
        ->where('entity_type', 'Modules\Ebook\Entities\Ebook')
        ->where('entity_id', $ebook->id)
        ->where('zone', 'logo')
        ->first();
        
    if ($ebookLogo) {
        echo "Copying cover image (File ID: {$ebookLogo->file_id})...\n";
        DB::table('entity_files')->insert([
            'file_id' => $ebookLogo->file_id,
            'entity_type' => 'Modules\Book\Entities\Book',
            'entity_id' => $book->id,
            'zone' => 'logo',
            'lang' => $ebookLogo->lang ?: 'en',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    // 6. Migrate Chapters and Page Details
    $allChapters = $audiobook->chapters;
    $chapterOrder = 0;
    
    foreach ($ebook->indexes as $index) {
        echo "Migrating Chapter: {$index->title}...\n";
        
        $chapter = BookChapter::create([
            'book_id' => $book->id,
            'parent_id' => null,
            'name' => $index->title,
            'order' => $chapterOrder++,
            'is_active' => true
        ]);
        
        $pageOrder = 0;
        foreach ($index->pages as $pIdx => $page) {
            // Find mapped audio chapter
            $mapping = EbookPageAudioMap::where('ebook_page_id', $page->id)->first();
            $audioChapter = null;
            
            if ($mapping) {
                $audioChapter = $allChapters->where('id', $mapping->audiobook_chapter_id)->first();
            }
            
            $slug = 'Page-' . ($pIdx + 1);
            $duration = 0;
            $audioFile = null;
            
            if ($audioChapter) {
                $slug = $audioChapter->title ?: $slug;
                $audioFile = $audioChapter->audio_file;
                $duration = (int)$audioChapter->duration;
            }
            
            echo "  Migrating Page {$slug} (Audio: " . ($audioFile ?: 'None') . ")...\n";
            
            BookDetail::create([
                'book_chapter_id' => $chapter->id,
                'slug' => $slug,
                'duration' => $duration,
                'audio_file' => $audioFile,
                'order' => $pageOrder++,
                'is_active' => true,
                'en' => [
                    'content' => $page->data ?: ''
                ]
            ]);
        }
    }
});

echo "Migration finished successfully!\n";
