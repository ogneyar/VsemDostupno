<?php

use kartik\helpers\Html;
use yii\bootstrap\Modal;
use app\modules\mailing\models\MailingVoteStat;

$this->title = 'Голосования';
$this->params['breadcrumbs'] = [$this->title];

?>

<?= Html::pageHeader(Html::encode($this->title)) ?>

<div class="vote-index">
    <?php if (count($votes)): ?>
        <?php foreach ($votes as $vote): ?>
            <div>
                <div class="form-group">
                    <h3><?= $vote->subject; ?></h3>
                </div>
                <div class="form-group">
                    <?= Html::radio('vote-' . $vote->id, true, ['value' => 'agree', 'label' => 'За', 'data-label' => 'За']); ?>&nbsp;&nbsp;&nbsp;&nbsp;
                    <?= Html::radio('vote-' . $vote->id, false, ['value' => 'against', 'label' => 'Против', 'data-label' => 'Против']); ?>&nbsp;&nbsp;&nbsp;&nbsp;
                    <?= Html::radio('vote-' . $vote->id, false, ['value' => 'hold', 'label' => 'Воздерживаюсь', 'data-label' => 'Воздерживаюсь']); ?>
                </div>
                <div class="form-group">
                    <input type="hidden" id="vote-subject-<?= $vote->id; ?>" value="<?= $vote->subject; ?>">
                    <?= Html::button('Проголосовать', ['class' => 'btn btn-success', 'data-id' => $vote->id, 'onclick' => 'sendVote(this);']); ?>
                </div>
                <hr>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <?php if (count($voted)): ?>
        <h4>Ваши предыдущие голоса</h4>
        <hr>
        <?php foreach ($voted as $vote): ?>
            <h5><?= $vote->subject; ?></h5>
            <strong><?= MailingVoteStat::getVoteByUser(Yii::$app->user->id, $vote->id); ?></strong>
            <hr>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php Modal::begin([
    'id' => 'vote-sending-modal',
    'options' => ['tabindex' => false,],
    'header' => '<h4>' . 'Подтверждение' . '</h4>',
    'footer' => '<a class="btn btn-default" data-dismiss="modal" aria-hidden="true" id="vote-sending-cancel-btn">' . 'Нет' . '</a>
                 <button id="vote-sending-btn" class="btn btn-success" type="button">' . 'Да' . '</button>',
]); ?>

    <p>По вопросу <strong>"<span id="vote-subject"></span>"</strong> Ваш голос: <strong>"<span id="vote-result"></span>"</strong></p>
    <p>Отправить Ваш голос?</p>

<?php Modal::end(); ?>

<?php Modal::begin([
    'id' => 'vote-sending-thanx-modal',
    'options' => ['tabindex' => false,],
]); ?>

    <p>Благодарим Вас за активность и высказанное мнение</p>

<?php Modal::end(); ?>