<?php

use yii\helpers\Html;

?>

<?php if ($services): ?>
    <div class="row service-panel" id="inner-service">
        <div class="col-md-12">
            <?php if (!empty($name)): ?>
                <div class="row service-name">
                    <div class="col-md-12">
                        <h2><?= Html::encode($name) ?></h2>
                    </div>
                </div>
            <?php endif ?>
            <div class="row">
                <div class="col-md-12">
                    <?= $this->renderFile('@app/modules/site/views/category/snippets/service-grid.php', [
                        'services' => $services,
                        'pages' => isset($pages) ? $pages : null,
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
<?php endif ?>
