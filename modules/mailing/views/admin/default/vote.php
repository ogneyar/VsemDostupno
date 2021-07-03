<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\mailing\models\MailingVoteStat;

$this->title = 'Статистика голосования';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="mailing-vote">
    <h1><?= Html::encode($this->title) ?></h1>
    
    <?php if (count($votes)): ?>
        <?php foreach ($votes as $vote): ?>
            <p><strong>За <?= date("d.m.Y", strtotime($vote->sent_date)); ?> г.</strong></p>
            <p><strong>Тема: <span style="text-decoration: underline;"><?= $vote->subject; ?></span></strong></p>
            <table class="table table-bordered">
                <thead>
                    <th>Всего проголосовало</th>
                    <th>"За"</th>
                    <th>"Против"</th>
                    <th>"Воздержалось"</th>
                    <th>Действия</th>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <a href="<?= Url::to(['vote-details', 'id' => $vote->id]); ?>" style="text-decoration: underline;">
                                <?= MailingVoteStat::getTotalCountByVote($vote->id); ?>
                            </a>
                        </td>
                        <td>
                            <a href="<?= Url::to(['vote-details', 'id' => $vote->id, 'vote' => MailingVoteStat::VOTE_AGREE]); ?>" style="text-decoration: underline;">
                                <?= MailingVoteStat::getTotalByVote($vote->id, MailingVoteStat::VOTE_AGREE); ?>
                            </a>
                        </td>
                        <td>
                            <a href="<?= Url::to(['vote-details', 'id' => $vote->id, 'vote' => MailingVoteStat::VOTE_AGAINST]); ?>" style="text-decoration: underline;">
                                <?= MailingVoteStat::getTotalByVote($vote->id, MailingVoteStat::VOTE_AGAINST); ?>
                            </a>
                        </td>
                        <td>
                            <a href="<?= Url::to(['vote-details', 'id' => $vote->id, 'vote' => MailingVoteStat::VOTE_HOLD]); ?>" style="text-decoration: underline;">
                                <?= MailingVoteStat::getTotalByVote($vote->id, MailingVoteStat::VOTE_HOLD); ?>
                            </a>
                        </td>
                        <td>
                            <a href="<?= Url::to(['vote-delete', 'id' => $vote->id]); ?>" title="Удалить" data-pjax="0" data-method="post" data-confirm="Вы уверены что хотите удалить статистику?"><span class="glyphicon glyphicon-trash"></span></a>
                        </td>
                    </tr>
                </tbody>
            </table>
        <?php endforeach; ?>
    <?php endif; ?>
</div>