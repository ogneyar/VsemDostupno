<?php

namespace app\modules\api\models\profile;

use Yii;
use yii\base\Model;

/**
 * This is the model class for user searching.
 *
 */
class UserSearching extends Model
{
    public $search;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['search'], 'required'],
            [['search'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'search' => 'Пользователь',
        ];
    }
}
