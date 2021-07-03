<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\web\JsExpression;
use kartik\dropdown\DropdownX;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\SqlDataProvider */
$this->title= "Поиск";
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="member-index">

    <h1><?= Html::encode($this->title) ?></h1>


    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
       'lastname:text:Фамилия',
            'firstname:text:Имя',
            'patronymic:text:Отчество',
            [
            'attribute'=>'role',
            'label'=>'Роль',
            'content'=>function($data){
                
            if($data['role']=='member')
                {return "Участник";}
            
            }
            ],
            'email:text:Email',
            'phone:text:Телефон',
            'name:text:Название организации',
            'number:text:Номер',
            [
                'class' => 'yii\grid\ActionColumn',
                'template'=> '{actions}',
                'buttons' => [
                    'actions' => function ($url, $model) {

                        if($model['role']=='member'){
                        
                        return Html::beginTag('div', ['class'=>'dropdown']) .
                            Html::button('Действия <span class="caret"></span>', [
                                'type'=>'button',
                                'class'=>'btn btn-default',
                                'data-toggle'=>'dropdown'
                            ]) .

                            DropdownX::widget([
                                'items' => [
                                    [
                                        'label' => 'Счета',
                                        'url' => Url::to(['profile/partner/member/account', 'id' => $model['member_id']]),
                                    ],
                                    '<li class="divider"></li>',
                                    
                                    [
                                        'label' => 'Редактировать',
                                        'url' => Url::to(['profile/partner/member/update', 'id' => $model['member_id']]),
                                    ],
                                    
                                    
                                    
                                    
                                    
                                    
                                ],
                            ]) .
                            Html::endTag('div');
                        } 
                    }
                ],
             ],
        ],
    ]); ?>

</div>
