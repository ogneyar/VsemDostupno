<?php

namespace app\components;

use Yii;
use yii\base\Component;
use yii\base\BootstrapInterface;
use app\models\Module;

class ModuleManager extends Component implements BootstrapInterface
{
    public function bootstrap($app)
    {
        $modules = Module::find()->all();
        if ($modules) {
            foreach ($modules as $mod) {
                if ($mod->state) {
                    $class = 'app\modules\\' . $mod->name . '\Module'; 
                    Yii::$app->setModule($mod->name, ['class' => $class]);
                    Yii::$app->getModule($mod->name)->bootstrap(Yii::$app);
                }
            }
        }
    }
}