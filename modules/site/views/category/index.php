<?php

use yii\web\View;
use yii\bootstrap\Alert;
use kartik\helpers\Html;
use yii\data\Pagination;
use app\models\Category;

/* @var $this yii\web\View */
$this->title = $model->fullName;

//$this->params['breadcrumbs'] = $model->breadcrumbs;

$categories = Category::find()
    ->where(['parent' => $model->id])
    ->andWhere('visibility != 0')
    ->orderBy([
        'order' => SORT_ASC,
        //'purchase_timestamp' => SORT_ASC,
        'name' => SORT_ASC,
    ])
    ->all();

$productsQuery = $model->getAllProductsQuery()
    ->andWhere('visibility != 0')
    ->andWhere('published != 0');
$productsCount = clone $productsQuery;
$productPages = new Pagination([
    'totalCount' => $productsCount->count(),
    'pageSize' => Yii::$app->params['pageSize'],
]);
$products = $productsQuery->offset($productPages->offset)
    ->limit($productPages->limit)
    //->orderBy(['name' => SORT_ASC])
    ->all();

$servicesQuery = $model->getAllServicesQuery()
    ->andWhere('visibility != 0')
    ->andWhere('published != 0');
$servicesCount = clone $servicesQuery;
$servicePages = new Pagination([
    'totalCount' => $servicesCount->count(),
    'pageSize' => Yii::$app->params['pageSize'],
]);
$services = $servicesQuery->offset($servicePages->offset)
    ->limit($servicePages->limit)
    ->orderBy(['name' => SORT_ASC])
    ->all();
    
?>

<?= Html::pageHeader(Html::encode($model->fullName), '', ['id' => 'page-header-category']) ?>

<?php if ($model->description): ?>
    <div class="row category-description" id="inner-cate-descr">
        <div class="col-md-12">
            <?= $model->description ?>
        </div>
    </div>
<?php endif ?>

<?= $this->renderFile('@app/modules/site/views/category/snippets/category-panel.php', [
    'categories' => $categories,
    'menu_first_level' => $menu_first_level,
]) ?>

<?php if (count($categories) == 0): ?>
    <?= $this->renderFile('@app/modules/site/views/category/snippets/product-panel.php', [
        'name' => count($categories) ? 'Товары' : null,
        'products' => $products,
        'pages' => $productPages,
    ]) ?>
<?php endif; ?>

<?php if (count($categories) == 0): ?>
    <?= $this->renderFile('@app/modules/site/views/category/snippets/service-panel.php', [
        'name' => count($categories) || count($products) ? 'Услуги' : null,
        'services' => $services,
        'pages' => $servicePages,
    ]) ?>
<?php endif; ?>

<?php if (!$model->description && !$categories && !$products && !$services): ?>
    <?= Alert::widget([
        'body' => 'Ничего не найдено.',
        'options' => [
            'class' => 'alert-info',
            'id' => 'inner-alert-info'
        ],
    ])?>
<?php endif ?>
