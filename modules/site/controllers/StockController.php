<?php
namespace app\modules\site\controllers;
use Yii;
use app\models\StockHead;
use app\models\StockBody;
use app\models\ProviderStock;
use yii\data\ActiveDataProvider;
use app\models\Provider;
use yii\Exception;
use app\models\UnitContibution;
use yii\web\ForbiddenHttpException;

class StockController extends BaseController
{
    public function actionIndex()
    {
        $providers = Provider::find()->all();
        foreach ($providers as $provider) {
            $provider_array[] = $provider->user_id;
            if($provider->user_id==Yii::$app->user->identity->id){
                $provider_id=$provider->id;
            }
        }
        if (in_array(Yii::$app->user->identity->id, $provider_array)) {

            $dataProvider = new ActiveDataProvider([
                'query' => ProviderStock::findBySql('SELECT ps.*  FROM provider_stock as ps INNER JOIN stock_body as body ON ps.stock_body_id=body.id INNER JOIN stock_head as head ON body.stock_head_id=head.id WHERE head.provider_id = '.$provider_id.' AND head.deleted_by_provider=0  ORDER BY head.date DESC'),
                'sort' => false,
            ]);

            return $this->render('index',
                ['dataProvider' => $dataProvider]);
        } else {
            throw new ForbiddenHttpException('Действие не разрешено.');
        }
    }

    public function actionContibute()
    {
        /*$providers = Provider::find()->all();
        foreach ($providers as $provider) {
            $provider_array[] = $provider->user_id;
            if ($provider->user_id == Yii::$app->user->identity->id) {
                $provider_id = $provider->id;
            }
        }


                if (in_array(Yii::$app->user->identity->entity->id, $provider_array)) {

                    $dataProvider = new ActiveDataProvider([
                        'query' => UnitContibution::findBySql('SELECT u.* FROM unit_contibution as u INNER JOIN `order` as o ON u.order_id=o.id INNER JOIN order_has_product as ohp ON ohp.order_id=o.id INNER JOIN provider_stock as stock ON u.provider_stock_id=stock.id INNER JOIN stock_body as body ON stock.stock_body_id=body.id INNER JOIN stock_head as head ON body.stock_head_id=head.id WHERE ohp.provider_id= ' . $provider_id . ' AND head.deleted_by_provider=0'),

                    ]);


                } else {
                    throw new ForbiddenHttpException('Действие не разрешено.');
                }*/


        $providers = Provider::find()->all();
        foreach ($providers as $provider) {
            $provider_array[] = $provider->user_id;
            if($provider->user_id==Yii::$app->user->identity->id){
                $provider_id=$provider->id;
            }
        }
        if (in_array(Yii::$app->user->identity->id, $provider_array)) {

            $dataProvider = ProviderStock::getDepositsByProvider($provider_id);
            
            return $this->render('contibute',
                ['dataProvider' => $dataProvider]);
        } else {
            throw new ForbiddenHttpException('Действие не разрешено.');
        }
    }

    public function actionDeletecon($id)
    {
        $head = StockHead::findOne($id);
        $head->deleted_by_provider = 1;
        $head->save();
        return $this->redirect('contibute');
    }

    public function actionDelete($id)
    {
        $stock=ProviderStock::findOne(['id'=>$id]);
        $head=$stock->stock_body->stockHead;
        $head->deleted_by_provider=1;
        $head->save();
        return $this->redirect('index');
    }

}

?>