<?php

namespace app\modules\mailing\controllers\admin;

use Yii;
use app\models\CandidateGroup;
use yii\web\Response;
use yii\data\ActiveDataProvider;
use app\modules\mailing\models\MailingNews;
use app\modules\mailing\models\MailingVote;
use app\modules\mailing\models\MailingVoteStat;
use app\modules\mailing\models\MailingProduct;
use app\modules\mailing\models\MailingMessage;

use app\models\EmailLetters;

use app\models\User;

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
        if (isset($_POST['message'])) {
            $data = [];
            $data['body'] = $_POST['message'];
            $category = $_POST['category'];
            $data['for_members'] = isset($_POST['members']) ? true : false; 
            $data['for_partners'] = isset($_POST['partners']) ? true : false; 
            $data['for_providers'] = isset($_POST['providers']) ? true : false; 
            $data['for_candidates'] = isset($_POST['candidates']) ? true : false;
            
            if ($data['for_candidates']) {
                if ($_POST['candidates-all'] == '1') {
                    $data['for_candidates'] = 'all';
                } else {
                    $data['for_candidates'] = [];
                    foreach ($_POST['candidates'] as $k => $val) {
                        $data['for_candidates'][] = $k;
                    }
                }
            }
            
            $data['files'] = [];
            $data['files_names'] = '';
            foreach ($_FILES['attachment']['tmp_name'] as $k => $filepath) {
                if (!empty($filepath) && $_FILES['attachment']['error'][$k] == 0) {
                    $data['files'][$k] = ['filepath' => $filepath, 'filename' => $_FILES['attachment']['name'][$k]];
                    $data['files_names'] .= $_FILES['attachment']['name'][$k] . ",";
                }
            }
            
            if ($category == 1) {
                $data['subject'] = htmlspecialchars($_POST['subject']);
                // MailingNews::sendMailing($data);

                EmailLetters::sendMailingNews($data);
            } elseif ($category == 5) {
                $data['subject'] = htmlspecialchars($_POST['subject_vote']);
                // MailingVote::sendMailing($data); 

                EmailLetters::sendMailingVote($data);
            }
        }
        
        return $this->render('index', [
            'groups' => CandidateGroup::find()->all(),
        ]);
    }
    
    public function actionVote()
    {
        $votes = MailingVote::find()->orderBy('sent_date DESC')->all();
        
        return $this->render('vote', [
            'votes' => $votes,
        ]);
    }
    
    public function actionVoteDetails($id, $vote = "")
    {
        $vote_model = MailingVote::findOne($id);
        $vote_text = !empty($vote) ? MailingVoteStat::getVoteText($vote) : "";
        
        $voted = MailingVoteStat::getVotedUsers($id, $vote);
        
        return $this->render('vote-details', [
            'vote_model' => $vote_model,
            'vote_text' => $vote_text,
            'voted' => $voted,
        ]);
    }
    
    public function actionVoteDelete($id)
    {
        $vote_model = MailingVote::findOne($id);
        $vote_model->delete();
        return $this->redirect(['vote']);
    }
    
    public function actionProduct()
    {
        $model = new MailingProduct;
        $model->product_id = $_POST['product-id'];
        $model->for_members = isset($_POST['members']) ? 1 : 0;
        $model->for_providers = isset($_POST['providers']) ? 1 : 0;
        $for_candidates = isset($_POST['candidates']) ? 1 : '';
        if ($for_candidates == 1) {
            if ($_POST['candidates-all'] == '1') {
                $for_candidates = 'all';
            } else {
                $for_candidates = "";
                foreach ($_POST['candidates'] as $k => $val) {
                    $for_candidates .= $k . ",";
                }
            }
        }
        $model->for_candidates = $for_candidates;
        $model->mailing_category_id = $_POST['category'];
        $model->subject = $_POST['subject'];
        $model->message = $_POST['message'];
        
        if ($model->save()) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'success' => true,
            ];
        } else {
            print_r($model);
        }
    }
    
    public function actionMessage()
    {
        if (isset($_POST['user_id'])) {
            $user = User::findOne($_POST['user_id']);
            $model = MailingMessage::findOne($_POST['id']);
            $mail = Yii::$app->mailer->compose()
                ->setFrom([Yii::$app->params['fromEmail'] => Yii::$app->params['name']])
                ->setTo($user->email)
                ->setSubject($_POST['subject'])
                ->setHtmlBody($_POST['message']);
            
            $mail->send();
            $model->answered = 1;
            $model->save();
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'success' => true,
            ];
        }
        
        $dataProvider = new ActiveDataProvider([
            'query' => MailingMessage::find(),
        ]);
        
        return $this->render('message', [
            'dataProvider' => $dataProvider,
        ]);
    }
    
    public function actionMessageDelete($id)
    {
        $model = MailingMessage::findOne($id);
        $model->delete();
        return $this->redirect(['message']);
    }
    
    public function actionMessageView($id)
    {
        $model = MailingMessage::findOne($id);
        
        return $this->render('message-view', [
            'model' => $model,
        ]);
    } 
}