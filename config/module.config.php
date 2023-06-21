<?php
namespace ExtendedFacets\Module\Configuration;

$config = [
    'service_manager' => [
        'allow_override' => true,
        'aliases' => [
            'sidefacets' => 'ExtendedFacets\Recommend\SideFacets',
        ],
        'factories' => [
            'ExtendedFacets\Recommend\SideFacets' => 'ExtendedFacets\Recommend\Factory::getSideFacets',
        ],
    ],
];

return $config;

