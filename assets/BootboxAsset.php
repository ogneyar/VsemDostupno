<?php

namespace app\assets;

use Yii;
use yii\web\AssetBundle;

class BootboxAsset extends AssetBundle
{
    public $sourcePath = '@vendor/bower/bootbox.js';
    public $js = [
        'bootbox.js',
    ];

    public static function overrideSystemMessageBox()
    {
        Yii::$app->view->registerJs('
            yii.alert = function(message, ok) {
                bootbox.alert(message, function() {
                    !ok || ok();
                });
            }

            yii.confirm = function(message, ok, cancel) {
                bootbox.confirm(message, function(result) {
                    if (result) { !ok || ok(); } else { !cancel || cancel(); }
                });
            }

            yii.prompt = function(message, ok, cancel) {
                bootbox.prompt(message, function(result) {
                    if (result) { !ok || ok(); } else { !cancel || cancel(); }
                });
            }
        ');

        if(Yii::$app->language !== null && strlen(Yii::$app->language) >= 2) {
            Yii::$app->view->registerJs('bootbox.setDefaults({locale: "' . substr(Yii::$app->language, 0, 2) . '"});');
        }
    }
}
