<?php

use yii\helpers\Html;
use yii\helpers\Url;
use mihaildev\ckeditor\CKEditor;

$this->title = 'Сообщение от ' . $model->user->fullName;
$this->params['breadcrumbs'][] = ['label' => 'Вопросы, жалобы, предложения', 'url' => ['message']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="mailing-message-view">
    <h1><?= Html::encode($this->title) ?></h1>
    <h3><?= $model->categoryTextRaw; ?></h3>
    <h4><?= $model->subject; ?></h4>
    <p><?= $model->message; ?></p>
    
    <p>
        <?= Html::a('Ответить', 'javascript:void(0)', ['class' => 'btn btn-success', 'id' => 'answer-message-btn']) ?>
        <?= Html::a('Удалить', Url::to(['message-delete', 'id' => $model->id]), ['class' => 'btn btn-danger']) ?>
    </p>
    
    <div id="answer-message-container" style="display: none;">
        <input type="hidden" id="answer-message-user-id" value="<?= $model->user_id; ?>">
        <input type="hidden" id="answer-message-id" value="<?= $model->id; ?>">
        <div class="form-group">
            <label for="subject">Тема</label>
            <?= Html::textInput('answer-subject', 'Re: ' . $model->subject, ['class' => 'form-control', 'id' => 'answer-subject']); ?>
        </div>
        <div class="form-group">
            <label for="subject">Сообщение</label>
            <?= CKEditor::widget([
                'name' => 'answer-message',
                'id' => 'answer-message',
                'value' => $model->user->respectedName . ', ' . date("d.m.Y", strtotime($model->sent_date)) . " Вы писали:<br>\"" . $model->message . "\"<br>",
                'editorOptions' => [
                    'preset' => 'standart',
                    'inline' => false,
                ]
            ]);?>
        </div>
        <div class="form-group">
            <?= Html::a('Отправить', 'javascript:void(0)', ['class' => 'btn btn-success', 'id' => 'answer-message-send-btn']) ?>
        </div>
    </div>
</div>