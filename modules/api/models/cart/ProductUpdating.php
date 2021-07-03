<?php

namespace app\modules\api\models\cart;

use Yii;
use yii\base\Model;
use app\models\Product;

/**
 * This is the model class for product updating.
 *
 */
class ProductUpdating extends Model
{
    public $id;
    public $quantity;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'quantity'], 'required'],
            [['id'], 'integer'],
            [['id'], 'validateId'],
            [['quantity'], 'integer', 'min' => 0],
        ];
    }

    public function validateId($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $product = Product::find()
                ->joinWith('productFeatures')
                ->where('product_feature.id = :id AND visibility != 0', [':id' => $this->id])
                ->one();

            if (!$product) {
                $this->addError($attribute, 'Указанный товар не найден.');
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор товара',
            'quantity' => 'Количество',
        ];
    }
}
