<?php

namespace app\modules\site\models\account;

use Yii;
use yii\base\Model;

/**
 * AccountTransferForm is the model behind the transfer form.
 */
class TransferForm extends Model
{
    public $to_user_id;
    public $amount;
    public $message;

    /*
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['to_user_id', 'amount', 'message'], 'required'],
            [['message'], 'string'],
            [['to_user_id'], 'integer'],
            [['amount'], 'number'],
            ['amount', 'validateAmount'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'to_user_id' => 'Пользователь',
            'message' => 'Сообщение',
            'amount' => 'Сумма',
        ];
    }

    public function validateAmount($attribute, $params)
    {
        if (!$this->hasErrors()) {
            if (preg_match('/\.\d\d\d+$/', $this->amount)) {
                $this->addError($attribute, 'Точность «Суммы» должна быть «0.01».');
            }

            $user = Yii::$app->user->identity->entity;
            if (!$user) {
                $this->addError($attribute, 'Нет доступа к счетам пользователя.');
            } else {
                $account = $user->deposit;
                if ($account->total < $this->amount) {
                    $this->addError($attribute, '«Сумма» превышает значение «Счета».');
                }
            }
        }
    }
}
