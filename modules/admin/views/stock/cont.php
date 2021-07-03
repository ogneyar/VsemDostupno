<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\web\JsExpression;
use kartik\dropdown\DropdownX;

/* @var $this yii\web\View */
//* @var $dataProvider yii\data\ActiveDataProvider */
$this->title= "Перевод пая на лицевой счёт";
$this->params['breadcrumbs'][]=['label'=>'Поставщики', 'url'=>URL::to(['/admin/provider'])];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="member-index">

    <h1><?= Html::encode($this->title) ?></h1>


    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'label'=>'Дата внесения паевых взносов',
                'value' => function($data) {
                    return $data['date'];
                }
            ],
            [
                'label'=>'Сумма П/В',
                'value' => function($data) {
                    return $data['total_sum'];
                }
            ],
            [
                'label'=>'Остаток П/В',
                'value' => function($data) {
                    return $data['summ_reminder'];
                }
            ],
            [
                'label'=>'Сумма на лицевом счёте',
                'value'=>function($data){

                    return $data['dep_summ'];
                }
            ],

            ['class' => 'yii\grid\ActionColumn',
                'template'=> '{actions}',
                'buttons' => [
                    'actions' => function ($url, $model) {


                        return Html::beginTag('div', ['class' => 'dropdown']) .
                            Html::button('Действия <span class="caret"></span>', [
                                'type' => 'button',
                                'class' => 'btn btn-default',
                                'data-toggle' => 'dropdown'
                            ]) .

                            DropdownX::widget([
                                'items' => [
                                    [
                                        'label' => 'Удалить',
                                        'url' => Url::to(['/admin/stock/deletecon', 'id' => $model['id'], 'provider' => $model['provider_id']]),
                                        'linkOptions' => [
                                            'data' => [
                                                'confirm' => 'Вы уверены, что хотите удалить запись?',
                                                'method' => 'post',
                                            ],
                                        ]
                                    ],

                                ],
                            ]);
                    }
                ],
            ],
        ],
    ]);
    ?>

</div>