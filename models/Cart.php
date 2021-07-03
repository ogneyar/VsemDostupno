<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\web\Session;
use app\models\Product;
use app\models\ProductFeature;

/**
 * This is the model class for "Cart".
 */
class Cart extends Model
{
    protected $key = 'cart';

    public function add($product, $quantity = 1)
    {
        $cart = $this->cart;

        if (isset($cart[$product->id])) {
            $quantity += $cart[$product->id]['quantity'];
        }

        return $this->update($product, $quantity);
    }

    public function update($product, $quantity)
    {
        $cart = $this->cart;

        if ($quantity > 0 && $product->product->visibility) {
            if ($quantity > $product->quantity) {
                $quantity = $product->quantity;
            }

            if (isset($cart[$product->id])) {
                $cart[$product->id]['quantity'] = $quantity;
            } else {
                $cart[$product->id] = [
                    'quantity' => $quantity,
                ];
            }
        } else {
            unset($cart[$product->id]);
            $quantity = 0;
        }

        $this->cart = $cart;

        return $quantity;
    }

    public function remove($product, $quantity = 1)
    {
        $cart = $this->cart;

        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] -= $quantity;
            if ($cart[$product->id]['quantity'] <= 0) {
                unset($cart[$product->id]);
            }
        }

        $this->cart = $cart;
    }

    public function clear()
    {
        $this->cart = [];
    }

    protected function getCart()
    {
        $session = new Session();
        $session->open();
        $cart = unserialize($session->get($this->key, serialize([])));
        $session->close();

        return $cart;
    }

    protected function setCart($cart)
    {
        $session = new Session();
        $session->open();
        $session->set($this->key, serialize($cart));
        $session->close();
    }

    public function getProducts()
    {
        $cart = $this->cart;

        if ($cart) {
            $products = ProductFeature::find()
                ->joinWith('product')
                ->andWhere(['IN', 'product_feature.id', array_keys($cart)])
                ->andWhere('product.visibility != 0')
                //->andWhere('quantity > 0')
                ->orderBy(['name' => SORT_ASC])
                ->all();
                
            foreach ($products as $index => $product) {
                if ($products[$index]->product->isPurchase()) {
                    $products[$index]->quantity = 100;
                }
                if ($cart[$product->id]['quantity'] > $products[$index]->quantity) {
                    $products[$index]->cart_quantity = $products[$index]->quantity;
                } else {
                    $products[$index]->cart_quantity = $cart[$product->id]['quantity'];
                }
            }

            return $products;
        }

        return [];
    }

    public function getQuantity()
    {
        return array_sum(ArrayHelper::getColumn($this->cart, 'quantity'));
    }

    public function getTotal()
    {
        $total = 0;

        foreach ($this->products as $product) {
            $total += $product->calculatedTotalPrice;
        }

        return sprintf('%.2f', $total);
    }

    public function getFormattedTotal()
    {
        return Yii::$app->formatter->asCurrency($this->total, 'RUB');
    }

    public function getInformation()
    {
        return sprintf('%s', $this->formattedTotal);
    }

    public static function hasProduct($product)
    {
        $cart = new self();

        return $product && isset($cart->cart[$product->productFeatures[0]->id]);
    }

    public static function hasProductId($product)
    {
        $cart = new self();

        return $product && isset($cart->cart[$product->id]);
    }
    
    public static function hasQuantity($product)
    {
        $cart = new self();

        return $product && isset($cart->cart[$product->id]) ? $cart->cart[$product->id]['quantity'] : 0;
    }

    public static function isEmpty()
    {
        $cart = new self();

        return !$cart->cart;
    }
}
