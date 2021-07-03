<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "email".
 *
 * @property integer $id
 * @property string $name
 * @property string $subject
 * @property string $body
 */
class Email extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'email';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'subject', 'body'], 'required'],
            [['body'], 'string'],
            [['name', 'subject'], 'string', 'max' => 255],
            [['name'], 'unique']
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
            'subject' => 'Тема',
            'body' => 'Содержание',
        ];
    }

    public static function send($name, $to, $params = [])
    {
        /*if (YII_ENV_DEV) {
            $to = Yii::$app->params['devEmail'];
        }*/

        $email = self::findOne(['name' => $name]);

        if ($email) {
            if ($params) {
                $patterns = [];
                $replacements = [];

                foreach ($params as $pattern => $replacement) {
                    $patterns[] = '/{{%' . $pattern . '}}/';
                    $replacements[] = $replacement;
                }

                $email->subject = preg_replace($patterns, $replacements, $email->subject);
                $email->body = preg_replace($patterns, $replacements, $email->body);
            }

            $email->subject = preg_replace('/{{%.*?}}/', '', $email->subject);
            $email->body = preg_replace('/{{%.*?}}/', '', $email->body);

            $toEmails = is_array($to) ? $to : [$to];
            foreach ($toEmails as $toEmail) {
                    Yii::$app->mailer->compose()
                        ->setFrom([Yii::$app->params['fromEmail'] => Yii::$app->params['name']])
                        ->setTo($toEmail)
                        ->setSubject($email->subject)
                        ->setHtmlBody($email->body)
                        ->send();
            }
        }
    }
}
