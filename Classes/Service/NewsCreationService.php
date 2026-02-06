<?php

declare(strict_types=1);

namespace OliverKroener\OkAiNews\Service;

use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class NewsCreationService
{
    /**
     * Create a tx_news_domain_model_news record via DataHandler.
     *
     * @param string $title News title
     * @param string $bodytext News body (HTML allowed)
     * @param int $storagePid Page UID to store the record on
     * @param int|null $fileUid FAL file UID to attach as fal_media (sys_file_reference)
     * @return int UID of the created news record
     */
    public function createNewsRecord(string $title, string $bodytext, int $storagePid, ?int $fileUid = null): int
    {
        $newId = 'NEW' . uniqid('', true);
        $newsData = [
            'pid' => $storagePid,
            'title' => $title,
            'bodytext' => $bodytext,
            'type' => 0,
            'hidden' => 1,
        ];

        if ($fileUid !== null) {
            $fileRefId = 'NEW' . uniqid('ref', true);
            $newsData['fal_media'] = $fileRefId;

            $data = [
                'tx_news_domain_model_news' => [
                    $newId => $newsData,
                ],
                'sys_file_reference' => [
                    $fileRefId => [
                        'pid' => $storagePid,
                        'uid_local' => $fileUid,
                        'tablenames' => 'tx_news_domain_model_news',
                        'fieldname' => 'fal_media',
                        'table_local' => 'sys_file',
                    ],
                ],
            ];
        } else {
            $data = [
                'tx_news_domain_model_news' => [
                    $newId => $newsData,
                ],
            ];
        }

        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->start($data, []);
        $dataHandler->process_datamap();

        if (!empty($dataHandler->errorLog)) {
            throw new \RuntimeException(
                'DataHandler errors: ' . implode(', ', $dataHandler->errorLog),
                1700000010
            );
        }

        $newsUid = $dataHandler->substNEWwithIDs[$newId] ?? 0;
        if ($newsUid === 0) {
            throw new \RuntimeException('Failed to create news record', 1700000011);
        }

        return (int)$newsUid;
    }
}
