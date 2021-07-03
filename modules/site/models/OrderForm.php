<?php

namespace app\modules\site\models;

use Yii;
use yii\base\Model;
use app\models\User as User_o;
use app\models\Cart;
use app\models\Member;

/**
 * OrderForm is the model behind the order form.
 */
class OrderForm extends Model
{
    public $partner;
    public $email;
    public $phone;
    public $firstname;
    public $lastname;
    public $patronymic;
    public $address;
    public $comment;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        $required = ['partner', 'email', 'phone', 'firstname', 'lastname', 'patronymic'];

        foreach ($required as $index => $name) {
            if (!$this->canFilled($name)) {
                unset($required[$index]);
            }
        }

        return [
            [$required, 'required'],
            [['partner'], 'integer'],
            [['phone', 'firstname', 'lastname', 'patronymic'], 'string', 'max' => 255],
            [['email'], 'email'],
            [['address', 'comment'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'partner' => 'Партнер',
            'email' => 'Емайл',
            'phone' => 'Телефон',
            'firstname' => 'Имя',
            'lastname' => 'Фамилия',
            'patronymic' => 'Отчество',
            'address' => 'Адрес доставки',
            'comment' => 'Комментарий',
        ];
    }

    public function load($data, $formName = null)
    {
        $enableCart = false;
        if (parent::load($data, $formName)) {
            $entity = !Yii::$app->user->isGuest ? Yii::$app->user->identity->entity : null;

            if ($entity && Yii::$app->user->identity->role == User_o::ROLE_PROVIDER) {
                $member = Member::find()->where(['user_id' => Yii::$app->user->identity->id])->one();
                if ($member) {
                    $enableCart = true;
                }
            }
            if ($entity && in_array($entity->role, [User_o::ROLE_PARTNER, User_o::ROLE_MEMBER])) {
                $enableCart = true;
            }
            
            if ($enableCart) {
                $this->firstname = $entity->firstname;
                $this->lastname = $entity->lastname;
                $this->patronymic = $entity->patronymic;
                $this->phone = $entity->phone;
                $this->email = $entity->email;
            }

            if ($entity && in_array($entity->role, [User_o::ROLE_MEMBER])) {
                $this->partner = $entity->member->partner_id;
            }
            if ($entity && Yii::$app->user->identity->role == User_o::ROLE_PROVIDER) {
                $member = Member::find()->where(['user_id' => Yii::$app->user->identity->id])->one();
                if ($member) {
                    $this->partner = $member->partner_id;
                }
            }

            return true;
        }

        return false;
    }

    public function canFilled($name)
    {
        $attributes = [
            'partner' => Yii::$app->user->isGuest,
            'email' => Yii::$app->user->isGuest,
            'phone' => Yii::$app->user->isGuest,
            'firstname' => Yii::$app->user->isGuest,
            'lastname' => Yii::$app->user->isGuest,
            'patronymic' => Yii::$app->user->isGuest,
            'address' => Yii::$app->user->isGuest || !in_array(Yii::$app->user->identity->role, [User_o::ROLE_PARTNER]),
        ];

        return isset($attributes[$name]) ? $attributes[$name] : true;
    }
}
