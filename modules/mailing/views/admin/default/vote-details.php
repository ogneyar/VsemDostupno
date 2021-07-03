<?php
use yii\helpers\Html;
use app\helpers\UtilsHelper;
use app\modules\mailing\models\MailingVoteStat;

$t_text = empty($vote_text) ? " (все проголосовавшие)" : " (проголосовавшие '" . $vote_text . "')";

$this->title = $vote_model->subject . $t_text;
$this->params['breadcrumbs'][] = ['label' => 'Статистика голосования', 'url' => ['vote']];
$this->params['breadcrumbs'][] = UtilsHelper::cutStr($this->title, 100);
?>

<div class="mailing-vote-details">
    <h1><?= Html::encode($this->title) ?></h1>
    <br>
    <?php if (count($voted)): ?>
        <table>
            <?php foreach ($voted as $vote): ?>
                <tr>
                    <td><?= $vote->user->fullName; ?></td>
                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td><?= MailingVoteStat::getVoteText($vote->vote); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>