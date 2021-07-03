<?php

namespace app\modules\purchase\controllers\site;

use Yii;
use yii\web\Response;

use app\modules\purchase\models\PurchaseProduct;
use app\modules\purchase\models\PurchaseProviderBalance;

class ProviderController extends BaseController
{
    public function actionIndex()
    {
        $dataProvider = PurchaseProduct::getProviderProducts(Yii::$app->user->identity->entity->provider->id);
        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }
    
    public function actionContibute()
    {
        $dataProvider = PurchaseProduct::getProviderProductsGrouped(Yii::$app->user->identity->entity->provider->id);
        return $this->render('contibute', [
            'dataProvider' => $dataProvider,
        ]);
    }
}