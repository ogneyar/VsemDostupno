<?php

namespace app\modules\mailing\models;

use Yii;
use app\models\Member;
use app\models\Partner;
use app\models\Provider;
use app\models\Candidate;

/**
 * This is the model class for table "mailing_news".
 *
 * @property integer $id
 * @property integer $for_members
 * @property integer $for_partners
 * @property integer $for_providers
 * @property string $for_candidates
 * @property string $subject
 * @property string $message
 * @property string $attachment
 * @property string $sent_date
 */
class MailingNews extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName() 
    {
        return 'mailing_news';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['for_members', 'for_partners', 'for_providers'], 'integer'],
            [['subject', 'message'], 'required'],
            [['message'], 'string'],
            [['sent_date'], 'safe'],
            [['for_candidates'], 'string', 'max' => 50],
            [['subject', 'attachment'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор',
            'for_members' => 'Отправка для пользователей',
            'for_partners' => 'Отправка для партнёров',
            'for_providers' => 'Отправка для поставщиков',
            'for_candidates' => 'Отправка для кандидатов',
            'subject' => 'Тема',
            'message' => 'Сообщение',
            'attachment' => 'Приложенные файлы',
            'sent_date' => 'Время отправки',
        ];
    }
    
    public static function sendMailing($data)
    {
        $send_to = [];
        $candidates_list = "";
        if ($data['for_members']) {
            $members = Member::find()->all();
            if ($members) {
                foreach ($members as $rec) {
                    if ($rec->user->disabled != 1) {
                        $send_to[] = $rec->user->email;
                    }
                }
            }
        }

        if ($data['for_partners']) {
            $partners = Partner::find()->all();
            if ($partners) {
                foreach ($partners as $rec) {
                    if ($rec->user->disabled != 1) {
                        if (!isset($rec->user->member)) {
                            $send_to[] = $rec->user->email;
                        }
                    }
                }
            }
        }
        
        if ($data['for_providers']) {
            $providers = Provider::find()->all();
            if ($providers) {
                foreach ($providers as $rec) {
                    if ($rec->user->disabled != 1) {
                        if (!isset($rec->user->member)) {
                            $send_to[] = $rec->user->email;
                        }
                    }
                }
            }
        }
        
        if ($data['for_candidates']) {
            if ($data['for_candidates'] == 'all') {
                $candidates_list = "all";
                $candidates = Candidate::find()->where(['block_mailing' => 0])->all();
                if ($candidates) {
                    foreach ($candidates as $rec) {
                        $send_to[] = $rec->email;
                    }
                }
            } else {
                foreach ($data['for_candidates'] as $group) {
                    $candidates_list .= $group . ",";
                    $candidates = Candidate::find()->where(['group_id' => $group, 'block_mailing' => 0])->all();
                    if ($candidates) {
                        foreach ($candidates as $rec) {
                            $send_to[] = $rec->email;
                        }
                    }
                }
            }
        }
        
        if (count($send_to)) {
            $count_exceptions = 0; // count exceptions - количество исключений
            
            $mail = Yii::$app->mailer->compose()
                ->setFrom([Yii::$app->params['fromEmail'] => Yii::$app->params['name']])
                ->setTo($send_to)
                ->setSubject($data['subject'])
                ->setHtmlBody($data['body']);
            if (count($data['files'])) {
                foreach ($data['files'] as $file) {
                    $mail->attach($file['filepath'], ['fileName' => $file['filename']]);
                }
            }
                
            try {
                $response = $mail->send();
                // $response = 0;
            }catch (Exception $e) {
                $count_exceptions++;
            }
            
            if ($count_exceptions) {
                $mail_to_me = Yii::$app->mailer->compose()
                    ->setFrom([Yii::$app->params['fromEmail'] => Yii::$app->params['name']])
                    ->setTo("ya13th@mail.ru")
                    ->setSubject($data['subject'])
                    ->setHtmlBody("КОЛИЧЕСТВО ИСКЛЮЧЕНИЙ - " . $count_exceptions)
                    ->send();
            }else {
                $mail_to_me = Yii::$app->mailer->compose()
                    ->setFrom([Yii::$app->params['fromEmail'] => Yii::$app->params['name']])
                    ->setTo("ya13th@mail.ru")
                    ->setSubject($data['subject'])
                    ->setHtmlBody("send_to - " . implode("<br/>", $send_to))
                    ->send();
            }
            
            $mailing = new MailingNews();
            $mailing->for_members = $data['for_members'] ? 1 : 0;
            $mailing->for_partners = $data['for_partners'] ? 1 : 0;
            $mailing->for_providers = $data['for_providers'] ? 1 : 0;
            $mailing->for_candidates = $candidates_list;
            $mailing->subject = $data['subject'];
            $mailing->message = $data['body'];
            $mailing->attachment = $data['files_names'];
            $mailing->save();
            
        } 
        else {
            if ($data['for_partners']) {
                $mail_to_me = Yii::$app->mailer->compose()
                    ->setFrom([Yii::$app->params['fromEmail'] => Yii::$app->params['name']])
                    ->setTo("ya13th@mail.ru")
                    ->setSubject($data['subject'])
                    ->setHtmlBody("count(send_to) пуст")
                    ->send();
            }
        }
        
    }
}
