<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\bootstrap\Modal;
use kartik\file\FileInput;
use mihaildev\ckeditor\CKEditor;

$this->title = 'Рассылка информации';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="mailing-default-index">
    <h1><?= Html::encode($this->title) ?></h1>
    
    <?php $form = ActiveForm::begin(['id' => 'mailing_info_frm', 'options' => ['enctype'=>'multipart/form-data']]); ?>
        
        <input type="hidden" name="candidates-all" id="candidates-all-hdn" value="0">
        <div class="form-group">
            <?= Html::checkbox('members', false, ['id' => 'members']); ?>
            <label for="members">Для Участников</label>
        </div>
        
        <div class="form-group">
            <?= Html::checkbox('partners', false, ['id' => 'partners']); ?>
            <label for="partners">Для Партнёров</label>
        </div>
        
        <div class="form-group">
            <?= Html::checkbox('providers', false, ['id' => 'providers']); ?>
            <label for="providers">Для Поставщиков</label>
        </div>
        
        <div class="form-group">
            <?= Html::checkbox('candidates', false, ['id' => 'candidates']); ?>
            <label for="candidates">Для Кандидатов</label>
        </div>
        
        <div class="form-group" id="candidates-groups" style="display: none;">
            <?php if ($groups): ?>
                <?= Html::checkbox('candidates-all', false, ['id' => 'candidates-all']); ?>
                <label for="candidates-all">Все</label>&nbsp;&nbsp;&nbsp;
                <?php foreach ($groups as $group): ?>
                    <?= Html::checkbox('candidates[' . $group->id . ']', false, ['id' => 'candidates-' . $group->id, 'class' => 'candidates-gr']); ?>
                    <label for="candidates-<?= $group->id; ?>"><?= $group->name; ?></label>&nbsp;&nbsp;&nbsp;
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label>Информационная категория</label>
        </div>
        
        <div class="form-group">
            <?= Html::radio('category', true, ['value' => '1', 'label' => 'Новости, сообщения']); ?>
            <?= Html::radio('category', false, ['value' => '5', 'label' => 'Голосования']); ?>
        </div>
        
        <div class="form-group row" id="subject-container">
            <div class="col-md-7">
                <label for="subject">Тема</label>
                <?= Html::textInput('subject', null, ['class' => 'form-control', 'id' => 'subject']); ?>
            </div>
        </div>
        
        <div class="form-group row" id="subject-vote-container" style="display: none;">
            <div class="col-md-7">
                <label for="subject">Тема</label>
                <?= Html::textarea('subject_vote', null, ['class' => 'form-control', 'id' => 'subject-vote', 'rows' => 10]); ?>
            </div>
        </div>
        
        <div class="form-group row" id="message-container">
            <div class="col-md-7">
                <label for="subject">Сообщение</label>
                <?= CKEditor::widget([
                    'name' => 'message',
                    'id' => 'message',
                    'value' => '<br><br>На это письмо отвечать не нужно, рассылка произведена автоматически.',
                    'editorOptions' => [
                        'preset' => 'full',
                        'inline' => false,
                    ]
                ]);?>
            </div>
        </div>
        
        
        
        <div class="form-group row">
            <div class="col-md-12">
                <label for="subject">Прикрепить файлы</label>
                <?= FileInput::widget([
                    'name' => 'attachment[]',
                    'id' => 'attachment',
                    'language' => 'ru',
                    'options' => ['multiple' => true],
                    'pluginOptions' => ['previewFileType' => 'any']
                ]); ?>
            </div>
        </div>
        
        <div class="form-group row col-md-12">
            <?= Html::button('Отправить', ['class' => 'btn btn-success', 'id' => 'send-mailing-btn']) ?>
        </div>
        
    <?php ActiveForm::end(); ?>
</div>

<?php Modal::begin([
    'id' => 'send-mailing-modal',
    'options' => ['tabindex' => false,],
    'header' => '<h4>' . 'Рассылка информации' . '</h4>',
    'footer' => '<a class="btn btn-default" data-dismiss="modal" aria-hidden="true" id="mailing-info-cancel-btn">' . 'Отменить' . '</a>
                 <button id="mailing-info-btn" class="btn btn-success" type="button">' . 'Отправить' . '</button>',
]); ?>

    <p style="display: none;" id="modal-title-ok">Рассылка будет произведена для</p>
    <p style="display: none;" id="members-modal"><strong>Участников</strong></p>
    <p style="display: none;" id="partners-modal"><strong>Партнёров</strong></p>
    <p style="display: none;" id="providers-modal"><strong>Поставщиков</strong></p>
    <p style="display: none;" id="candidates-modal"><strong>Кандидатов</strong></p>
    
    <p style="display: none;" id="modal-title-empty-address">Не выбраны адресаты, рассылка невозможна!</p>
    <p style="display: none;" id="modal-title-empty-subject">Не указана тема сообщения, рассылка невозможна!</p>
    <p style="display: none;" id="modal-title-empty-message">Текст сообщения пуст, рассылка невозможна!</p>

<?php Modal::end(); ?>
<div class="loader">Подождите, идет отправка сообщений</div>