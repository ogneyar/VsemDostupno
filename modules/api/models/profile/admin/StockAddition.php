<?php

namespace app\modules\api\models\profile\admin;

use Yii;
use yii\base\Model;

/**
 * This is the model class for stock addition.
 *
 */
class StockAddition extends Model
{
    public $product_id;
    public $tare;
    public $weight;
    public $measurement;
    public $count;
    public $summ;
    public $deposit;
    public $comment;
    public $product_name;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tare', 'weight', 'measurement', 'count', 'summ'], 'required'],
            [['product_id', 'count', 'deposit', 'summ'], 'integer'],
            [['weight'], 'double'],
            [['tare', 'measurement', 'comment', 'product_name'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'product_id' => 'Идентификатор товара',
            'tare' => 'Тара',
            'weight' => 'Масса',
            'measurement' => 'Единица измерения',
            'count' => 'Количество',
            'summ' => 'Сумма за единицу товара',
            'deposit' => 'Зачислять на лицевой счёт',
            'comment' => 'Комментарий'
        ];
    }
}