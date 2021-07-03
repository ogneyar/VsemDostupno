<?php

use yii\db\Migration;
use app\models\User;
use app\models\Category;
use app\models\CategoryHasProduct;
use app\models\CategoryHasService;
use app\models\Product;
use app\models\ProductHasPhoto;
use app\models\Service;
use app\models\ServiceHasPhoto;

class m160906_072927_move_products_to_services extends Migration
{
    public function up()
    {
        $user = User::findOne(['email' => 'partner@vsemdostupno.ru']);
        $category = Category::findOne(['slug' => Category::SERVICE_SLUG]);

        foreach ($category->getAllProductsQuery()->each() as $product) {
            $service = new Service([
                'user_id' => $user->id,
                'visibility' => 1,
                'published' => 1,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price > 0 ? $product->price : '',
            ]);
            $service->save();

            foreach ($product->categories as $category) {
                $categoryHasService = new CategoryHasService([
                    'category_id' => $category->id,
                    'service_id' => $service->id,
                ]);
                $categoryHasService->save();
            }

            foreach ($product->productHasPhoto as $productHasPhoto) {
                $serviceHasPhoto = new ServiceHasPhoto([
                    'service_id' => $service->id,
                    'photo_id' => $productHasPhoto->photo_id,
                ]);
                $serviceHasPhoto->save();
            }

            ProductHasPhoto::deleteAll('product_id = :product_id', [':product_id' => $product->id]);
            $product = Product::findOne($product->id);
            $product->delete();
        }
    }

    public function down()
    {
        $category = Category::findOne(['slug' => Category::SERVICE_SLUG]);

        foreach ($category->getAllServicesQuery()->each() as $service) {
            $product = new Product([
                'visibility' => 1,
                'name' => $service->name,
                'description' => $service->description,
                'price' => $service->price ? $service->price : 0,
                'member_price' => $service->price ? $service->price : 0,
                'partner_price' => $service->price ? $service->price : 0,
            ]);
            $product->save();

            foreach ($service->categories as $category) {
                $categoryHasProduct = new CategoryHasProduct([
                    'category_id' => $category->id,
                    'product_id' => $product->id,
                ]);
                $categoryHasProduct->save();
            }

            foreach ($service->serviceHasPhoto as $serviceHasPhoto) {
                $productHasPhoto = new ProductHasPhoto([
                    'product_id' => $product->id,
                    'photo_id' => $serviceHasPhoto->photo_id,
                ]);
                $productHasPhoto->save();
            }

            ServiceHasPhoto::deleteAll('service_id = :service_id', [':service_id' => $service->id]);
            $service = Service::findOne($service->id);
            $service->delete();
        }
    }
}
