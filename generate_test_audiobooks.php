<?php

use Illuminate\Support\Str;
use Modules\User\Entities\User;
use Modules\Audiobook\Entities\Audiobook;
use Modules\Audiobook\Entities\AudiobookChapter;
use Modules\Audiobook\Entities\AudiobookCategory;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Starting Audiobook generation script...\n";

// Configuration
$numberOfAudiobooks = 15;
$userId = 22; // As identified previously
$categories = AudiobookCategory::pluck('id')->toArray();

if (empty($categories)) {
    echo "No categories found. Please create categories first.\n";
    exit(1);
}

$user = User::find($userId);
if (!$user) {
    $user = User::first();
    if (!$user) {
        echo "No users found. Please create a user first.\n";
        exit(1);
    }
    $userId = $user->id;
}

echo "Using User ID: $userId\n";

DB::beginTransaction();

try {
    for ($i = 1; $i <= $numberOfAudiobooks; $i++) {
        $name = "Test Audiobook " . Str::random(5) . " " . $i;
        $slug = Str::slug($name);
        
        echo "Creating Audiobook: $name\n";
        
        $audiobook = Audiobook::create([
            'slug' => $slug,
            'author' => "Test Author $i",
            'narrator' => "Test Narrator $i",
            'language' => $i % 2 == 0 ? 'English' : 'Hindi',
            'is_active' => true,
            'user_id' => $userId,
            'upload_status' => 'approved',
            'is_public' => true,
            'en' => [
                'name' => $name,
                'description' => "This is a test description for $name. It contains some interesting facts about the book.",
            ],
            'hi' => [
                'name' => "परीक्षण ऑडियोबुक $i",
                'description' => "यह $name के लिए एक परीक्षण विवरण है। इसमें पुस्तक के बारे में कुछ दिलचस्प तथ्य हैं।",
            ]
        ]);

        // Attach random categories
        $randomCategories = (array) array_rand(array_flip($categories), min(2, count($categories)));
        $audiobook->categories()->sync($randomCategories);

        // Add 1-2 chapters
        $numChapters = rand(1, 2);
        for ($j = 1; $j <= $numChapters; $j++) {
            $songNum = rand(1, 16);
            AudiobookChapter::create([
                'audiobook_id' => $audiobook->id,
                'title' => "Chapter $j of $name",
                'duration' => rand(300, 3600), // 5 to 60 minutes
                'audio_url' => "https://www.soundhelix.com/examples/mp3/SoundHelix-Song-{$songNum}.mp3",
                'order' => $j,
                'is_active' => true,
                'user_id' => $userId,
                'upload_status' => 'approved',
            ]);
        }
    }

    DB::commit();
    echo "\nSuccessfully generated $numberOfAudiobooks audiobooks with chapters.\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "\nError occurred: " . $e->getMessage() . "\n";
    exit(1);
}
