<?php

namespace FleetCart\Services;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Exception;

class GoogleDriveService
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Drive
     */
    protected $driveService;

    /**
     * Authenticate and initialize the Google Client with OAuth 2.0 User credentials.
     *
     * @return void
     * @throws Exception
     */
    public function authenticate(): void
    {
        if ($this->client && $this->driveService) {
            return;
        }

        $clientId = config('services.google_drive.client_id');
        $clientSecret = config('services.google_drive.client_secret');
        $refreshToken = config('services.google_drive.refresh_token');

        if (empty($clientId) || empty($clientSecret)) {
            throw new Exception("Google Client ID or Client Secret is not configured in services.php");
        }

        if (empty($refreshToken)) {
            throw new Exception("Google Refresh Token is missing. Please authorize the application at /google/auth first.");
        }

        try {
            $client = new Client();
            $client->setClientId($clientId);
            $client->setClientSecret($clientSecret);
            $client->setAccessType('offline');

            // Fetch the access token using the refresh token
            $token = $client->fetchAccessTokenWithRefreshToken($refreshToken);
            if (isset($token['error'])) {
                throw new Exception("Google OAuth token refresh failed: " . ($token['error_description'] ?? $token['error']));
            }
            $client->setAccessToken($token);

            $this->client = $client;
            $this->driveService = new Drive($this->client);
        } catch (Exception $e) {
            Log::error('Google Drive Authentication Failure: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Upload a file to Google Drive using Resumable Chunked Upload.
     *
     * @param string $filePath Local file path
     * @param string $filename Remote file name
     * @param string|null $folderId Target folder ID
     * @return DriveFile
     * @throws Exception
     */
    public function uploadFile(string $filePath, string $filename, ?string $folderId = null): DriveFile
    {
        $this->authenticate();

        if (!file_exists($filePath)) {
            throw new Exception("Local backup file not found for upload: {$filePath}");
        }

        $fileMetadata = new DriveFile([
            'name' => $filename
        ]);

        if (!empty($folderId)) {
            $fileMetadata->setParents([$folderId]);
        }

        // Define chunk size (5MB minimum required by Google API)
        $chunkSizeBytes = 5 * 1024 * 1024;

        // Defer request to set up resumable upload
        $this->client->setDefer(true);
        
        $request = $this->driveService->files->create($fileMetadata, [
            'supportsAllDrives' => true,
        ]);

        $media = new \Google\Http\MediaFileUpload(
            $this->client,
            $request,
            'application/zip',
            null,
            true,
            $chunkSizeBytes
        );
        $media->setFileSize(filesize($filePath));

        $status = false;
        $handle = fopen($filePath, 'rb');
        
        if ($handle === false) {
            throw new Exception("Unable to open local file: {$filePath}");
        }

        try {
            while (!$status && !feof($handle)) {
                $chunk = fread($handle, $chunkSizeBytes);
                $status = $media->nextChunk($chunk);
            }
        } finally {
            fclose($handle);
            $this->client->setDefer(false);
        }

        if ($status instanceof DriveFile) {
            Log::info("Successfully uploaded backup file to Google Drive: {$filename} (ID: {$status->id})");
            return $status;
        }

        throw new Exception("Chunked upload failed to complete.");
    }

    /**
     * List all files in a specific Google Drive folder.
     *
     * @param string|null $folderId
     * @return array
     * @throws Exception
     */
    public function listFiles(?string $folderId = null): array
    {
        $this->authenticate();

        $optParams = [
            'pageSize' => 100,
            'fields' => 'files(id, name, createdTime)',
            'supportsAllDrives' => true,
            'includeItemsFromAllDrives' => true,
        ];

        $q = ["trashed = false"];
        if (!empty($folderId)) {
            $q[] = "'{$folderId}' in parents";
        }

        $optParams['q'] = implode(' and ', $q);

        try {
            $results = $this->driveService->files->listFiles($optParams);
            return $results->getFiles();
        } catch (Exception $e) {
            Log::error("Failed to list Google Drive files: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Clean up backups older than a certain number of days in Google Drive.
     *
     * @param int $keepDays
     * @param string|null $folderId
     * @return int Number of deleted files
     * @throws Exception
     */
    public function deleteOldBackups(int $keepDays, ?string $folderId = null): int
    {
        $this->authenticate();

        $files = $this->listFiles($folderId);
        $deletedCount = 0;
        $expiryDate = Carbon::now()->subDays($keepDays);

        foreach ($files as $file) {
            // Delete ZIP files matching backup naming conventions
            if (str_contains($file->getName(), '.zip')) {
                $createdTime = Carbon::parse($file->getCreatedTime());
                if ($createdTime->lessThan($expiryDate)) {
                    try {
                        $this->driveService->files->delete($file->getId(), [
                            'supportsAllDrives' => true,
                        ]);
                        Log::info("Deleted old Google Drive backup: {$file->getName()} (ID: {$file->getId()})");
                        $deletedCount++;
                    } catch (Exception $e) {
                        Log::error("Failed to delete old backup {$file->getName()} (ID: {$file->getId()}): {$e->getMessage()}");
                    }
                }
            }
        }

        return $deletedCount;
    }
}
