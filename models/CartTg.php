<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\db\Query;
use kgladkiy\behaviors\NestedSetBehavior;
use kgladkiy\behaviors\NestedSetQuery;

/**
 * This is the model class for table "cart_tg".
 *
 * @property integer $id
 * @property integer $tg_id
 * @property integer $product_id
 * @property integer $product_feature_id
 * @property integer $quantity
 *
 */
class CartTg extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'cart_tg';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tg_id', 'product_id', 'product_feature_id', 'quantity'], 'integer'],
            [['tg_id', 'product_id', 'product_feature_id'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор',
            'tg_id' => 'Идентификатор телеграм',
            'product_id' => 'Номер товара',
            'product_feature_id' => 'Номер особенности товара',
            'quantity' => 'Количество'
        ];
    }


}
