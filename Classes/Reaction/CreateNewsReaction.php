<?php

declare(strict_types=1);

namespace OliverKroener\OkAiNews\Reaction;

use OliverKroener\OkAiNews\Service\FileHandlingService;
use OliverKroener\OkAiNews\Service\NewsCreationService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use TYPO3\CMS\Reactions\Model\ReactionInstruction;
use TYPO3\CMS\Reactions\Reaction\ReactionInterface;

class CreateNewsReaction implements ReactionInterface
{
    public function __construct(
        private readonly FileHandlingService $fileHandlingService,
        private readonly NewsCreationService $newsCreationService,
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly StreamFactoryInterface $streamFactory,
    ) {}

    public static function getType(): string
    {
        return 'create-news-from-webhook';
    }

    public static function getDescription(): string
    {
        return 'LLL:EXT:ok_ai_news/Resources/Private/Language/locallang.xlf:reaction.description';
    }

    public static function getIconIdentifier(): string
    {
        return 'content-news';
    }

    public function react(
        ServerRequestInterface $request,
        array $payload,
        ReactionInstruction $reaction,
    ): ResponseInterface {
        $title = trim($payload['title'] ?? '');
        if ($title === '') {
            return $this->jsonResponse(['success' => false, 'error' => 'Field "title" is required'], 400);
        }

        $bodytext = $payload['bodytext'] ?? '';
        $storagePid = (int)($payload['storagePid'] ?? $reaction->toArray()['storage_pid'] ?? 0);

        $fileUid = null;
        $imageData = $payload['image'] ?? '';
        if ($imageData !== '') {
            $filename = $payload['imageFilename'] ?? 'news-image-' . date('Y-m-d-His') . '.jpg';
            $targetFolder = $payload['imageFolder'] ?? 'ai_news';

            try {
                $file = $this->fileHandlingService->createFileFromBase64($imageData, $filename, $targetFolder);
                $fileUid = $file->getUid();
            } catch (\RuntimeException $e) {
                return $this->jsonResponse(['success' => false, 'error' => 'Image processing failed: ' . $e->getMessage()], 400);
            }
        }

        try {
            $newsUid = $this->newsCreationService->createNewsRecord($title, $bodytext, $storagePid, $fileUid);
        } catch (\RuntimeException $e) {
            return $this->jsonResponse(['success' => false, 'error' => 'News creation failed: ' . $e->getMessage()], 500);
        }

        return $this->jsonResponse(['success' => true, 'newsUid' => $newsUid]);
    }

    private function jsonResponse(array $data, int $statusCode = 201): ResponseInterface
    {
        return $this->responseFactory
            ->createResponse($statusCode)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($this->streamFactory->createStream((string)json_encode($data, JSON_THROW_ON_ERROR)));
    }
}
