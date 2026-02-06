<?php

declare(strict_types=1);

namespace OliverKroener\OkAiNews\Service;

use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FileHandlingService
{
    public function __construct(
        private readonly ResourceFactory $resourceFactory,
        private readonly StorageRepository $storageRepository,
    ) {}

    /**
     * Decode a base64 image string, store it in FAL, and return the File object.
     *
     * @param string $base64Data Raw base64 string (with or without data URI prefix)
     * @param string $filename Target filename
     * @param string $targetFolder Subfolder inside fileadmin (created if missing)
     */
    public function createFileFromBase64(string $base64Data, string $filename, string $targetFolder = 'ai_news'): File
    {
        $binaryData = $this->decodeBase64($base64Data);

        $tempFile = GeneralUtility::tempnam('ok_ai_news_');
        if (file_put_contents($tempFile, $binaryData) === false) {
            throw new \RuntimeException('Could not write temporary file', 1700000001);
        }

        try {
            $storage = $this->storageRepository->getDefaultStorage();
            if ($storage === null) {
                throw new \RuntimeException('No default storage found', 1700000002);
            }

            if (!$storage->hasFolder($targetFolder)) {
                $storage->createFolder($targetFolder);
            }
            $folder = $storage->getFolder($targetFolder);

            $file = $storage->addFile($tempFile, $folder, $filename);
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }

        return $file;
    }

    private function decodeBase64(string $data): string
    {
        // Strip optional data URI prefix (e.g. "data:image/jpeg;base64,")
        if (str_contains($data, ',')) {
            $data = substr($data, strpos($data, ',') + 1);
        }

        $decoded = base64_decode($data, true);
        if ($decoded === false || $decoded === '') {
            throw new \RuntimeException('Invalid base64 image data', 1700000003);
        }

        return $decoded;
    }
}
