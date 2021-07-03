<?php
namespace app\commands;

use Yii;
use yii\console\Controller;
use app\models\Member;
use app\models\Provider;
use app\models\Candidate;
use app\modules\mailing\models\MailingProduct;
use app\modules\mailing\models\MailingUser;

class MailingProductInfoController extends Controller
{
    public function actionIndex()
    {
        $mailings = MailingProduct::getForSend();
        if ($mailings) {
            foreach ($mailings as $res) {
                $send_to = [];
                if ($res->for_members == 1) {
                    $members = Member::find()->all();
                    if ($members) {
                        foreach ($members as $rec) {
                            if ($rec->user->disabled != 1) {
                                if (MailingUser::find()->where(['user_id' => $rec->user->id, 'mailing_category_id' => $res->mailing_category_id])->exists()) {
                                    $send_to[] = $rec->user->email;
                                }
                            }
                        }
                    }
                }
                if ($res->for_providers == 1) {
                    $providers = Provider::find()->all();
                    if ($providers) {
                        foreach ($providers as $rec) {
                            if ($rec->user->disabled != 1) {
                                if (!isset($rec->user->member)) {
                                    if (MailingUser::find()->where(['user_id' => $rec->user->id, 'mailing_category_id' => $res->mailing_category_id])->exists()) {
                                        $send_to[] = $rec->user->email;
                                    }
                                }
                            }
                        }
                    }
                }
                if (!empty($res->for_candidates)) {
                    if ($res->for_candidates == 'all') {
                        $candidates = Candidate::find()->where(['block_mailing' => 0])->all();
                        if ($candidates) {
                            foreach ($candidates as $rec) {
                                $send_to[] = $rec->email;
                            }
                        }
                    } else {
                        $groups = explode(",", $res->for_candidates);
                        foreach ($groups as $group) {
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
                    foreach ($send_to as $to) {
                        $mail = Yii::$app->mailer->compose()
                            ->setFrom([Yii::$app->params['fromEmail'] => Yii::$app->params['name']])
                            ->setTo($to)
                            ->setSubject($res['subject'])
                            ->setHtmlBody($res['message']);
                        
                        $mail->send();
                    }
                }
                $res->delete();
            }
        }
    }
}