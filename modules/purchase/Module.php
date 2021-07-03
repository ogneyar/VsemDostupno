<?php

namespace app\modules\purchase;
use app\components\ModuleInterface;

/**
 * purchase module definition class
 */
class Module extends \yii\base\Module implements ModuleInterface
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\purchase\controllers';

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
            //'purchase' => 'purchase/site/default/index',
            'purchase/pricelist' => 'purchase/site/default/pricelist',
            'purchase/<_c:[\w\-]+>/<_a:[\w\-]+>' => 'purchase/site/<_c>/<_a>',
            'purchase/<_c:[\w\-]+>' => 'purchase/site/<_c>/index',
            
            //'admin/mailing' => 'mailing/admin/default/index',
            'admin/purchase/<_a:[\w\-]+>' => 'purchase/admin/default/<_a>',
            
        ]);
    }
}
