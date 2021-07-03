<?php

namespace app\modules\purchase\controllers\site;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use app\models\Category;
use app\modules\purchase\models\PurchaseProduct;

/**
 * Default controller for the `mailing` module
 */
class DefaultController extends Controller
{
    
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionPricelist()
    {
        $products = [];
        $productQuery = PurchaseProduct::find()->where('NOW() < purchase_date')->andWhere(['status' => 'advance'])->orderBy('purchase_date')->all();
        if ($productQuery) {
            foreach ($productQuery as $product) {
                $products[] =[
                    'name' => Category::getCategoryPath($product->productFeature->product->categoryHasProduct[0]->category->id),
                    'p_name' => $product->productFeature->product->name,
                    'descr' => ' (' . (!empty($product->productFeature->tare) ? $product->productFeature->tare . ', ' : "") . $product->productFeature->volume . ' ' . $product->productFeature->measurement . ')',
                    'date' => (new \DateTime($product->purchase_date))->format('d.m.Y'),
                    'price' => $product->productFeature->productPrices[0]->price != 0 ? $product->productFeature->productPrices[0]->price : '',
                    'member_price' => $product->productFeature->productPrices[0]->member_price != 0 ? $product->productFeature->productPrices[0]->member_price : ''
                ];
            }
        }
        usort($products, function($a, $b) {
            if ($a['name'] == $b['name']) {
                if (strtotime($a['date']) == strtotime($b['date'])) {
                    return ($a['p_name'] > $b['p_name']);
                }
                return (strtotime($a['date']) > strtotime($b['date']));
            }
            return ($a['name'] > $b['name']);
        });
        
        return $this->renderFile('@app/modules/purchase/views/site/default/pricelist.php', [
            'products' => $products,
        ]);
    }
}