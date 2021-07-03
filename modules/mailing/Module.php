<?php

namespace app\modules\mailing;
use app\components\ModuleInterface;

/**
 * mailing module definition class
 */
class Module extends \yii\base\Module implements ModuleInterface
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\mailing\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }
    
    public function bootstrap($app)
    {
        $app->getUrlManager()->addRules([
            'mailing' => 'mailing/site/default/index',
            'mailing/<_a:[\w\-]+>' => 'mailing/site/default/<_a>',
            'admin/mailing' => 'mailing/admin/default/index',
            'admin/mailing/<_a:[\w\-]+>' => 'mailing/admin/default/<_a>',
            
        ]);
    }
}
