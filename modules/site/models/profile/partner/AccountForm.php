<?php

namespace app\modules\site\models\profile\partner;

use Yii;
use yii\base\Model;
use app\models\Account;
use app\models\User;

/**
 * AccountForm is the model behind the account form.
 */
class AccountForm extends Model
{
    public $user_id;
    public $account_type;
    public $amount;
    public $message;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['user_id', 'account_type', 'amount', 'message'], 'required'],
            [['user_id'], 'integer'],
            [['message'], 'string'],
            [['account_type'], 'in', 'range' => [
                Account::TYPE_DEPOSIT,
            ]],
            [['amount'], 'number'],
            ['amount', 'validateAmount'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'user_id' => 'Идентификатор пользователя',
            'account_type' => 'Счет',
            'amount' => 'Сумма',
            'message' => 'Сообщение',
        ];
    }

    public function validateAmount($attribute, $params)
    {
        if (!$this->hasErrors()) {
            if (preg_match('/\.\d\d\d+$/', $this->amount)) {
                $this->addError($attribute, 'Точность «Суммы» должна быть «0.01».');
            }

            if (!$this->amount) {
                $this->addError($attribute, '«Сумма» не должна быть равна «0.00».');
            }

            $user = User::findOne($this->user_id);
            if (!$user) {
                $this->addError($attribute, 'Нет доступа к счетам пользователя.');
            } elseif ($this->amount < 0) {
                $account = $user->getAccount($this->account_type);
                if ($account->total + $this->amount < 0) {
                    $this->addError($attribute, '«Сумма» списания превышает значение «Счета».');
                }
            }
        }
    }
}
