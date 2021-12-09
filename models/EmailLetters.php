<?php

namespace app\models;

use Yii;
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Member;
use app\models\Partner;
use app\models\Provider;
use app\models\Candidate;

use app\modules\mailing\models\MailingNews;


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
            echo '<p>Дата поступления:</p>';
            
            foreach($letters as $letter) {
                if (!$letter->is_read) echo "<b>";
                
                // echo '<a href="#" onclick="getLetter('.$letter->id.', `'.$letter->subject.'`,`'.$letter->body.'`);">'.$letter->date.'</a>';
                
                echo Html::a($letter->date, 'javascript:void(0)', [
                    'onclick' => 'getLetter('.$letter->id.', `'.$letter->subject.'`,`'.$letter->body.'`);'
                ]);

                echo Html::a('Удалить', 'javascript:void(0)', [
                    'onclick' => 'delLetter('.$letter->id.');',
                    'class' => 'btn btn-danger',
                    'style' => 'margin-left:40px;'
                ]);
                

                if (!$letter->is_read) echo "</b>";

                echo "<br /><br />";
            }
            
            echo '<div id="letters-details-container" class="letters-details" style="display: none;"></div>';
       

        }else {
            echo '<p style="font-size: 28px;">'.$firstname.", у Вас писем нет.</p>";
        }
        
    }


    public static function readLetter($id)
    {
        $letter = EmailLetters::findOne(['id' => $id]);
        $letter->is_read = true;
        $letter->save();
    }

    public static function deleteLetter($id)
    {
        $letter = EmailLetters::findOne(['id' => $id]);
        $letter->delete();
    }

    public static function sendMailingNews($data)
    {
        $send_to = [];
        $send_to_candidates = [];
        $candidates_list = "";
        if ($data['for_members']) {
            $members = Member::find()->all();
            if ($members) {
                foreach ($members as $rec) {
                    if ($rec->user->disabled != 1) {
                        $send_to[] = $rec->user->id;
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
                            $send_to[] = $rec->user->id;
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
                            $send_to[] = $rec->user->id;
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
                        $send_to_candidates[] = $rec->email;
                    }
                }
            } else {
                foreach ($data['for_candidates'] as $group) {
                    $candidates_list .= $group . ",";
                    $candidates = Candidate::find()->where(['group_id' => $group, 'block_mailing' => 0])->all();
                    if ($candidates) {
                        foreach ($candidates as $rec) {
                            $send_to_candidates[] = $rec->email;
                        }
                    }
                }
            }

            if (count($send_to_candidates)) {
                $count_exceptions = 0;
                $mail = Yii::$app->mailer->compose()
                    ->setFrom([Yii::$app->params['fromEmail'] => Yii::$app->params['name']])
                    ->setTo($send_to_candidates)
                    ->setSubject($data['subject'])
                    ->setHtmlBody($data['body']);
                if (count($data['files'])) {
                    foreach ($data['files'] as $file) {
                        $mail->attach($file['filepath'], ['fileName' => $file['filename']]);
                    }
                }
                    
                try {
                    $response = $mail->send();
                }catch (Exception $e) {
                    $count_exceptions++;
                }
                
                $mailing = new MailingNews();
                $mailing->for_members = 0; 
                $mailing->for_partners = 0;
                $mailing->for_providers = 0;
                $mailing->for_candidates = $candidates_list;
                $mailing->subject = $data['subject'];
                $mailing->message = $data['body'];
                $mailing->attachment = $data['files_names'];
                $mailing->save();
            }

        }

        
        if (count($send_to)) {
            foreach ($send_to as $to) {
                EmailLetters::send($to, $data['subject'], $data['body']);
            }

            if (count($data['files'])) {
                // foreach ($data['files'] as $file) {
                //     $mail->attach($file['filepath'], ['fileName' => $file['filename']]);
                // }
            }
              
            // $mailing->attachment = $data['files_names'];
            
        }

    }


    public static function sendMailingVote($data)
    {
        $send_to = [];
        if ($data['for_members']) {
            $members = Member::find()->all();
            if ($members) {
                foreach ($members as $rec) {
                    if ($rec->user->disabled != 1) {
                        $send_to[] = [
                            'id' => $rec->user->id,
                            'name' => $rec->user->respectedName,
                        ];
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
                            $send_to[] = [
                                'id' => $rec->user->id,
                                'name' => $rec->user->respectedName,
                            ];
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
                            $send_to[] = [
                                'id' => $rec->user->id,
                                'name' => $rec->user->respectedName,
                            ];
                        }
                    }
                }
            }
        }
        
        if (count($send_to)) {

            $subject = $data['subject'];

            foreach ($send_to as $to) {
                
                $id = $to['id'];

                $body = 'Уважаемый/ая ' . $to['name'] . ', просим Вас высказать своё мнение по работе Потребительского общества через участие в голосовании из <a href="' . Url::to('profile/login', true) . '">личного кабинета</a>.';
                $body .= '<br><br>';
                $body .= 'На это письмо отвечать не нужно, рассылка произведена автоматически.';

                if (count($data['files'])) {
                    // foreach ($data['files'] as $file) {
                        // $mail->attach($file['filepath'], ['fileName' => $file['filename']]);
                    // }
                }

                EmailLetters::send($id, $subject, $body);
               
            }
           
            // $mailing->attachment = $data['files_names'];

        }
    }


}
