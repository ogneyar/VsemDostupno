<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "email_letters".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $subject
 * @property string $body 
 * @property boolean $is_read
 * @property string $date
 */
class EmailLetters extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'email_letters';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'subject', 'body'], 'required'],
            [['body'], 'string'],
            [['date', 'is_read'], 'safe'],
            [['subject'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор',
            'user_id' => 'Чьё письмо',
            'subject' => 'Тема',
            'body' => 'Содержание',
            'is_read' => 'Прочтено ли',
            'date' => 'Дата',
        ];
    }

    public static function send($user_id, $subject, $body)
    {
        $letters = new EmailLetters();
        $letters->user_id = $user_id;
        $letters->subject = $subject;
        $letters->body = $body;
        // $letters->is_read = 0;
        // $letters->date = date("Y-m-d");
        $letters->save();
    }

    

    public static function getLetters($user_data)
    {
        $user_id = $user_data['id'];
        $firstname = $user_data['firstname'];

        $letters = EmailLetters::findAll(['user_id' => $user_id]);
        
        if($letters) {
            echo '<p style="font-size: 28px;">'.$firstname.", вот все Ваши письма.</p>";
            foreach($letters as $letter) {
                echo $letter->id . " - ". $letter->date . "<br />";
            }
        }else {
            echo '<p style="font-size: 28px;">'.$firstname.", у Вас писем нет.</p>";
        }
        

        // $letters->subject;
        // $letters->body;
        // $letters->is_read = 0;
    }

}
