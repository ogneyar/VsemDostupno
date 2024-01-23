<?php

namespace app\models;

use Yii;


/**
 * This is the model class for table "archive".
 *
 * @property integer $id
 * @property datetime $date
 * @property string $operation
 * @property string $account_name
 * @property double $amount
 * @property string $reason
 * @property string $fio
 * @property integer $number
 */
class Archive extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'archive';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date', 'operation','account_name', 'amount','reason', 'fio','number'], 'required'],
            [['amount', 'number'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор',
            'date' => 'Дата',
            'operation' => 'Операция',
            'account_name' => 'Наименование счёта',
            'amount' => 'Сумма',
            'reason' => 'Основание / Причина',
            'fio' => 'Ф.И.О.',
            'number' => 'Номер регистрации',
        ];
    }
    
    
    
    
}

