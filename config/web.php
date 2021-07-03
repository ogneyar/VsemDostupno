<?php

$params = require(__DIR__ . '/params.php');

$config = [
    'id' => 'basic',
    'language' => 'ru-RU',
    'charset' => 'UTF-8',
    'timeZone' => 'Europe/Moscow',
    'basePath' => dirname(__DIR__),
    'bootstrap' => [
        'log',
        'app\components\ModuleManager'
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'PGl5g0Qfp-5pd6hiZf8tJYD5d5prKiTn',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\modules\site\models\User',
            'enableAutoLogin' => true,
            'loginUrl' => ['profile/login'],
            'on ' . yii\web\User::EVENT_AFTER_LOGIN => ['app\modules\site\models\User', 'handleAfterLogin'],
        ],
        'errorHandler' => [
            'errorAction' => 'site/default/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                
                // 'host' => 'ssl://smtp.yandex.ru',
                // 'username' => 'info@vsemdostupno.ru',
                // 'password' => 'Jeish2ai',
                // 'port' => '465',
                
                // 'host' => 'ssl://smtp.mail.ru',
                // 'username' => 'vsemdostupno@bk.ru',
                // 'password' => 'uyu4Ci%1aYYT',
                // 'port' => '465',
                
                // 'host' => 'ssl://smtp.rambler.ru',
                // 'username' => 'prizmarket@rambler.ru',
                // 'password' => 'Qwrtui_13',
                // 'port' => '465',
				
				// 'host' => 'tls://smtp.sibnet.ru',
    //             'username' => 'prizmarket@sibnet.ru',
    //             'password' => 'Qwrtui_13',
    //             'port' => '25',
                
	            'host' => 'ssl://smtp.gmail.com',
                'username' => 'vsemdostupno2021@gmail.com',
                'password' => 'ukpOUTLti21-',
                'port' => '465',
                
                
                // 'encryption' => 'ssl',
                // 'encryption' => 'tls',
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'formatter' => [
            'defaultTimeZone' => 'Europe/Moscow',
        ],
        'urlManager' => require(__DIR__ . '/urlManager.php'),
        'reCaptcha' => [
            'name' => 'reCaptcha',
            'class' => 'himiklab\yii2\recaptcha\ReCaptcha',
            // 'siteKey' => '6Lc1IBMTAAAAAPZnX-2z8X9eQm5mVYu_sB6KG93n', // VsemDostupno.ru
            // 'siteKey' => '6LeVYLMaAAAAAGZOjVCmTDu5B5wl9dBOZkDey-V0', // ПриродныеПродукты.рус
			
			'siteKey' => '6Lf4VL4aAAAAAPuoCGVnr0vXR_b7C5uE0VfVdqiX', // Будь-Здоров.рус
			
            // 'secret' => '6Lc1IBMTAAAAANGZcCq4fzLV9K_waMq2d2ydC-cv', // VsemDostupno.ru
            // 'secret' => '6LeVYLMaAAAAANyXlq2FJlGl1_lUcvol00aZ9L60', // ПриродныеПродукты.рус
			
			'secret' => '6Lf4VL4aAAAAAHFptl-9Bvr4eR2qaN1pyH82uAOP', // Будь-Здоров.рус
			
        ],
        'i18n' => [
            'translations' => [
                'app*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'sourceLanguage' => 'ru-RU',
                ],
            ],
        ],
        'db' => require(__DIR__ . '/db.php'),
    ],
    'modules' => [
        'site' => [
            'class' => 'app\modules\site\Module',
        ],
        'admin' => [
            'class' => 'app\modules\admin\Module',
        ],
        'api' => [
            'class' => 'app\modules\api\Module',
        ],
     ],
    'controllerMap' => [
        'elfinder' => [
            'class' => 'app\modules\site\controllers\ElfinderController',
            'access' => ['admin', 'superadmin'],
            'root' => [
                'baseUrl' => '@web',
                'basePath'=>'@webroot',
                'path' => '',
                'name' => 'Файлы'
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
