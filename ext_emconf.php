<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'AI News Writer',
    'description' => 'Create news records from webhook reactions with image support',
    'category' => 'be',
    'author' => 'Oliver Kroener',
    'state' => 'stable',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-13.4.99',
            'reactions' => '12.4.0-13.4.99',
            'news' => '11.0.0-12.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
