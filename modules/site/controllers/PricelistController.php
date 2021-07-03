<?php

namespace app\modules\site\controllers;

use Yii;
use yii\web\Controller;
use app\models\Product;
use app\models\Category;

class PricelistController extends BaseController
{
    public function actionProduct()
    {
        $products = [];
        $productQuery = Product::getPriceList();
        foreach ($productQuery as $product) {
            $products[] = [
                'name' => Category::getCategoryPath($product->product->categoryHasProduct[0]->category->id) . $product->product->name . ' (' . $product->tare . ', ' . $product->volume . ' ' . $product->measurement . ')',
                //'date' => (new \DateTime($product->stock_date))->format('d.m.Y'),
                'date' => $product->product->purchaseDate ? (new \DateTime($product->product->purchaseDate))->format('d.m.Y') : '',
                'inventory' => $product->quantity,
                'price' => $product->productPrices[0]->price != 0 ? $product->productPrices[0]->price : '',
                'member_price' => $product->productPrices[0]->member_price != 0 ? $product->productPrices[0]->member_price : ''
            ];
        }

        usort($products, function($a, $b){
            return ($a['name'] > $b['name']);
        });
        
        return $this->renderFile('@app/modules/site/views/pricelist/product.php', [
            'productQuery' => $products
        ]);
    }
}
