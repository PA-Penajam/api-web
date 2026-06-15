<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;

class GoogleDriveService
{
    protected $client;
    protected $service;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setClientId(env('GOOGLE_DRIVE_CLIENT_ID'));
        $this->client->setClientSecret(env('GOOGLE_DRIVE_CLIENT_SECRET'));
        $this->client->refreshToken(env('GOOGLE_DRIVE_REFRESH_TOKEN'));

        $this->service = new Drive($this->client);
    }

    /**
     * Upload a file to Google Drive
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string|null $folderId
     * @return string|null Web View Link
     */
    /**
     * Upload a file to Google Drive
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string|null $folderId
     * @return string|null Web View Link
     */
    public function upload($file, $folderId = null)
    {
        if (!$folderId) {
            $folderId = env('GOOGLE_DRIVE_ROOT_FOLDER_ID');
        }

        // Feature: Upgrade organized by Date (YYYY-MM-DD)
        // Automatically create/find subfolder for today's date
        try {
            $dateFolderId = $this->getOrCreateDailyFolder($folderId);
            if ($dateFolderId) {
                $folderId = $dateFolderId;
            }
        } catch (\Exception $e) {
            // If checking/creating folder fails, fallback to root folder allowed
            // Log if necessary, but keep proceeding with upload to root
        }

        $fileMetadata = new DriveFile([
            'name' => $file->getClientOriginalName(),
            'parents' => [$folderId]
        ]);

        $content = file_get_contents($file->getRealPath());

        $uploadedFile = $this->service->files->create($fileMetadata, [
            'data' => $content,
            'mimeType' => $file->getClientMimeType(),
            'uploadType' => 'multipart',
            'fields' => 'id, webViewLink, webContentLink'
        ]);

        // Make the file publicly readable (optional, depending on requirements)
        // This makes it viewable by anyone with the link
        try {
            $permission = new \Google\Service\Drive\Permission();
            $permission->setRole('reader');
            $permission->setType('anyone');
            $this->service->permissions->create($uploadedFile->id, $permission);
        } catch (\Exception $e) {
            // Handle permission error or ignore
        }

        return $uploadedFile->webViewLink;
    }

    /**
     * Get or Create a folder for current date
     */
    private function getOrCreateDailyFolder($parentId)
    {
        $folderName = date('Y-m-d'); // Example: 2023-10-27

        // 1. Check if folder exists
        $query = "mimeType='application/vnd.google-apps.folder' and name='{$folderName}' and '{$parentId}' in parents and trashed=false";

        $files = $this->service->files->listFiles([
            'q' => $query,
            'fields' => 'files(id, name)'
        ]);

        if (count($files->getFiles()) > 0) {
            return $files->getFiles()[0]->getId();
        }

        // 2. Create if not exists
        $folderMetadata = new DriveFile([
            'name' => $folderName,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => [$parentId]
        ]);

        $folder = $this->service->files->create($folderMetadata, [
            'fields' => 'id'
        ]);

        return $folder->id;
    }

    /**
     * Upload file ke Google Drive dan return array dengan webViewLink.
     * Method ini dipanggil oleh controller yang mengharapkan return array.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string|null $folderId
     * @return array|null Array dengan key 'webViewLink' atau null jika gagal
     */
    public function uploadFile($file, $folderId = null)
    {
        $webViewLink = $this->upload($file, $folderId);
        
        if (!$webViewLink) {
            return null;
        }

        return [
            'webViewLink' => $webViewLink
        ];
    }
}
