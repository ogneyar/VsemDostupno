<?php

namespace app\modules\mailing\controllers\site;

use Yii;
use yii\web\Response;
use app\modules\mailing\models\MailingUser;
use app\modules\mailing\models\MailingVote;
use app\modules\mailing\models\MailingVoteStat;
use app\modules\mailing\models\MailingMessage;
use app\models\NoticeEmail;

/**
 * Default controller for the `mailing` module
 */
class DefaultController extends BaseController
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
    
    public function actionSettings()
    {
        $cats = $_POST['m_category'];
        $user_id = $_POST['user_id'];
        $model = MailingUser::find()->where(['user_id' => $user_id])->all();
        if ($model) {
            foreach ($model as $val) {
                $val->delete();
            }
        }
        
        foreach ($cats as $cat_id => $val) {
            $model = new MailingUser;
            $model->user_id = $user_id;
            $model->mailing_category_id = $cat_id;
            $model->save();
        }
        
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'success' => true,
        ];
    }
    
    public function actionVote()
    {
        if (isset($_POST['vote_id'])) {
            $stat = new MailingVoteStat;
            $stat->mailing_vote_id = $_POST['vote_id'];
            $stat->user_id = Yii::$app->user->id;
            $stat->vote = $_POST['vote_val'];
            if ($stat->save()) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'success' => true,
                ];
            }
        }
        
        $votes = [];
        if (MailingVote::existsActiveVote(Yii::$app->user->id) == 1) {
            $votes = MailingVote::getActiveVotes(Yii::$app->user->id);
        }
        
        $voted = MailingVote::getVoted(Yii::$app->user->id);
        
        return $this->render('vote', [
            'votes' => $votes,
            'voted' => $voted,
        ]);
    }
    
    public function actionMessage()
    {
        if (isset($_POST['category'])) {
            $model = new MailingMessage;
            $model->category = $_POST['category'];
            $model->user_id = Yii::$app->user->id;
            $model->subject = $_POST['subject'];
            $model->message = $_POST['message'];
            
            if ($model->save()) {
                $body = "Пользователь " . $model->user->fullName . " (" . $model->user->email . ", " . $model->user->phone . ") оставил сообщение:";
                $body .= "<br><br>";
                $body .= $model->getCategoryText($model->category);
                $body .= "<br>";
                $body .= $model->message;
                if ($emails = NoticeEmail::getEmails()) {
                    foreach ($emails as $email) {
                        $mail = Yii::$app->mailer->compose()
                            ->setFrom([Yii::$app->params['fromEmail'] => Yii::$app->params['name']])
                            ->setTo($email)
                            ->setSubject("Сообщение от пользователя")
                            ->setHtmlBody($body);
                        
                        $mail->send();
                    }
                }
                
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'success' => true,
                ];
            }
        }
        
        return $this->render('message', [
            
        ]);
    }
}