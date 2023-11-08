<?php

namespace app\modules\purchase\controllers\admin;

use Yii;
use app\models\CandidateGroup;
use yii\web\Response;
use yii\data\ActiveDataProvider;
use app\modules\purchase\models\PurchaseProduct;
use app\modules\purchase\models\PurchaseOrder;
use app\models\Product;
use app\models\ProductFeature;
use app\models\ProductPrice;
use app\models\Template;
use app\helpers\Sum;

/**
 * Default controller for the `purchase` module
 */
class DefaultController extends BaseController
{
    public function actionCreate()
    {
        $dataProvider = new ActiveDataProvider([
            // 'query' => PurchaseProduct::find()->where('NOW() <= purchase_date')->orderBy('purchase_date')->orderBy('stop_date'),
            'query' => PurchaseProduct::find()->where('NOW() <= purchase_date')->andWhere(['!=', 'status', 'abortive'])->orderBy('purchase_date')->orderBy('stop_date'),
            'sort' => false,
        ]);
        
        return $this->render('create', [
            'model' => new PurchaseProduct,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionOldData()
    {
        $dataProvider = new ActiveDataProvider([
            // 'query' => PurchaseProduct::find()->where('NOW() > purchase_date')->orderBy('purchase_date')->orderBy('stop_date'),            
            'query' => PurchaseProduct::find()->where(['status' => 'abortive'])->orderBy('purchase_date')->orderBy('stop_date'),            
            'sort' => false,
        ]);
        
        return $this->render('old-data', [
            'model' => new PurchaseProduct,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionDeleteOldData()
    {
        $id = $_GET['id'];
        $page = $_GET['page'];

        $order = PurchaseProduct::findOne($id);
        $order->delete();
        
        return $this->redirect('old-data?page='.$page);
    }
    
    public function actionGetProducts()
    {
        $provider_id = $_POST['provider_id'];
        //$products = Product::getProductsByProvider($provider_id)->getModels();
        $products = Product::getProductsByProviderAll($provider_id);
        //print_r($products);
        $data = [];
        if ($products) {
            foreach ($products as $k => $val) {
                if (isset($val->categoryHasProduct[0])) {
                    if ($val->categoryHasProduct[0]->category->isPurchase()) {
                        $data[$val->categoryHasProduct[0]->category->name][$val->id] = $val->name;
                    }
                }
            }
        }
        
        return $this->renderPartial('_products', [
            'data' => $data,
        ]);
    }
    
    public function actionGetProduct()
    {
        $product_id = $_POST['product_id'];

        $product = Product::find()->joinWith('productFeatures')->joinWith('productFeatures.productPrices')->where(['product.id' => $product_id])->one();
        return $this->renderPartial('_form', [
            'product' => $product,
        ]);
    }
    
    public function actionGetFeature()
    {
        $id = $_POST['id'];
        $feature = ProductFeature::find()->joinWith('productPrices')->where(['product_feature.id' => $id])->one();
        $res = [
            'tare' => $feature->tare,
            'volume' => $feature->volume,
            'measurement' => $feature->measurement,
            'price' => $feature->productPrices[0]->purchase_price,
            'is_weights' => $feature->is_weights,
        ];
        return json_encode($res);
    }
    
    public function actionAddProduct() 
    {
        
        $volume = ($_POST['product_exists'] == '0') ? $_POST['volume'] : $_POST['volume_ex'];
        $tare = ($_POST['product_exists']) == '0' ? $_POST['tare'] : $_POST['tare_ex'];
        $measurement = ($_POST['product_exists'] == '0') ? $_POST['measurement'] : $_POST['measurement_ex'];
        $price = ($_POST['product_exists'] == '0') ? $_POST['summ'] : $_POST['summ_ex'];
        $comment = ($_POST['product_exists'] == '0') ? $_POST['comment'] : $_POST['comment_ex'];
        $deposit = ($_POST['product_exists'] == '0') ? (isset($_POST['send_notification']) ? 1 : 0) : (isset($_POST['send_notification_ex']) ? 1 : 0);
        $is_weights = ($_POST['product_exists'] == '0') ? (isset($_POST['is_weights']) ? 1 : 0) : (isset($_POST['is_weights_ex']) ? 1 : 0);
        
        $product = Product::find()->where(['id' => $_POST['product-id']])->one();
        $product_feature = ProductFeature::find()
            ->where([
                'product_id' => $product->id,
                'volume' => $volume,
                'measurement' => $measurement,
                'tare' => $tare,
                'is_weights' => $is_weights])
            ->one();
        if (!$product_feature) {
            $product_feature = new ProductFeature();
            $product_feature->product_id = $product->id;
            $product_feature->volume = $volume;
            $product_feature->measurement = $measurement;
            $product_feature->tare = $tare;
            $product_feature->quantity = 0;
            $product_feature->is_weights = $is_weights;
            $product_feature->save();
            
            $product_price = new ProductPrice();
            $product_price->product_id = $product->id;
            $product_price->product_feature_id = $product_feature->id;
            $product_price->purchase_price = $price; 
            $product_price->save();
        }
        
        $purchase = new PurchaseProduct;
        $purchase->created_date = $_POST['PurchaseProduct']['created_date'];
        $purchase->purchase_date = $_POST['PurchaseProduct']['purchase_date'];
        $purchase->stop_date = $_POST['PurchaseProduct']['stop_date'];
        $purchase->renewal = isset($_POST['PurchaseProduct']['renewal']) ? 1 : 0;
        $purchase->purchase_total = $_POST['PurchaseProduct']['purchase_total'];
        $purchase->is_weights = $is_weights;
        $purchase->tare = $tare;
        $purchase->weight = $volume;
        $purchase->measurement = $measurement;
        $purchase->summ = $price;
        $purchase->product_feature_id = $product_feature->id;
        $purchase->provider_id = $_POST['PurchaseProduct']['provider_id'];
        $purchase->comment = $comment;
        $purchase->send_notification = isset($_POST['send_notification']) ? 1 : 0;
        $purchase->status = 'advance';
        $purchase->save();
        //print_r($purchase);

        return true;
    }
    
    public function actionCnangeRenewal()
    {
        $purchase = PurchaseProduct::findOne($_POST['id']);
        $purchase->renewal = $_POST['checked'];
        $purchase->save();
        return true;
    }
    
    public function actionDownloadOrder($id)
    {
        $order = PurchaseOrder::findOne($id);

        if (!$order) {
            throw new NotFoundHttpException('Заказ не найден.');
        }

        $templateName = preg_replace('/^download-/', '', $this->action->id);
        $templateFile = Template::getFileByName('order', $templateName);
        if (!$templateFile) {
            throw new NotFoundHttpException('Шаблон не найден.');
        }
        $templateExtension = pathinfo($templateFile, PATHINFO_EXTENSION);
        $attachmentName = sprintf('%s-%d.%s', $templateName, $order->id, $templateExtension);

        $objectReader = \PHPExcel_IOFactory::createReader('Excel5');
        $objectExcel = $objectReader->load($templateFile);

        $spelloutTotal = sprintf(
            '%s %02d копеек',
            Yii::t('app', '{value, spellout}', ['value' => floor($order->total)], Yii::$app->language),
            round(100 * ($order->total - floor($order->total)))
        );

        $parameters = Template::getUserParameters($order->user ? $order->user : new User());
        $parameters['message'] = sprintf('Основание: Паевой взнос по программе "Стол заказов" - %.2f руб.', $order->total);
        $objectExcel->setActiveSheetIndex(0)
            ->setCellValue('A25', $parameters['message'])
            ->setCellValue('AM21', $order->total)
            ->setCellValue('AR15', sprintf('%05d', $order->order_id))
            ->setCellValue('BB15', Yii::$app->formatter->asDate($order->created_at, 'php:d.m.Y'))
            ->setCellValue('BQ10', sprintf('к приходному кассовому ордеру № %05d', $order->order_id))
            ->setCellValue('BQ12', sprintf('от %s г.', Yii::$app->formatter->asDate($order->created_at, 'php:d.m.Y')))
            ->setCellValue('BQ14', $parameters['fullName'])
            ->setCellValue('BQ16', $parameters['message'])
            ->setCellValue('BQ23', $spelloutTotal)
            ->setCellValue('BQ29', sprintf('%s г.', Yii::$app->formatter->asDate($order->created_at, 'php:d.m.Y')))
            ->setCellValue('BV21', floor($order->total))
            ->setCellValue('CM21', sprintf('%02d', round(100 * ($order->total - floor($order->total)))))
            ->setCellValue('F27', $spelloutTotal)
            ->setCellValue('K23', $parameters['fullName']);
        $objectWriter = \PHPExcel_IOFactory::createWriter($objectExcel, 'Excel5');

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $attachmentName .'"');
        header('Cache-Control: max-age=0');

        $objectWriter->save('php://output');

        exit();
    }
    
    public function actionDownloadAct($id)
    {
        $order = PurchaseOrder::findOne($id);

        if (!$order) {
            throw new NotFoundHttpException('Заказ не найден.');
        }

        $templateName = preg_replace('/^download-/', '', $this->action->id);
        $templateFile = Template::getFileByName('order', $templateName);
        if (!$templateFile) {
            throw new NotFoundHttpException('Шаблон не найден.');
        }
        $templateExtension = pathinfo($templateFile, PATHINFO_EXTENSION);
        $attachmentName = sprintf('%s-%d.%s', $templateName, $order->id, $templateExtension);

        $objectReader = \PHPExcel_IOFactory::createReader('Excel5');
        $objectExcel = $objectReader->load($templateFile);

        $parameters = Template::getUserParameters($order->user ? $order->user : new User());
        $parameters['orderTotal'] = sprintf('%.2f', $order->total);
        $parameters['orderSubTotal'] = sprintf('%.2f', $order->getProductPriceTotal('purchase_price'));
        $parameters['orderTax'] = sprintf('%.2f', $parameters['orderTotal'] - $parameters['orderSubTotal']);

        $objectExcel->setActiveSheetIndex(0)
            ->insertNewRowBefore(15, count($order->purchaseOrderProducts));

        foreach ($order->purchaseOrderProducts as $count => $orderHasProduct) {
            $cellName = 'A' . (15 + $count);
            $objectExcel->setActiveSheetIndex(0)
                ->setCellValue($cellName, $orderHasProduct->name);
            $objectExcel->setActiveSheetIndex(0)
                ->getStyle($cellName)->getFont()->setBold(false);
            $objectExcel->setActiveSheetIndex(0)
                ->getStyle($cellName)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

            $cellName = 'C' . (15 + $count);
            $objectExcel->setActiveSheetIndex(0)
                ->setCellValue($cellName, $orderHasProduct->purchase_price);
            $objectExcel->setActiveSheetIndex(0)
                ->getStyle($cellName)->getFont()->setBold(false);
            $objectExcel->setActiveSheetIndex(0)
                ->getStyle($cellName)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $cellName = 'D' . (15 + $count);
            $objectExcel->setActiveSheetIndex(0)
                ->setCellValue($cellName, $orderHasProduct->quantity);
            $objectExcel->setActiveSheetIndex(0)
                ->getStyle($cellName)->getFont()->setBold(false);
            $objectExcel->setActiveSheetIndex(0)
                ->getStyle($cellName)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $cellName = 'F' . (15 + $count);
            $objectExcel->setActiveSheetIndex(0)
                ->setCellValue($cellName, $orderHasProduct->purchase_price * $orderHasProduct->quantity);
            $objectExcel->setActiveSheetIndex(0)
                ->getStyle($cellName)->getFont()->setBold(false);
            $objectExcel->setActiveSheetIndex(0)
                ->getStyle($cellName)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        }

        $cellName = 'E' . (15 + count($order->purchaseOrderProducts));
        $objectExcel->setActiveSheetIndex(0)
            ->setCellValue($cellName, $parameters['orderSubTotal']);

        $cellName = 'B' . (18 + count($order->purchaseOrderProducts));
        $objectExcel->setActiveSheetIndex(0)
            ->setCellValue($cellName, $parameters['orderTax']);

        $cellName = 'E' . (23 + count($order->purchaseOrderProducts));
        $objectExcel->setActiveSheetIndex(0)
            ->setCellValue($cellName, $parameters['orderTotal']);

        $cellNumbers = [5, 9, 10, 11];
        foreach ($cellNumbers as $cellNumber) {
            $value = $objectExcel->setActiveSheetIndex(0)->getCell('A' . $cellNumber)->getValue();
            $objectExcel->setActiveSheetIndex(0)->setCellValue('A' . $cellNumber, Template::parseTemplate($parameters, $value));
        }

        $cellNumbers = [32];
        foreach ($cellNumbers as $cellNumber) {
            $cellNumber += count($order->purchaseOrderProducts);
            $value = $objectExcel->setActiveSheetIndex(0)->getCell('A' . $cellNumber)->getValue();
            $objectExcel->setActiveSheetIndex(0)->setCellValue('A' . $cellNumber, Template::parseTemplate($parameters, $value));
        }

        $objectWriter = \PHPExcel_IOFactory::createWriter($objectExcel, 'Excel5');

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $attachmentName .'"');
        header('Cache-Control: max-age=0');

        $objectWriter->save('php://output');

        exit();
    }
    
    public function actionDownloadRequest($id)
    {
        $order = PurchaseOrder::findOne($id);

        if (!$order) {
            throw new NotFoundHttpException('Заказ не найден.');
        }

        $templateName = preg_replace('/^download-/', '', $this->action->id);
        $templateFile = Template::getFileByName('order', $templateName);
        if (!$templateFile) {
            throw new NotFoundHttpException('Шаблон не найден.');
        }
        $templateExtension = pathinfo($templateFile, PATHINFO_EXTENSION);
        $attachmentName = sprintf('%s-%d.%s', $templateName, $order->id, $templateExtension);

        $objectReader = \PHPExcel_IOFactory::createReader('Excel5');
        $objectExcel = $objectReader->load($templateFile);

        $spelloutTotal = sprintf(
            '%s %02d копеек',
            Yii::t('app', '{value, spellout}', ['value' => floor($order->total)], Yii::$app->language),
            round(100 * ($order->total - floor($order->total)))
        );

        $parameters = Template::getUserParameters($order->user ? $order->user : new User());
        $parameters['orderTotal'] = sprintf('%.2f', $order->total);
        $parameters['orderSubTotal'] = sprintf('%.2f', $order->getProductPriceTotal('purchase_price'));
        $parameters['orderTax'] = sprintf('%.2f', $parameters['orderTotal'] - $parameters['orderSubTotal']);
        $parameters['quantityTotal'] = 0;

        $objectExcel->setActiveSheetIndex(0)
            ->setCellValue('A5', sprintf('ЗАКАЗ № %05d от %s', $order->order_id, $parameters['currentDate']))
            ->setCellValue('C8', $order->fullName)
            ->setCellValue('A15', sprintf('Итого к оплате: %s', $spelloutTotal));

        $objectExcel->setActiveSheetIndex(0)
            ->insertNewRowBefore(12, count($order->purchaseOrderProducts) - 1);

        foreach ($order->purchaseOrderProducts as $count => $orderHasProduct) {
            $objectExcel->setActiveSheetIndex(0)
                ->mergeCells('B' . (11 + $count) . ':' . 'E' . (11 + $count));
            $cellName = 'A' . (11 + $count);
            $objectExcel->setActiveSheetIndex(0)
                ->setCellValue($cellName, 1 + $count);
            $cellName = 'B' . (11 + $count);
            $objectExcel->setActiveSheetIndex(0)
                ->setCellValue($cellName, $orderHasProduct->name);
            $cellName = 'F' . (11 + $count);
            $objectExcel->setActiveSheetIndex(0)
                ->setCellValue($cellName, $orderHasProduct->price);
            $cellName = 'G' . (11 + $count);
            $objectExcel->setActiveSheetIndex(0)
                ->setCellValue($cellName, $orderHasProduct->quantity);
            $parameters['quantityTotal'] += $orderHasProduct->quantity;
            $cellName = 'I' . (11 + $count);
            $objectExcel->setActiveSheetIndex(0)
                ->setCellValue($cellName, $orderHasProduct->total);
        }

        $cellName = 'G' . (12 + $count);
        $objectExcel->setActiveSheetIndex(0)
            ->setCellValue($cellName, $parameters['quantityTotal']);

        $cellName = 'I' . (12 + $count);
        $objectExcel->setActiveSheetIndex(0)
            ->setCellValue($cellName, $order->total);

        $objectWriter = \PHPExcel_IOFactory::createWriter($objectExcel, 'Excel5');

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $attachmentName .'"');
        header('Cache-Control: max-age=0');

        $objectWriter->save('php://output');

        exit();
    }
    
    public function actionDownloadReturnFeeAct($id)
    {
        $order = PurchaseOrder::findOne($id);

        if (!$order) {
            throw new NotFoundHttpException('Заказ не найден.');
        }

        $templateName = preg_replace('/^download-/', '', $this->action->id);
        $templateFile = Template::getFileByName('order', $templateName);
        if (!$templateFile) {
            throw new NotFoundHttpException('Шаблон не найден.');
        }
        $templateExtension = pathinfo($templateFile, PATHINFO_EXTENSION);
        $attachmentName = sprintf('%s-%d.%s', $templateName, $order->id, $templateExtension);
        
        $objectReader = \PHPExcel_IOFactory::createReader('Excel5');
        $objectExcel = $objectReader->load($templateFile);

        $parameters = Template::getUserParameters($order->user);
        $value_b13 = $objectExcel->setActiveSheetIndex(0)->getCell('B13')->getValue();
        $value_f8 = $objectExcel->setActiveSheetIndex(0)->getCell('F8')->getValue();
        
        $objectExcel->setActiveSheetIndex(0)->setCellValue('T11', $parameters['currentDate']);
        $objectExcel->setActiveSheetIndex(0)->setCellValue('B13', Template::parseTemplate($parameters, $value_b13));
        $objectExcel->setActiveSheetIndex(0)->setCellValue('F8', Template::parseTemplate($parameters, $value_f8));
        $objectExcel->setActiveSheetIndex(0)->setCellValue('F4', $parameters['fullName']);
        $objectExcel->setActiveSheetIndex(0)->setCellValue('F6', $parameters['fullName']);
        $objectExcel->setActiveSheetIndex(0)->setCellValue('N11', sprintf('%05d', $order->order_id));

        $total_summ = 0;
        if (count($order->purchaseOrderProducts) > 1) {
            $objectExcel->setActiveSheetIndex(0)->insertNewRowBefore(20, count($order->purchaseOrderProducts) - 1);
        }
        
        foreach ($order->purchaseOrderProducts as $k => $val) {
            $objectExcel->setActiveSheetIndex(0)->mergeCells('C' . (19 + $k) . ':G' . (19 + $k));
            $objectExcel->setActiveSheetIndex(0)->mergeCells('H' . (19 + $k) . ':J' . (19 + $k));
            $objectExcel->setActiveSheetIndex(0)->mergeCells('Z' . (19 + $k) . ':AC' . (19 + $k));
            
            $objectExcel->setActiveSheetIndex(0)->setCellValue('B' . (19 + $k), $k + 1);
            $objectExcel->setActiveSheetIndex(0)->setCellValue('C' . (19 + $k), $val->name);
            $objectExcel->setActiveSheetIndex(0)->setCellValue('K' . (19 + $k), $val->productFeature->measurement);
            $objectExcel->setActiveSheetIndex(0)->setCellValue('M' . (19 + $k), $val->productFeature->tare);
            $objectExcel->setActiveSheetIndex(0)->setCellValue('O' . (19 + $k), $val->quantity);
            $objectExcel->setActiveSheetIndex(0)->setCellValue('T' . (19 + $k), number_format(sprintf("%01.2f", $val->price), 2, '.', ' '));
            $objectExcel->setActiveSheetIndex(0)->setCellValue('X' . (19 + $k), number_format(sprintf("%01.2f", $val->quantity * $val->price), 2, '.', ' '));
            $objectExcel->setActiveSheetIndex(0)->setCellValue('AG' . (19 + $k), number_format(sprintf("%01.2f", $val->quantity * $val->price), 2, '.', ' '));
            $objectExcel->setActiveSheetIndex(0)->setCellValue('Z' . (19 + $k), 'Без НДС');
            
            $total_summ += $val->total;
        }

        $objectExcel->setActiveSheetIndex(0)->setCellValue('X' . (19 + count($order->purchaseOrderProducts)), $total_summ);
        $objectExcel->setActiveSheetIndex(0)->setCellValue('AG' . (19 + count($order->purchaseOrderProducts)), $total_summ);
        $objectExcel->setActiveSheetIndex(0)->setCellValue('X' . (20 + count($order->purchaseOrderProducts)), $total_summ);
        $objectExcel->setActiveSheetIndex(0)->setCellValue('AG' . (20 + count($order->purchaseOrderProducts)), $total_summ);
        $objectExcel->setActiveSheetIndex(0)->setCellValue('F' . (36 + count($order->purchaseOrderProducts) - 1), '"' . Yii::$app->formatter->asDate($parameters['currentDate'], 'php:d') . '"');
        $objectExcel->setActiveSheetIndex(0)->setCellValue('I' . (36 + count($order->purchaseOrderProducts) - 1), Yii::$app->formatter->asDate($parameters['currentDate'], 'php:Y') . ' года');
        $objectExcel->setActiveSheetIndex(0)->setCellValue('G' . (36 + count($order->purchaseOrderProducts) - 1), Yii::$app->formatter->asDate($parameters['currentDate'], 'php:F'));
        $objectExcel->setActiveSheetIndex(0)->setCellValue('B' . (29 + count($order->purchaseOrderProducts) - 1), Sum::toStr($total_summ));
        $objectExcel->setActiveSheetIndex(0)->setCellValue('E' . (23 + count($order->purchaseOrderProducts) - 1), Sum::toStr(count($order->purchaseOrderProducts), false));
        $objectExcel->setActiveSheetIndex(0)->setCellValue('AG' . (33 + count($order->purchaseOrderProducts) - 1), $parameters['shortName']);
        
        $objectWriter = \PHPExcel_IOFactory::createWriter($objectExcel, 'Excel5');

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $attachmentName .'"');
        header('Cache-Control: max-age=0');

        $objectWriter->save('php://output');

        exit();
    }
    
    public function actionDelete()
    {
        $order = PurchaseOrder::findOne($_POST['id']);

        $order->delete();
        return true;
    }
    
    public function actionDeleteReturn()
    {
        $order = PurchaseOrder::findOne($_POST['id']);

        $order->deleteReturn();
        return true;
    }
}