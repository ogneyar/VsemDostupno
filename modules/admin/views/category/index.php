<?php

use yii\web\JsExpression;
use yii\helpers\Html;

$this->title = 'Категории';
$this->params['breadcrumbs'][] = $this->title;

$script = <<<JS
$(function () {
    $(".view-category").click(function() {
        window.location = "/web/admin/category/view?id=" + $(this).attr("data-id");
    });
    $(".update-category").click(function() {
        window.location = "/web/admin/category/update?id=" + $(this).attr("data-id");
    });
    $(".delete-category").click(function() {
        if (confirm('Вы уверены, что желаете удалить категорию?')) {
            window.location = "/web/admin/category/delete?id=" + $(this).attr("data-id");
        }
    });
});
JS;
$this->registerJs($script, $this::POS_END);

?>
<div>
    <h1><?= Html::encode($this->title) ?></h1>
    <?= yii2mod\tree\Tree::widget([
            'items' => $items,
            'clientOptions' => [
                'extensions' => ["edit", "dnd", "table", "gridnav"],
                'dnd' => [
                    'preventVoidMoves' => true,
                    'preventRecursiveMoves' => true,
                    'autoExpandMS' => 400,
                    'dragStart' => new JsExpression('function(node, data) {return true;}'),
                    'dragEnter' => new JsExpression('function(node, data) {return true;}'),
                    'dragDrop' => new JsExpression('function(node, data) {
                        data.otherNode.moveTo(node, data.hitMode);
                        if (data.hitMode == "before" || data.hitMode == "after") {
                            var parent_id = data.node.parent.data.id;
                        } else if (data.hitMode == "over") {
                            var parent_id = data.node.data.id;
                        }
                        $.ajax({
                            url: "/web/admin/category/save-category",
                            type: "POST",
                            data: {id: data.otherNode.data.id, parent_id: parent_id},
                            async: false,
                            success: function(response) {
                                
                            }
                        });
                    }')
                ],
                'edit' => [
                    'triggerStart' => ["f2", "shift+click", "mac+enter"],
                    'close' => new JsExpression('function(event, data) {if( data.save && data.isNew ){$("#tree").trigger("nodeCommand", {cmd: "addSibling"});}}'),
                ],
                'table' => [
                    'indentation' => 20,
                    'nodeColumnIdx' => 0,
                ],

                'gridnav' => [
                    'autofocusInput' => false,
                    'handleCursorKeys' => true
                ],
                'renderColumns' => new JsExpression('function(event, data) {
                    var node = data.node,
                    $tdList = $(node.tr).find(">td");
                    var el = $tdList.eq(2);
                    $(el).find("a").attr("data-id", node.data.id);
                }'),
                'collapse' => new JsExpression('function(event, data) {
                    $.ajax({
                        url: "/web/admin/category/update-collapsed",
                        type: "POST",
                        data: {id: data.node.data.id, value: 1},
                        async: false,
                        success: function(response) {
                            
                        }
                    });
                }'),
                'expand' => new JsExpression('function(event, data) {
                    $.ajax({
                        url: "/web/admin/category/update-collapsed",
                        type: "POST",
                        data: {id: data.node.data.id, value: 0},
                        async: false,
                        success: function(response) {
                            
                        }
                    });
                }')
            ],
        ]);
    ?>
</div>