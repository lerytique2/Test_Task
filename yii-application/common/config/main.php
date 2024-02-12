<?php

use sizeg\jwt\Jwt;
use yii\caching\FileCache;

return [
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(__DIR__, 2) . '/vendor',
    'components' => [
        'cache' => [
            'class' => FileCache::class,
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
        'jwt' => [
            'class' => Jwt::class,
            'key' => '4GPNO93hbS1L0VUPPIMo7NNN2Zi0JDVxy8g1s7tA6+E=',
        ],
    ],
];
