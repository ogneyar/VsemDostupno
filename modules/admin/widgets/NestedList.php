<?php

namespace app\modules\admin\widgets;

use Yii;
use yii\helpers\Html;

class NestedList extends \kgladkiy\widgets\NestedList
{
    protected function renderInput()
    {
        if ( count($this->items) == 0 ) {
            echo Html::tag('div', Yii::t('yii', 'No results found.'), ['class' => 'empty']);
        } else {
            parent::renderInput();
        }
    }

    protected function buildActionButtons($id)
    {
        $html = Html::beginTag('div', ['class'=>'nested-actions']);
        $html .= Html::tag('span', Html::checkbox('for_reg', false, ['id' => 'for-reg-' . $id, 'value' => $id]), ['class' => 'for-reg-check', 'title' => 'Доступно для регистрации']);
        $html .= Html::a(Html::tag('span', '', ['class'=>'glyphicon glyphicon-eye-open']), [$this->view->context->id . '/view', 'id' => $id], ['class' => 'btn btn-default btn-xs', 'data-pjax' => '0', 'aria-label' => Yii::t('yii', 'View'), 'title' => Yii::t('yii', 'View')]);
        $html .= Html::a(Html::tag('span', '', ['class'=>'glyphicon glyphicon-pencil']), [$this->view->context->id . '/update', 'id' => $id], ['class' => 'btn btn-default btn-xs', 'title' => Yii::t('yii', 'Update'), 'aria-label' => Yii::t('yii', 'Update'), 'data-pjax' => '0']);
        $html .= Html::a(Html::tag('span', '', ['class'=>'glyphicon glyphicon-trash']), [$this->view->context->id . '/delete', 'id' => $id], ['class' => 'btn btn-default btn-xs', 'title' => Yii::t('yii', 'Delete'), 'aria-label' => Yii::t('yii', 'Delete'), 'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'), 'data-method' => 'post', 'data-pjax' => '0']);
        $html .= Html::endTag('div');
        return $html;
    }
}
