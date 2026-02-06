<?php

defined('TYPO3') or die();

if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('reactions')) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaSelectItem(
        'sys_reaction',
        'reaction_type',
        [
            'label' => \OliverKroener\OkAiNews\Reaction\CreateNewsReaction::getDescription(),
            'value' => \OliverKroener\OkAiNews\Reaction\CreateNewsReaction::getType(),
            'icon' => \OliverKroener\OkAiNews\Reaction\CreateNewsReaction::getIconIdentifier(),
        ]
    );
}
