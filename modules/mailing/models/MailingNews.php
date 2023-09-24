<?php

namespace app\modules\mailing\models;

use Yii;
use app\models\Member;
use app\models\Partner;
use app\models\Provider;
use app\models\Candidate;
use app\modules\bots\api\Bot;

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
                        // $send_to[] = $rec->user->email;
                        if ($rec->user->tg_id) $send_to[] = $rec->user->tg_id;
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
                            // $send_to[] = $rec->user->email;
                            if ($rec->user->tg_id) $send_to[] = $rec->user->tg_id;
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
                            // $send_to[] = $rec->user->email;
                            if ($rec->user->tg_id) $send_to[] = $rec->user->tg_id;
                        }
                    }
                }
            }
        }
        
        // if ($data['for_candidates']) {
        //     if ($data['for_candidates'] == 'all') {
        //         $candidates_list = "all";
        //         $candidates = Candidate::find()->where(['block_mailing' => 0])->all();
        //         if ($candidates) {
        //             foreach ($candidates as $rec) {
        //                 // $send_to[] = $rec->email;
        //                 if ($rec->tg_id) $send_to[] = $rec->tg_id;
        //             }
        //         }
        //     } else {
        //         foreach ($data['for_candidates'] as $group) {
        //             $candidates_list .= $group . ",";
        //             $candidates = Candidate::find()->where(['group_id' => $group, 'block_mailing' => 0])->all();
        //             if ($candidates) {
        //                 foreach ($candidates as $rec) {
        //                     // $send_to[] = $rec->email;
        //                     if ($rec->tg_id) $send_to[] = $rec->tg_id;
        //                 }
        //             }
        //         }
        //     }
        // }
        
        if (count($send_to)) {
            
            $config = require(__DIR__ . '/../../../config/constants.php');
            $web = $config['WEB'];
            $token = $config['BOT_TOKEN'];
            $master = Yii::$app->params['masterChatId'];             
            $admin = $master; 
            // $admin = Yii::$app->params['adminChatId'];    
            $bot = new Bot($token);

            $count_exceptions = 0; // count exceptions - количество исключений
            
            // $mail = Yii::$app->mailer->compose()
            //     ->setFrom([Yii::$app->params['fromEmail'] => Yii::$app->params['name']])
            //     ->setTo($send_to)
            //     ->setSubject($data['subject'])
            //     ->setHtmlBody($data['body']);
            // if (count($data['files'])) {
            //     foreach ($data['files'] as $file) {
            //         $mail->attach($file['filepath'], ['fileName' => $file['filename']]);
            //     }
            // }                
            // try {
            //     $response = $mail->send();
            // }catch (Exception $e) {
            //     $count_exceptions++;
            // }

            $body = $data['body'];
            $body = str_replace("<br>","\r\n", $body);
            $body = str_replace("<br />","\r\n", $body);
            $body = str_replace("<p>","<pre>", $body);
            $body = str_replace("</p>","</pre>", $body);
            $body = str_replace("&nbsp;"," ", $body);

            
            if (count($data['files'])) {
                foreach ($data['files'] as $file) {
                    // $bot->sendMessage($send_to[$iter], $file['filepath']);
                    move_uploaded_file($file['filepath'], __DIR__ . "/../../../web/images/store/temp/".$file['filename']);

                    for ($iter = 0; $iter < count($send_to); $iter++) {
                        try {
                            // if ($send_to[$iter] == $master) {     
                                $bot->sendPhoto($send_to[$iter], "https://будь-здоров.рус/web/images/store/temp/".$file['filename']);
                            // }
                        }catch (Exception $e) {}
                    }

                    unlink(__DIR__ . "/../../../web/images/store/temp/".$file['filename']);
                    // $bot->sendMessage($send_to[$iter], $file['filename']);
                }
            }

            for ($iter = 0; $iter < count($send_to); $iter++) {
                try {
                    // if ($send_to[$iter] == $master) {                        
                        $bot->sendMessage(
                            $send_to[$iter], 
                            $data['subject'] . 
                                "\r\n\r\n" . 
                                $body, 
                            "html"
                        );
                    // }
                }catch (Exception $e) {
                    $count_exceptions++;
                }
            }


            // $bot->sendMessage($master, "пост не прост, ёпрст (Ы)");


           
            if ($count_exceptions) {
                // $mail_to_me = Yii::$app->mailer->compose()
                //     ->setFrom([Yii::$app->params['fromEmail'] => Yii::$app->params['name']])
                //     ->setTo("ya13th@mail.ru")
                //     ->setSubject($data['subject'])
                //     ->setHtmlBody("КОЛИЧЕСТВО ИСКЛЮЧЕНИЙ - " . $count_exceptions)
                //     ->send();
            
                $bot->sendMessage($master, "КОЛИЧЕСТВО ИСКЛЮЧЕНИЙ - " . $count_exceptions);
            }
            
            $mailing = new MailingNews();
            $mailing->for_members = $data['for_members'] ? 1 : 0; 
            $mailing->for_partners = $data['for_partners'] ? 1 : 0;
            $mailing->for_providers = $data['for_providers'] ? 1 : 0;
            $mailing->for_candidates = $candidates_list;
            $mailing->subject = $data['subject'];
            $mailing->message = $data['body'];
            $mailing->attachment = $data['files_names'];
            // $mailing->save();
            
        } 
        
    }


    
}
