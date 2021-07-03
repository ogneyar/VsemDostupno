<?php

namespace app\modules\site\models;

use Yii;
use app\models;

class User extends \yii\base\BaseObject implements \yii\web\IdentityInterface
{
    public $entity;
    public $id;
    public $role;
    public $username;
    public $password;
    public $authKey;
    public $accessToken;

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        $user = models\User::findOne($id);

        return $user ? self::createUserFromEntity($user) : null;
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $user = models\User::findOne(['access_token' => $token]);

        return $user ? self::createUserFromEntity($user) : null;
    }

    /**
     * Finds user by username
     *
     * @param  string      $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        $user = models\User::findOne(['email' => $username]);

        return $user ? self::createUserFromEntity($user) : null;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }

    /**
     * Validates password
     *
     * @param  string  $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        $entity = new models\User($this->entity);
        $entity->password = $password;
        $entity->updateAuthData();

        return YII_ENV_DEV || $this->password === $entity->password;
    }

    public static function handleAfterLogin()
    {
        $entity = Yii::$app->user->identity->entity;
        $time = $time = new \DateTime('now', new \DateTimeZone(Yii::$app->params['timezone']));
        $entity->logged_in_at = $time->format('Y-m-d H:i:s');
        $entity->logged_in_ip = Yii::$app->getRequest()->getUserIP();
        $entity->scenario = 'user_login';
        $entity->save();
        
    }

    protected static function createUserFromEntity($entity)
    {
        $user = new static();

        $user->entity = $entity;
        $user->id = $entity->id;
        $user->role = $entity->role;
        $user->username = $entity->email;
        $user->password = $entity->password;
        $user->authKey = $entity->auth_key;
        $user->accessToken = $entity->access_token;

        return $user;
    }
}
