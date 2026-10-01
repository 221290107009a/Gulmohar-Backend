<?php

namespace FleetCart\Console\Commands;

use Illuminate\Console\Command;
use FleetCart\Services\GoogleDriveService;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use ZipArchive;
use Exception;

class GoogleDriveBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:google-drive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform a daily MySQL database backup, compress it to ZIP, and upload it to Google Drive via OAuth 2.0.';

    /**
     * Execute the console command.
     *
     * @param GoogleDriveService $googleDriveService
     * @return int
     */
    public function handle(GoogleDriveService $googleDriveService): int
    {
        Log::info('Google Drive Backup: Starting backup process...');
        $this->info('Starting Google Drive Backup...');

        $tempDir = storage_path('app/backups');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $dbConnection = config('database.default');
        $dbConfig = config("database.connections.{$dbConnection}");

        if (!$dbConfig || $dbConnection !== 'mysql') {
            $errorMsg = 'Google Drive Backup failed: Only MySQL database connection is supported.';
            Log::error($errorMsg);
            $this->error($errorMsg);
            return Command::FAILURE;
        }

        $dbName = $dbConfig['database'];
        $dbUser = $dbConfig['username'];
        $dbPassword = $dbConfig['password'];
        $dbHost = $dbConfig['host'];
        $dbPort = $dbConfig['port'] ?? '3306';

        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $sqlFilename = "backup_{$dbName}_{$timestamp}.sql";
        $zipFilename = "backup_{$dbName}_{$timestamp}.zip";

        $sqlPath = "{$tempDir}/{$sqlFilename}";
        $zipPath = "{$tempDir}/{$zipFilename}";

        // 1. Perform MySQL Dump
        try {
            $this->info('Dumping database...');
            // Using Symfony Process for secure command execution
            $process = new Process([
                'mysqldump',
                "--user={$dbUser}",
                "--password={$dbPassword}",
                "--host={$dbHost}",
                "--port={$dbPort}",
                $dbName,
                "--result-file={$sqlPath}"
            ]);

            $process->run();

            if (!$process->isSuccessful()) {
                throw new Exception('mysqldump failed: ' . $process->getErrorOutput());
            }

            if (!file_exists($sqlPath) || filesize($sqlPath) === 0) {
                throw new Exception('Database dump file is empty or was not created.');
            }

            Log::info("Google Drive Backup: Database dumped successfully to {$sqlFilename}");
            $this->info('Database dumped successfully.');
        } catch (Exception $e) {
            $errorMsg = 'Google Drive Backup failed during MySQL dump: ' . $e->getMessage();
            Log::error($errorMsg);
            $this->error($errorMsg);
            $this->cleanupLocalFiles([$sqlPath, $zipPath]);
            return Command::FAILURE;
        }

        // 2. Compress to ZIP
        try {
            $this->info('Compressing SQL dump to ZIP...');
            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new Exception("Cannot create ZIP file at {$zipPath}");
            }

            $zip->addFile($sqlPath, $sqlFilename);
            $zip->close();

            if (!file_exists($zipPath) || filesize($zipPath) === 0) {
                throw new Exception('Compressed ZIP file is empty or was not created.');
            }

            Log::info("Google Drive Backup: Database compressed successfully to {$zipFilename}");
            $this->info('Compression complete.');
        } catch (Exception $e) {
            $errorMsg = 'Google Drive Backup failed during ZIP compression: ' . $e->getMessage();
            Log::error($errorMsg);
            $this->error($errorMsg);
            $this->cleanupLocalFiles([$sqlPath, $zipPath]);
            return Command::FAILURE;
        }

        // 3. Upload to Google Drive using GoogleDriveService (OAuth 2.0)
        try {
            $this->info('Uploading ZIP archive to Google Drive...');
            $folderId = config('services.google_drive.folder_id');

            $googleDriveService->uploadFile($zipPath, $zipFilename, $folderId);

            Log::info("Google Drive Backup: Successfully uploaded {$zipFilename} to Google Drive.");
            $this->info('Upload successful.');
        } catch (Exception $e) {
            $errorMsg = 'Google Drive Backup failed during Google Drive upload: ' . $e->getMessage();
            Log::error($errorMsg);
            $this->error($errorMsg);
            $this->cleanupLocalFiles([$sqlPath, $zipPath]);
            return Command::FAILURE;
        }

        // 4. Remote cleanup: Delete backups older than BACKUP_KEEP_DAYS
        try {
            $this->info('Cleaning up old backups on Google Drive...');
            $keepDays = (int) env('BACKUP_KEEP_DAYS', 30);
            $folderId = config('services.google_drive.folder_id');

            $deletedCount = $googleDriveService->deleteOldBackups($keepDays, $folderId);

            Log::info("Google Drive Backup cleanup success: Deleted {$deletedCount} old backup files from Google Drive.");
            $this->info("Remote cleanup complete. Deleted {$deletedCount} old backup(s).");
        } catch (Exception $e) {
            Log::error('Google Drive Backup cleanup warning: ' . $e->getMessage());
            $this->warn('Remote cleanup encountered a warning: ' . $e->getMessage());
        }

        // 5. Local cleanup: Remove temporary files
        $this->cleanupLocalFiles([$sqlPath, $zipPath]);
        $this->info('Local cleanup complete.');

        Log::info('Google Drive Backup: Process finished successfully.');
        return Command::SUCCESS;
    }

    /**
     * Safely delete local files.
     *
     * @param array $filePaths
     * @return void
     */
    protected function cleanupLocalFiles(array $filePaths): void
    {
        foreach ($filePaths as $path) {
            if (!empty($path) && file_exists($path)) {
                @unlink($path);
            }
        }
    }
}
