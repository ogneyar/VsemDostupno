<?php
namespace app\models;
use Yii;
/**
 * @property integer $id
 * @property integer $order_id
 * @property integer $provider_stock_id
 * @property double $on_deposit
 */
class UnitContibution extends \yii\db\ActiveRecord{
    /**
     *  @inheritdoc
     */
    public static function tableName()
    {
        return 'unit_contibution';
   }
    /**
     *  @inheritdoc
     */

    public function rules()
    {
        return [
            [['id','order_id','provider_stock_id'],'integer'],
            [['on_deposit'],'double'],
                ];
    }

    /**
     *  @inheritdoc
     */
    public function attributelabels()
    {
        return [
            'id'=>'Идентификатор',
            'order_id'=>'Номер заказа',
            'provider_stock_id'=>'Идентификатор остатка',
            'on_deposit'=>'Сумма на лицевом счёте',
        ];
    }

    public function getOrder()
    {
        return $this->hasOne(Order::className(),['id'=>'order_id']);
    }

    public function getStock()
    {
        return $this->hasOne(ProviderStock::className(),['id'=>'provider_stock_id']);
    }
}
?>