<?php

use yii\helpers\Html;
use yii\widgets\DetailView;


use app\modules\mailing\models\MailingProduct;
use yii\bootstrap\Modal;
use app\models\Product;
use app\models\CandidateGroup;
use yii\widgets\ActiveForm;
use mihaildev\ckeditor\CKEditor;


/* @var $this yii\web\View */
/* @var $model app\models\Product */

$this->title = 'Товар: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Товары', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs("CKEDITOR.plugins.addExternal('youtube', '/ckeditor/plugins/youtube/youtube/plugin.js', '');");

?>
<div class="product-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Изменить', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Вы уверены, что хотите удалить этот товар?',
                'method' => 'post',
            ],
        ]) ?>
    

    <?php if (Yii::$app->hasModule('mailing')): ?>
        <?php $mailing_product_id = $model->isNewRecord ? Product::getNextId() : $model->id ?>

        <?php if (!MailingProduct::find()->where(['product_id' => $mailing_product_id])->exists()): ?>
            <?php $this->registerJsFile('/js/mailing/admin.js',  ['position' => $this::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]); ?>

            <?php $groups = CandidateGroup::find()->all(); ?>
            <?= Html::button('Рассылка информации', ['class' => 'btn btn-success', 'id' => 'mailing-product-btn', 'data-toggle' => 'modal', 'data-target' => '#mailing-product-modal']); ?>
            <?php Modal::begin([
                'id' => 'mailing-product-modal',
                'options' => ['tabindex' => false,],
                'header' => '<h4>' . 'Рассылка информации' . '</h4>',
                'footer' => '<a class="btn btn-default" data-dismiss="modal" aria-hidden="true" id="mailing-product-info-cancel-btn">' . 'Отменить' . '</a>
                             <button id="mailing-product-info-save-btn" class="btn btn-success" type="button">' . 'Сохранить' . '</button>',
            ]); ?>

                <?php $form = ActiveForm::begin(['id' => 'mailing_product_info_frm']); ?>
            
                    <input type="hidden" name="candidates-all" id="candidates-all-hdn" value="0">
                    <input type="hidden" name="product-id" id="product-id-hdn" value="<?= $mailing_product_id; ?>">
                    <div class="form-group">
                        <?= Html::checkbox('members', false, ['id' => 'members']); ?>
                        <label for="members">Для Участников</label>
                    
                        <?= Html::checkbox('providers', false, ['id' => 'providers']); ?>
                        <label for="providers">Для Поставщиков</label>
                    
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
                        <?= Html::radio('category', true, ['value' => '2', 'label' => 'Реклама новых товаров']); ?>
                        <?= Html::radio('category', false, ['value' => '3', 'label' => 'Акции, спец предложения']); ?>
                        <?= Html::radio('category', false, ['value' => '4', 'label' => 'Информация о предстоящих закупках']); ?>
                    </div>
            
                    <div class="form-group">
                        <label for="subject">Тема</label>
                        <?= Html::textInput('subject', null, ['class' => 'form-control', 'id' => 'subject']); ?>
                    </div>
            
                    <div class="form-group" id="message-container">
                        <label for="subject">Сообщение</label>
                        <?= CKEditor::widget([
                            'name' => 'message',
                            'id' => 'message',
                            'value' => '<br>На это письмо отвечать не нужно, рассылка произведена автоматически.',
                            'editorOptions' => [
                                'preset' => 'basic',
                                'inline' => false,
                            ]
                        ]);?>
                    </div>
            
                <?php ActiveForm::end(); ?>

            <?php Modal::end(); ?>
        <?php endif; ?>
    <?php endif; ?>
    
    </p>
    

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'visibility',
            'only_member_purchase',
            'published',
            'auto_send',
            [
                'label' => 'Поставщик',
                'value' => $model->getProviderForView(),
            ],
            [
                'label' => 'Категория',
                'value' => $model->getCategoryForView(),
            ],
            'name',
            [
                'label' => 'Имеющиеся виды',
                'value' => $model->getFeaturesForView(),
                'format' => 'raw',
            ],
            'composition',
            'packing',
            'manufacturer',
            'status',
            'description:html',
            'min_inventory',
            'expiry_timestamp',
            'thumbUrl:image',
            'thumbUrlManufacturer:image',
        ],
    ]) ?>

</div>
