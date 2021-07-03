<?php

Yii::setAlias('@tests', dirname(__DIR__) . '/tests');

$params = require(__DIR__ . '/params.php');
$db = require(__DIR__ . '/db.php');

return [
    'id' => 'basic-console',
    'timeZone' => 'Europe/Moscow',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'gii'],
    'controllerNamespace' => 'app\commands',
    'modules' => [
        'gii' => 'yii\gii\Module',
    ],
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                
                // 'host' => 'ssl://smtp.yandex.ru',
                // 'username' => 'info@vsemdostupno.ru',
                // 'password' => 'Jeish2ai',
                // 'port' => '465',
                
                'host' => 'ssl://smtp.mail.ru',
                'username' => 'vsemdostupno@bk.ru',
                'password' => 'uyu4Ci%1aYYT',
                'port' => '465',
				
				// 'host' => 'ssl://smtp.gmail.com',
                // 'username' => 'vsemdostupno2021@gmail.com',
                // 'password' => 'ukpOUTLti21-',
                // 'port' => '587',
                
                // 'encryption' => 'ssl',
            ],
            
        ],
        'urlManager' => require(__DIR__ . '/urlManager.php'),
        'db' => $db,
    ],
    'params' => $params,
];
