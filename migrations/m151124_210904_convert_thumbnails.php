<?php

use yii\db\Schema;
use yii\db\Migration;
use app\models\Category;
use app\models\Product;
use yii\imagine\Image;

class m151124_210904_convert_thumbnails extends Migration
{
    public function up()
    {
        foreach (Category::find()->each() as $category) {
            if ($category->photo) {
                Image::thumbnail(
                    'web' . $category->photo->image->file,
                    Category::MAX_THUMB_WIDTH,
                    Category::MAX_THUMB_HEIGHT
                )
                ->save('web' . $category->photo->thumb->file);
            }
        }

        foreach (Product::find()->each() as $product) {
            foreach ($product->productHasPhoto as $productHasPhoto) {
                Image::thumbnail(
                    'web' . $productHasPhoto->photo->image->file,
                    Product::MAX_GALLERY_THUMB_WIDTH,
                    Product::MAX_GALLERY_THUMB_HEIGHT
                )
                ->save('web' . $productHasPhoto->photo->thumb->file);
            }
        }
    }

    public function down()
    {
    }
}
