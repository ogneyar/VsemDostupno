<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "candidate_group".
 *
 * @property integer $id
 * @property string $name
 *
 * @property Candidate[] $candidates
 */
class CandidateGroup extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'candidate_group';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор',
            'name' => 'Название',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCandidates()
    {
        return $this->hasMany(Candidate::className(), ['group_id' => 'id']);
    }
    
    public static function getGroupNameById($id)
    {
        $res = self::findOne($id);
        return $res->name;
    }
}
