<?php

use kartik\helpers\Html;
use dosamigos\selectize\SelectizeDropDownList;
use yii\widgets\ActiveForm;
use yii\bootstrap\Modal;
use app\modules\mailing\models\MailingMessage;

$this->title = 'Вопросы, жалобы, предложения';
$this->params['breadcrumbs'] = [$this->title];

?>
<?= Html::pageHeader(Html::encode($this->title)) ?>
<div class="mailing-message">
    <p>На этой странице Вы можете задать свой вопрос, пожаловаться на работу нерадивого сотрудника, а также внести рациональное предложение для улучшения работы нашего Общества.</p>
    
    <br>
    <?php $form = ActiveForm::begin(['id' => 'mailing_message_frm']); ?>
        <div class="col-md-6">
            <div class="form-group">
                <label for="category">Категория сообщения</label>
                <?= SelectizeDropDownList::widget([
                    'name' => 'category',
                    'id' => 'category',
                    'items' => [
                        MailingMessage::CATEGORY_QUESTION => 'Вопрос',
                        MailingMessage::CATEGORY_CLAIM => 'Жалоба',
                        MailingMessage::CATEGORY_PROPOSAL => 'Предложение',
                    ],
                ]); ?>
            </div>
            <div class="form-group">
                <label for="subject">Тема</label>
                <?= Html::textInput('subject', null, ['class' => 'form-control', 'id' => 'subject']); ?>
            </div>
            <div class="form-group">
                <label for="subject">Сообщение</label>
                <?= Html::textarea('message', null, ['class' => 'form-control', 'id' => 'message', 'rows' => 10, 'style' => 'resize: none;']); ?>
            </div>
            <div class="form-group">
                <?= Html::button('Отправить', ['class' => 'btn btn-success', 'id' => 'send-mailing-message-btn', 'onclick' => 'sendMailingMessage()']) ?>
            </div>
        </div>
    <?php ActiveForm::end(); ?>
</div>

<?php Modal::begin([
    'id' => 'message-sending-thanx-modal',
    'options' => ['tabindex' => false,],
]); ?>

    <p>Благодарим Вас за активное участие в улучшении работы Потребительского общества</p>

<?php Modal::end(); ?>