<?php

namespace app\modules\admin\models;

use Yii;
use yii\base\Model;

/**
 * OrderForm is the model behind the create/update form.
 */
class OrderForm extends Model
{
    public $user_id;
    public $product_list;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['user_id', 'product_list'], 'required'],
            [['user_id'], 'integer'],
            [['product_list'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'user_id' => 'Идентификатор пользователя',
            'product_list' => 'Список товаров',
        ];
    }
}
