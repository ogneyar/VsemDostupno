<?php

namespace app\modules\mailing\models;

use Yii;
use yii\helpers\Url;
use app\helpers\UtilsHelper;
use app\models\Member;
use app\models\Partner;
use app\models\Provider;
use app\models\User;
use app\modules\mailing\models\MailingVoteStat;
use app\modules\bots\api\Bot;

/**
 * This is the model class for table "mailing_vote".
 *
 * @property integer $id
 * @property integer $for_members
 * @property integer $for_partners
 * @property integer $for_providers
 * @property string $subject
 * @property string $attachment
 * @property string $sent_date
 */
class MailingVote extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'mailing_vote';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['for_members', 'for_partners', 'for_providers'], 'integer'],
            [['subject'], 'required'],
            [['sent_date'], 'safe'],
            [['attachment'], 'string', 'max' => 255],
            [['subject'], 'string'],
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
            'subject' => 'Тема',
            'attachment' => 'Приложенные файлы',
            'sent_date' => 'Время отправки',
        ];
    }
    
    public static function sendMailing($data)
    {
        $send_to = [];
        if ($data['for_members']) {
            $members = Member::find()->all();
            if ($members) {
                foreach ($members as $rec) {
                    if ($rec->user->disabled != 1) {
                        // $send_to[] = [
                        //     'email' => $rec->user->email,
                        //     'name' => $rec->user->respectedName,
                        // ];                            
                        if ($rec->user->tg_id) {                                
                            $send_to[] = [
                                'tg_id' => $rec->user->tg_id,
                                'name' => $rec->user->respectedName,
                            ];                            
                        }
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
                            // $send_to[] = [
                            //     'email' => $rec->user->email,
                            //     'name' => $rec->user->respectedName,
                            // ];                            
                            if ($rec->user->tg_id) {                                
                                $send_to[] = [
                                    'tg_id' => $rec->user->tg_id,
                                    'name' => $rec->user->respectedName,
                                ];                            
                            }
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
                            // $send_to[] = [
                            //     'email' => $rec->user->email,
                            //     'name' => $rec->user->respectedName,
                            // ];
                            if ($rec->user->tg_id) {                                
                                $send_to[] = [
                                    'tg_id' => $rec->user->tg_id,
                                    'name' => $rec->user->respectedName,
                                ];                            
                            }
                        }
                    }
                }
            }
        } 
        
        if (count($send_to)) {
            
            $config = require(__DIR__ . '/../../../config/constants.php');
            $web = $config['WEB'];
            $token = $config['BOT_TOKEN'];
            $master = Yii::$app->params['masterChatId'];             
            $admin = $master; 
            // $admin = Yii::$app->params['adminChatId'];    
            $bot = new Bot($token);

            $count_exceptions = 0; // count exceptions - количество исключений

            // foreach ($send_to as $to) {
            //     $body = 'Уважаемый/ая ' . $to['name'] . ', просим Вас высказать своё мнение по работе Потребительского общества через участие в голосовании из <a href="' . Url::to('profile/login', true) . '">личного кабинета</a>.';
            //     $body .= '<br><br>';
            //     $body .= 'На это письмо отвечать не нужно, рассылка произведена автоматически.';
            //     $mail = Yii::$app->mailer->compose()
            //         ->setFrom([Yii::$app->params['fromEmail'] => Yii::$app->params['name']])
            //         ->setTo($to['email'])
            //         ->setSubject(UtilsHelper::cutStr($data['subject'], 150))
            //         ->setHtmlBody($body);
            //     if (count($data['files'])) {
            //         foreach ($data['files'] as $file) {
            //             $mail->attach($file['filepath'], ['fileName' => $file['filename']]);
            //         }
            //     }
            //     $mail->send();
            // }

            
            $mailing = new MailingVote();
            $mailing->for_members = $data['for_members'] ? 1 : 0;
            $mailing->for_partners = $data['for_partners'] ? 1 : 0;
            $mailing->for_providers = $data['for_providers'] ? 1 : 0;
            $mailing->subject = $data['subject'];
            $mailing->attachment = $data['files_names'];
            $mailing->save();

            // $bot->sendMessage($master, "mailing->id - " .$mailing->id);
            
            if ($mailing->id) {

                if (count($data['files'])) {
                    foreach ($data['files'] as $file) {
                        
                        move_uploaded_file($file['filepath'], __DIR__ . "/../../../web/images/store/temp/".$file['filename']);

                        foreach ($send_to as $to) {
                            try {
                                // if ($to['tg_id'] == $master) {     
                                    $bot->sendPhoto($to['tg_id'], "https://будь-здоров.рус/web/images/store/temp/".$file['filename']);
                                // }
                            }catch (Exception $e) {}
                        }

                        unlink(__DIR__ . "/../../../web/images/store/temp/".$file['filename']);
                        
                    }
                }
                
                $InlineKeyboardMarkup = [
                    'inline_keyboard' => [[
                        [
                            'text' => 'За',
                            'callback_data' => 'vote_agree'
                        ],
                        [
                            'text' => 'Против',
                            'callback_data' => 'vote_against'
                        ],
                        [
                            'text' => 'Воздержался',
                            'callback_data' => 'vote_hold'
                        ]
                    ]]
                ];

                $response = $mailing->id . "\r\nГолосование\r\n\r\n" . $data['subject'];

                foreach ($send_to as $to) {
                    try {
                        // if ($to['tg_id'] == $master) {                        
                            $bot->sendMessage(
                                $to['tg_id'], 
                                $response,
                                null,
                                $InlineKeyboardMarkup
                            );
                        // }
                    }catch (Exception $e) {
                        $count_exceptions++;
                    }
                }
                
                if ($count_exceptions) {
                    $bot->sendMessage($master, "КОЛИЧЕСТВО ИСКЛЮЧЕНИЙ - " . $count_exceptions);
                }
            }
            
        }
    }
    
     /**
     * @return \yii\db\ActiveQuery
     */
    public function getMailingVoteStats()
    {
        return $this->hasMany(MailingVoteStat::className(), ['mailing_vote_id' => 'id']);
    }
    
    public static function existsActiveVote($user_id)
    {
        $user = User::findOne($user_id);
        if ($user) {
            if ($user->role == User::ROLE_MEMBER) {
                $votes = self::find()->where(['for_members' => 1])->all();
                if ($votes) {
                    foreach ($votes as $vote) {
                        if (!MailingVoteStat::find()->where(['mailing_vote_id' => $vote->id, 'user_id' => $user->id])->exists()) {
                            return 1;
                        }
                    }
                }
            }
            if ($user->role == User::ROLE_PROVIDER) {
                if (!isset($user->member)) {
                    $votes = self::find()->where(['for_providers' => 1])->all();
                    if ($votes) {
                        foreach ($votes as $vote) {
                            if (!MailingVoteStat::find()->where(['mailing_vote_id' => $vote->id, 'user_id' => $user->id])->exists()) {
                                return 1;
                            }
                        }
                    }
                } else {
                    $votes = self::find()->where(['for_members' => 1])->all();
                    if ($votes) {
                        foreach ($votes as $vote) {
                            if (!MailingVoteStat::find()->where(['mailing_vote_id' => $vote->id, 'user_id' => $user->id])->exists()) {
                                return 1;
                            }
                        }
                    }
                }
            }
        }
        
        return 0;
    }
    
    public static function getActiveVotes($user_id)
    {
        $user = User::findOne($user_id);
        if ($user->role == User::ROLE_MEMBER) {
            $for_user = 'for_members';
        }
        if ($user->role == User::ROLE_PROVIDER) {
            if (!isset($user->member)) {
                $for_user = 'for_providers';
            } else {
                $for_user = 'for_members';
            }
        }
        
        $res = [];
        $votes = self::find()->where([$for_user => 1])->all();
        if ($votes) {
            foreach ($votes as $vote) {
                if (!MailingVoteStat::find()->where(['mailing_vote_id' => $vote->id, 'user_id' => $user->id])->exists()) {
                    $res[] = $vote;
                }
            }
        }
        return $res;
    }
    
    public static function getVoted($user_id)
    {
        $user = User::findOne($user_id);
        if ($user->role == User::ROLE_MEMBER) {
            $for_user = 'for_members';
        }
        if ($user->role == User::ROLE_PROVIDER) {
            if (!isset($user->member)) {
                $for_user = 'for_providers';
            } else {
                $for_user = 'for_members';
            }
        }
        
        $res = [];
        $votes = self::find()->where([$for_user => 1])->orderBy('sent_date DESC')->all();
        if ($votes) {
            foreach ($votes as $vote) {
                if (MailingVoteStat::find()->where(['mailing_vote_id' => $vote->id, 'user_id' => $user->id])->exists()) {
                    $res[] = $vote;
                }
            }
        }
        return $res;
    }
}
