<?php

namespace app\modules\admin\controllers;

use Yii;
use yii\base\Exception;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use PhpOffice\PhpWord\TemplateProcessor;
use app\models\Account;
use app\models\AccountLog;
use app\models\Email;
use app\models\Forgot;
use app\models\Product;
use app\models\Provider;
use app\models\Template;
use app\models\User;
use app\models\Member;
use app\models\StockHead;
use app\models\StockBody;
use app\models\ProviderStock;
use app\models\Candidate;
use app\modules\admin\models\AccountForm;
use app\modules\admin\models\ProviderForm;
use app\helpers\Html;
use app\helpers\Sum;

/**
 * ProviderController implements the CRUD actions for Provider model.
 */
class ProviderController extends BaseController
{
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ]);
    }

    /**
     * Lists all Provider models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Provider::find()->joinWith('user')->where('user.request = 0'),
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Provider model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Provider model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() 
    {
        $model = new ProviderForm([
            'categoryIds' => '',
            'categories' => [],
        ]);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $transaction = Yii::$app->db->beginTransaction();

            try {
                $user = new User();
                $user->role = User::ROLE_PROVIDER;
                $user->disabled = $model->disabled;
                $user->tg_id = $model->tg_id;
                $user->email = $model->email;
                $user->phone = $model->phone;
                $user->ext_phones = $model->ext_phones;
                $user->firstname = $model->firstname;
                $user->lastname = $model->lastname;
                $user->patronymic = $model->patronymic;
                $user->created_ip = Yii::$app->getRequest()->getUserIP();
                $user->birthdate = $model->birthdate;
                $user->citizen = $model->citizen;
                $user->registration = $model->registration;
                $user->residence = $model->residence && $model->residence != $model->registration ? $model->residence : null;
                $user->passport = preg_replace('/\D+/', '', $model->passport);
                $user->passport_date = $model->passport_date;
                $user->passport_department = $model->passport_department;
                $model->itn = preg_replace('/\D+/', '', $model->itn);
                $user->itn = $model->itn ? $model->itn : null;
                $user->skills = $model->skills ? $model->skills : null;
                // $user->number = $model->number ? $model->number : (int) User::find()->max('number') + 1;
                $user->number = (int) User::find()->max('number') + 1;
                $user->recommender_id = $model->recommender_id ? $model->recommender_id : 95;
                $user->scenario = 'admin_creation';

                if (!$user->save()) {
                    print_r($user);
                    die();
                    throw new Exception('Ошибка создания пользователя!');
                }

                $types = [Account::TYPE_DEPOSIT, Account::TYPE_BONUS, Account::TYPE_SUBSCRIPTION];
                foreach ($types as $type) {
                    $account = new Account(['user_id' => $user->id, 'type' => $type, 'total' => 0]);
                    if (!$account->save()) {
                        throw new Exception('Ошибка создания счета пользователя!');
                    }
                }

                $provider = new Provider();
                $provider->user_id = $user->id;
                $provider->name = $model->name;
                $provider->field_of_activity = $model->field_of_activity;
                $provider->snils = $model->snils;
                $provider->legal_address = $model->legal_address;
                $provider->ogrn = $model->ogrn;
                $provider->site = $model->site;
                $provider->description = $model->description;
                $provider->categoryIds = $model->categoryIds;
                if (!$provider->save()) {
                    throw new Exception('Ошибка создания партнера!');
                }
                $model->id = $provider->id;

                $member = new Member();
                $member->user_id = $user->id;
                $member->partner_id = $model->partner;
                $member->become_provider = 1;
                if (!$member->save()) {
                    throw new Exception('Ошибка создания участника!');
                }

                $forgot = new Forgot();
                $forgot->user_id = $user->id;
                if (!$forgot->save()) {
                    throw new Exception('Ошибка создания уведомления для партнера!');
                }

                $transaction->commit();
            } catch (Exception $e) {
                $transaction->rollBack();
                throw new ForbiddenHttpException($e->getMessage());
            }

            $c_params = [
                'email' => $user->email,
            ];
            $candidate = Candidate::isCandidate($c_params);
            if ($candidate) {
                Email::tg_send('register-candidate', Yii::$app->params['superadminChatId'], [
                    'link' => $candidate
                ]);
            }
            
            if ($user->tg_id) Email::tg_send('forgot', $user->tg_id, ['url' => $forgot->url]);

            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Provider model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $provider = $this->findModel($id);
        $model = new ProviderForm([
            'isNewRecord' => false,
            'id' => $id,
            'user_id' => $provider->user->id,
            'tg_id' => $provider->user->tg_id,
            'name' => $provider->name,
            'disabled' => $provider->user->disabled,
            'email' => $provider->user->email,
            'phone' => $provider->user->phone,
            'ext_phones' => $provider->user->ext_phones,
            'firstname' => $provider->user->firstname,
            'lastname' => $provider->user->lastname,
            'patronymic' => $provider->user->patronymic,
            'birthdate' => mb_substr($provider->user->birthdate, 0, 10, Yii::$app->charset),
            'citizen' => $provider->user->citizen,
            'registration' => $provider->user->registration,
            'residence' => $provider->user->residence,
            'passport' => $provider->user->passport,
            'passport_date' => strtotime($provider->user->passport_date) > 0 ? date('Y-m-d', strtotime($provider->user->passport_date)) : '',
            'passport_department' => $provider->user->passport_department,
            'itn' => $provider->user->itn,
            'skills' => $provider->user->skills,
            'number' => $provider->user->number,
            'categoryIds' => $provider->categoryIds,
            'categories' => $provider->categories,
            'recommender_id' => $provider->user->recommender_id,
            'field_of_activity' => $provider->field_of_activity,
            'offered_goods' => $provider->offered_goods,
            'snils' => $provider->snils,
            'legal_address' => $provider->legal_address,
            'ogrn' => $provider->ogrn,
            'site' => $provider->site,
            'description' => $provider->description,
        ]);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            $provider = Provider::findOne($id);
            $provider->user->scenario = 'admin_creation';
            $provider->user->disabled = $model->disabled;
            $provider->user->tg_id = $model->tg_id;
            $provider->user->phone = $model->phone;
            $provider->user->ext_phones = $model->ext_phones;
            $provider->user->firstname = $model->firstname;
            $provider->user->lastname = $model->lastname;
            $provider->user->patronymic = $model->patronymic;
            $provider->user->birthdate = $model->birthdate;
            $provider->user->citizen = $model->citizen;
            $provider->user->registration = $model->registration;
            $provider->user->residence = $model->residence && $model->residence != $model->registration ? $model->residence : null;
            $provider->user->passport = preg_replace('/\D+/', '', $model->passport);
            $provider->user->passport_date = $model->passport_date;
            $provider->user->passport_department = $model->passport_department;
            $model->itn = preg_replace('/\D+/', '', $model->itn);
            $provider->user->itn = $model->itn ? $model->itn : null;
            $provider->user->skills = $model->skills ? $model->skills : null;
            $provider->user->number = $model->number ? $model->number : null;
            $provider->user->recommender_id = $model->recommender_id ? $model->recommender_id : null;
            $provider->user->save();

            $provider->name = $model->name;
            $provider->categoryIds = $model->categoryIds;
            $provider->field_of_activity = $model->field_of_activity;
            $provider->offered_goods = $model->offered_goods;
            $provider->snils = $model->snils;
            $provider->legal_address = $model->legal_address;
            $provider->ogrn = $model->ogrn;
            $provider->site = $model->site;
            $provider->description = $model->description;
            $provider->save();

            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Provider model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $user = $this->findModel($id)->user;
        $user->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Provider model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Provider the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Provider::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionAccount($id)
    {
        $provider = $this->findModel($id);
        $model = new AccountForm(['user_id' => $provider->user->id]);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            Account::swap(null, $provider->user->getAccount($model->account_type), $model->amount, $model->message);
            if ($model->amount < 0) {
                ProviderStock::setStockSum($provider->user->id, $model->amount);
            }
            
            return $this->redirect(['account', 'id' => $id, 'type' => $model->account_type]);
        }

        $accounts = [];
        $accountTypes = ArrayHelper::getColumn($provider->user->accounts, 'type');
        foreach ($accountTypes as $accountType) {
            $account = $provider->user->getAccount($accountType);
            if ($account) {
                $accounts[] = [
                    'type' => $account->type,
                    'name' => Html::makeTitle($account->typeName),
                    'account' => $account,
                    'dataProvider' => new ActiveDataProvider([
                        'id' => $account->type,
                        'query' => AccountLog::find()->where('account_id = :account_id', [':account_id' => $account->id]),
                        'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]],
                        'pagination' => [
                            'params' => array_merge($_GET, [
                                'type' => $account->type,
                            ]),
                        ],
                    ]),
                ];
            }
        }

        $accountType = Yii::$app->getRequest()->getQueryParam('type');
        if (!$provider->user->getAccount($accountType)) {
            $accountType = Account::TYPE_DEPOSIT;
        }

        return $this->render('account', [
            'provider' => $provider,
            'model' => $model,
            'accounts' => $accounts,
            'accountType' => $accountType,
        ]);
    }

    public function actionDownloadAgreementDelivery1($id)
    {
        return $this->downloadDocumentFile($id);
    }

    public function actionDownloadAgreementDelivery2($id)
    {
        return $this->downloadDocumentFile($id);
    }

    public function actionDownloadAgreementOneTimeDelivery($id)
    {
        return $this->downloadDocumentFile($id);
    }

    public function actionDownloadInvoice($id)
    {
        $provider = Provider::findOne($id);

        if (!$provider) {
            throw new NotFoundHttpException('Поставщик не найден.');
        }

        $templateName = preg_replace('/^download-/', '', $this->action->id);
        $templateFile = Template::getFileByName('provider', $templateName);
        if (!$templateFile) {
            throw new NotFoundHttpException('Шаблон не найден.');
        }
        $templateExtension = pathinfo($templateFile, PATHINFO_EXTENSION);
        $attachmentName = sprintf('%s-%d.%s', $templateName, (int) $provider->user->number, $templateExtension);

        $objectReader = \PHPExcel_IOFactory::createReader('Excel5');
        $objectExcel = $objectReader->load($templateFile);

        $parameters = Template::getUserParameters($provider->user);
        $objectExcel->setActiveSheetIndex(0);

        $objectWriter = \PHPExcel_IOFactory::createWriter($objectExcel, 'Excel5');

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $attachmentName .'"');
        header('Cache-Control: max-age=0');

        $objectWriter->save('php://output');

        exit();
    }

    public function actionDownloadAcceptanceReport($id)
    {
        $provider = Provider::findOne($id);

        if (!$provider) {
            throw new NotFoundHttpException('Поставщик не найден.');
        }

        $templateName = preg_replace('/^download-/', '', $this->action->id);
        $templateFile = Template::getFileByName('provider', $templateName);
        if (!$templateFile) {
            throw new NotFoundHttpException('Шаблон не найден.');
        }
        $templateExtension = pathinfo($templateFile, PATHINFO_EXTENSION);
        $attachmentName = sprintf('%s-%d.%s', $templateName, (int) $provider->user->number, $templateExtension);

        $objectReader = \PHPExcel_IOFactory::createReader('Excel5');
        $objectExcel = $objectReader->load($templateFile);

        $parameters = Template::getUserParameters($provider->user);

        $objectExcel->setActiveSheetIndex(0)
            ->setCellValue('H6', $parameters['currentDate']);

        $cells = ['B10', 'B11', 'B30'];
        foreach ($cells as $cell) {
            $value = $objectExcel->setActiveSheetIndex(0)->getCell($cell)->getValue();
            $objectExcel->setActiveSheetIndex(0)->setCellValue($cell, Template::parseTemplate($parameters, $value));
        }

        $products = Product::find()
            ->joinWith('provider')
            ->where('provider_id = :provider_id AND visibility <> 0 AND inventory > 0', [':provider_id' => $provider->id])
            ->all();

        $objectExcel->setActiveSheetIndex(0)
            ->insertNewRowBefore(17, count($products))
            ->removeRow(17 + count($products));

        $productTotal = 0;
        foreach ($products as $index => $product) {
            $objectExcel->setActiveSheetIndex(0)
                ->setCellValue('B' . (17 + $index), $index + 1)
                ->setCellValue('D' . (17 + $index), $product->name)
                ->setCellValue('H' . (17 + $index), sprintf('%.2f', $product->purchase_price))
                ->setCellValue('I' . (17 + $index), $product->inventory)
                ->setCellValue('K' . (17 + $index), sprintf('%.2f', $product->inventory * $product->purchase_price))
            ;
            $productTotal += $product->inventory * $product->purchase_price;
        }
        $parameters['productCount'] = count($products);
        $parameters['productTotal'] = sprintf('%.2f', $productTotal);

        $cells = [
            'B' . (30 + count($products)),
            'K' . (17 + count($products)),
            'B' . (19 + count($products)),
        ];
        foreach ($cells as $cell) {
            $value = $objectExcel->setActiveSheetIndex(0)->getCell($cell)->getValue();
            $objectExcel->setActiveSheetIndex(0)->setCellValue($cell, Template::parseTemplate($parameters, $value));
        }

        $objectWriter = \PHPExcel_IOFactory::createWriter($objectExcel, 'Excel5');

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $attachmentName .'"');
        header('Cache-Control: max-age=0');

        $objectWriter->save('php://output');

        exit();
    }

    public function actionDownloadStorageCard($id)
    {
        $provider = Provider::findOne($id);

        if (!$provider) {
            throw new NotFoundHttpException('Поставщик не найден.');
        }

        $templateName = preg_replace('/^download-/', '', $this->action->id);
        $templateFile = Template::getFileByName('provider', $templateName);
        if (!$templateFile) {
            throw new NotFoundHttpException('Шаблон не найден.');
        }
        $templateExtension = pathinfo($templateFile, PATHINFO_EXTENSION);
        $attachmentName = sprintf('%s-%d.%s', $templateName, (int) $provider->user->number, $templateExtension);

        $objectReader = \PHPExcel_IOFactory::createReader('Excel5');
        $objectExcel = $objectReader->load($templateFile);

        $parameters = Template::getUserParameters($provider->user);
        $objectExcel->setActiveSheetIndex(0);

        $objectWriter = \PHPExcel_IOFactory::createWriter($objectExcel, 'Excel5');

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $attachmentName .'"');
        header('Cache-Control: max-age=0');

        $objectWriter->save('php://output');

        exit();
    }

    protected function downloadDocumentFile($id)
    {
        $provider = Provider::findOne($id);

        if (!$provider) {
            throw new NotFoundHttpException('Поставщик не найден.');
        }

        $templateName = preg_replace('/^download-/', '', $this->action->id);
        $templateFile = Template::getFileByName('provider', $templateName);
        if (!$templateFile) {
            throw new NotFoundHttpException('Шаблон не найден.');
        }
        $templateExtension = pathinfo($templateFile, PATHINFO_EXTENSION);
        $attachmentName = sprintf('%s-%d.%s', $templateName, (int) $provider->user->number, $templateExtension);

        $templateProcessor = new TemplateProcessor($templateFile);
        $parameters = Template::getUserParameters($provider->user);

        /*$productQuery = Product::find()
            ->joinWith('provider')
            ->where('provider_id = :provider_id AND visibility <> 0 AND inventory > 0', [':provider_id' => $provider->id]);
        $parameters['productList'] = '';
        $productTotal = 0;
        foreach ($productQuery->each() as $index => $product) {
            $parameters['productList'] .= sprintf(
                "%d. %s %d шт. %.2f руб.\n",
                $index,
                $product->name,
                $product->inventory,
                $product->inventory * $product->purchase_price
            );
            $productTotal += $product->inventory * $product->purchase_price;
        }
        $parameters['productTotal'] = sprintf('%.2f руб.', $productTotal);*/

        foreach ($parameters as $name => $value) {
            $templateProcessor->setValue($name, $value);
        }

        return Yii::$app->response->sendFile(
            $templateProcessor->save(),
            $attachmentName
        );
    }
    
    public function actionDownloadTransferOfRights($id)
    {
        $head = StockHead::findOne($id);

        if (!$head) {
            throw new NotFoundHttpException('Поставка не найдена.');
        }

        $templateName = preg_replace('/^download-/', '', $this->action->id);
        $templateFile = Template::getFileByName('provider', $templateName);
        if (!$templateFile) {
            throw new NotFoundHttpException('Шаблон не найден.');
        }
        
        $templateExtension = pathinfo($templateFile, PATHINFO_EXTENSION);
        $attachmentName = sprintf('%s-%d.%s', $templateName, (int) $head->id, $templateExtension);

        $objectReader = \PHPExcel_IOFactory::createReader('Excel5');
        $objectExcel = $objectReader->load($templateFile);
        
        
        $objectExcel->setActiveSheetIndex(0)->setCellValue('T11', Yii::$app->formatter->asDate($head->date, 'php:d.m.Y'));
        
        $body = StockBody::find()->with('product')->where(['stock_head_id' => $head->id])->all();
        
        if ($body) {
            $total_summ = 0;
            $objectExcel->setActiveSheetIndex(0)->insertNewRowBefore(20, count($body) - 1);
            foreach ($body as $k => $val) {
                $objectExcel->setActiveSheetIndex(0)->mergeCells('C' . (19 + $k) . ':G' . (19 + $k));
                $objectExcel->setActiveSheetIndex(0)->mergeCells('H' . (19 + $k) . ':J' . (19 + $k));
                $objectExcel->setActiveSheetIndex(0)->mergeCells('Z' . (19 + $k) . ':AC' . (19 + $k));
                
                $objectExcel->setActiveSheetIndex(0)->setCellValue('B' . (19 + $k), $k + 1);
                $objectExcel->setActiveSheetIndex(0)->setCellValue('C' . (19 + $k), $val->product->name);
                $objectExcel->setActiveSheetIndex(0)->setCellValue('K' . (19 + $k), $val->measurement);
                $objectExcel->setActiveSheetIndex(0)->setCellValue('M' . (19 + $k), $val->tare);
                $objectExcel->setActiveSheetIndex(0)->setCellValue('O' . (19 + $k), $val->count);
                $objectExcel->setActiveSheetIndex(0)->setCellValue('T' . (19 + $k), number_format(sprintf("%01.2f", $val->summ), 2, '.', ' '));
                $objectExcel->setActiveSheetIndex(0)->setCellValue('Z' . (19 + $k), 'Без НДС');
                
                $total_summ += $val->total_summ;
            }
        }
        $objectExcel->setActiveSheetIndex(0)->setCellValue('F' . (36 + count($body) - 1), '"' . Yii::$app->formatter->asDate($head->date, 'php:d') . '"');
        $objectExcel->setActiveSheetIndex(0)->setCellValue('I' . (36 + count($body) - 1), Yii::$app->formatter->asDate($head->date, 'php:Y') . ' года');
        $objectExcel->setActiveSheetIndex(0)->setCellValue('G' . (36 + count($body) - 1), Yii::$app->formatter->asDate($head->date, 'php:F'));
        $objectExcel->setActiveSheetIndex(0)->setCellValue('B' . (29 + count($body) - 1), Sum::toStr($total_summ));
        $objectExcel->setActiveSheetIndex(0)->setCellValue('E' . (23 + count($body) - 1), Sum::toStr(count($body), false));
        
        $objectWriter = \PHPExcel_IOFactory::createWriter($objectExcel, 'Excel5');

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $attachmentName .'"');
        header('Cache-Control: max-age=0');

        $objectWriter->save('php://output');

        exit();
    }
    
    public function actionDownloadM17($id)
    {
        $provider = Provider::findOne($id);

        if (!$provider) {
            throw new NotFoundHttpException('Поставщик не найден.');
        }

        $templateName = preg_replace('/^download-/', '', $this->action->id);
        $templateFile = Template::getFileByName('provider', $templateName);
        if (!$templateFile) {
            throw new NotFoundHttpException('Шаблон не найден.');
        }
        
        $templateExtension = pathinfo($templateFile, PATHINFO_EXTENSION);
        $attachmentName = sprintf('%s-%d.%s', $templateName, (int) $provider->user->number, $templateExtension);

        $objectReader = \PHPExcel_IOFactory::createReader('Excel5');
        $objectExcel = $objectReader->load($templateFile);
        
        $objectExcel->setActiveSheetIndex(0);

        $objectWriter = \PHPExcel_IOFactory::createWriter($objectExcel, 'Excel5');

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $attachmentName .'"');
        header('Cache-Control: max-age=0');

        $objectWriter->save('php://output');

        exit();
    }
    
    public function actionDownloadM15($id)
    {
        $head = StockHead::findOne($id);

        if (!$head) {
            throw new NotFoundHttpException('Поставка не найдена.');
        }

        $templateName = preg_replace('/^download-/', '', $this->action->id);
        $templateFile = Template::getFileByName('provider', $templateName);
        if (!$templateFile) {
            throw new NotFoundHttpException('Шаблон не найден.');
        }
        
        $templateExtension = pathinfo($templateFile, PATHINFO_EXTENSION);
        $attachmentName = sprintf('%s-%d.%s', $templateName, (int) $head->id, $templateExtension);

        $objectReader = \PHPExcel_IOFactory::createReader('Excel5');
        $objectExcel = $objectReader->load($templateFile);
        
        $body = StockBody::find()->with('product')->where(['stock_head_id' => $head->id])->all();
        
        $objectExcel->setActiveSheetIndex(0)->setCellValue('F9', Yii::$app->formatter->asDate($head->date, 'php:d.m.Y'));
        if ($body) {
            $total_summ = 0;
            $objectExcel->setActiveSheetIndex(0)->insertNewRowBefore(18, count($body) - 1);
            foreach ($body as $k => $val) {
                $objectExcel->setActiveSheetIndex(0)->mergeCells('F' . (17 + $k) . ':J' . (17 + $k));
                $objectExcel->setActiveSheetIndex(0)->mergeCells('K' . (17 + $k) . ':O' . (17 + $k));
                $objectExcel->setActiveSheetIndex(0)->mergeCells('Q' . (17 + $k) . ':S' . (17 + $k));
                
                $objectExcel->setActiveSheetIndex(0)->setCellValue('F' . (17 + $k), $val->product->name);
                $objectExcel->setActiveSheetIndex(0)->setCellValue('P' . (17 + $k), $val->measurement);
                $objectExcel->setActiveSheetIndex(0)->setCellValue('X' . (17 + $k), $val->count);
                $objectExcel->setActiveSheetIndex(0)->setCellValue('Z' . (17 + $k), number_format(sprintf("%01.2f", $val->summ), 2, '.', ' '));
                
                $total_summ += $val->total_summ;
            }
        }
        $objectExcel->setActiveSheetIndex(0)->setCellValue('B' . (21 + count($body) - 1), 'на сумму ' . Sum::toStr($total_summ) . ', в том числе сумма НДС ______ руб. ______ коп.');
        
        $objectWriter = \PHPExcel_IOFactory::createWriter($objectExcel, 'Excel5');

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $attachmentName .'"');
        header('Cache-Control: max-age=0');

        $objectWriter->save('php://output');

        exit();
    }
    
    public function actionDownloadSalesInvoce($id)
    {
        $provider = Provider::findOne($id);

        if (!$provider) {
            throw new NotFoundHttpException('Поставщик не найден.');
        }

        $templateName = preg_replace('/^download-/', '', $this->action->id);
        $templateFile = Template::getFileByName('provider', $templateName);
        if (!$templateFile) {
            throw new NotFoundHttpException('Шаблон не найден.');
        }
        
        $templateExtension = pathinfo($templateFile, PATHINFO_EXTENSION);
        $attachmentName = sprintf('%s-%d.%s', $templateName, (int) $provider->user->number, $templateExtension);

        $objectReader = \PHPExcel_IOFactory::createReader('Excel5');
        $objectExcel = $objectReader->load($templateFile);
        
        $objectExcel->setActiveSheetIndex(0);

        $objectWriter = \PHPExcel_IOFactory::createWriter($objectExcel, 'Excel5');

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $attachmentName .'"');
        header('Cache-Control: max-age=0');

        $objectWriter->save('php://output');

        exit();
    }
    
    public function actionDownloadConsignmentNote($id)
    {
        $provider = Provider::findOne($id);

        if (!$provider) {
            throw new NotFoundHttpException('Поставщик не найден.');
        }

        $templateName = preg_replace('/^download-/', '', $this->action->id);
        $templateFile = Template::getFileByName('provider', $templateName);
        if (!$templateFile) {
            throw new NotFoundHttpException('Шаблон не найден.');
        }
        
        $templateExtension = pathinfo($templateFile, PATHINFO_EXTENSION);
        $attachmentName = sprintf('%s-%d.%s', $templateName, (int) $provider->user->number, $templateExtension);

        $objectReader = \PHPExcel_IOFactory::createReader('Excel5');
        $objectExcel = $objectReader->load($templateFile);
        
        $objectExcel->setActiveSheetIndex(0);

        $objectWriter = \PHPExcel_IOFactory::createWriter($objectExcel, 'Excel5');

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $attachmentName .'"');
        header('Cache-Control: max-age=0');

        $objectWriter->save('php://output');

        exit();
    }
    
    public function actionDownloadTorg12($id)
    {
        $provider = Provider::findOne($id);

        if (!$provider) {
            throw new NotFoundHttpException('Поставщик не найден.');
        }

        $templateName = preg_replace('/^download-/', '', $this->action->id);
        $templateFile = Template::getFileByName('provider', $templateName);
        if (!$templateFile) {
            throw new NotFoundHttpException('Шаблон не найден.');
        }
        
        $templateExtension = pathinfo($templateFile, PATHINFO_EXTENSION);
        $attachmentName = sprintf('%s-%d.%s', $templateName, (int) $provider->user->number, $templateExtension);

        $objectReader = \PHPExcel_IOFactory::createReader('Excel5');
        $objectExcel = $objectReader->load($templateFile);
        
        $objectExcel->setActiveSheetIndex(0);

        $objectWriter = \PHPExcel_IOFactory::createWriter($objectExcel, 'Excel5');

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $attachmentName .'"');
        header('Cache-Control: max-age=0');

        $objectWriter->save('php://output');

        exit();
    }
    
    public function actionDownloadWaybill($id)
    {
        $provider = Provider::findOne($id);

        if (!$provider) {
            throw new NotFoundHttpException('Поставщик не найден.');
        }

        $templateName = preg_replace('/^download-/', '', $this->action->id);
        $templateFile = Template::getFileByName('provider', $templateName);
        if (!$templateFile) {
            throw new NotFoundHttpException('Шаблон не найден.');
        }
        
        $templateExtension = pathinfo($templateFile, PATHINFO_EXTENSION);
        $attachmentName = sprintf('%s-%d.%s', $templateName, (int) $provider->user->number, $templateExtension);

        $objectReader = \PHPExcel_IOFactory::createReader('Excel5');
        $objectExcel = $objectReader->load($templateFile);
        
        $objectExcel->setActiveSheetIndex(0);

        $objectWriter = \PHPExcel_IOFactory::createWriter($objectExcel, 'Excel5');

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $attachmentName .'"');
        header('Cache-Control: max-age=0');

        $objectWriter->save('php://output');

        exit();
    }
    
    public function actionDownloadUpd($id)
    {
        $provider = Provider::findOne($id);

        if (!$provider) {
            throw new NotFoundHttpException('Поставщик не найден.');
        }

        $templateName = preg_replace('/^download-/', '', $this->action->id);
        $templateFile = Template::getFileByName('provider', $templateName);
        if (!$templateFile) {
            throw new NotFoundHttpException('Шаблон не найден.');
        }
        
        $templateExtension = pathinfo($templateFile, PATHINFO_EXTENSION);
        $attachmentName = sprintf('%s-%d.%s', $templateName, (int) $provider->user->number, $templateExtension);

        $objectReader = \PHPExcel_IOFactory::createReader('Excel5');
        $objectExcel = $objectReader->load($templateFile);
        
        $objectExcel->setActiveSheetIndex(0);

        $objectWriter = \PHPExcel_IOFactory::createWriter($objectExcel, 'Excel5');

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $attachmentName .'"');
        header('Cache-Control: max-age=0');

        $objectWriter->save('php://output');

        exit();
    }
    
    public function actionDownloadAcceptanceFeeAct($id)
    {
        $head = StockHead::findOne($id);

        if (!$head) {
            throw new NotFoundHttpException('Поставка не найдена.');
        }

        $templateName = preg_replace('/^download-/', '', $this->action->id);
        $templateFile = Template::getFileByName('provider', $templateName);
        if (!$templateFile) {
            throw new NotFoundHttpException('Шаблон не найден.');
        }
        
        $templateExtension = pathinfo($templateFile, PATHINFO_EXTENSION);
        $attachmentName = sprintf('%s-%d.%s', $templateName, (int) $head->id, $templateExtension);

        $objectReader = \PHPExcel_IOFactory::createReader('Excel5');
        $objectExcel = $objectReader->load($templateFile);
        
        $parameters = Template::getUserParameters($head->provider->user);
        $value_b13 = $objectExcel->setActiveSheetIndex(0)->getCell('B13')->getValue();
        $value_f8 = $objectExcel->setActiveSheetIndex(0)->getCell('F8')->getValue();
        
        $objectExcel->setActiveSheetIndex(0)->setCellValue('T11', Yii::$app->formatter->asDate($head->date, 'php:d.m.Y'));
        $objectExcel->setActiveSheetIndex(0)->setCellValue('B13', Template::parseTemplate($parameters, $value_b13));
        $objectExcel->setActiveSheetIndex(0)->setCellValue('F8', Template::parseTemplate($parameters, $value_f8));
        $objectExcel->setActiveSheetIndex(0)->setCellValue('F2', $parameters['fullName']);
        $objectExcel->setActiveSheetIndex(0)->setCellValue('F6', $parameters['fullName']);
        
        $body = StockBody::find()->with('product')->where(['stock_head_id' => $head->id])->all();
        
        if ($body) {
            $total_summ = 0;
            if (count($body) > 1) {
                $objectExcel->setActiveSheetIndex(0)->insertNewRowBefore(20, count($body) - 1);
            }
            
            foreach ($body as $k => $val) {
                $objectExcel->setActiveSheetIndex(0)->mergeCells('C' . (19 + $k) . ':G' . (19 + $k));
                $objectExcel->setActiveSheetIndex(0)->mergeCells('H' . (19 + $k) . ':J' . (19 + $k));
                $objectExcel->setActiveSheetIndex(0)->mergeCells('Z' . (19 + $k) . ':AC' . (19 + $k));
                
                $objectExcel->setActiveSheetIndex(0)->setCellValue('B' . (19 + $k), $k + 1);
                $objectExcel->setActiveSheetIndex(0)->setCellValue('C' . (19 + $k), $val->product->name);
                $objectExcel->setActiveSheetIndex(0)->setCellValue('K' . (19 + $k), $val->measurement);
                $objectExcel->setActiveSheetIndex(0)->setCellValue('M' . (19 + $k), $val->tare);
                $objectExcel->setActiveSheetIndex(0)->setCellValue('O' . (19 + $k), $val->count);
                $objectExcel->setActiveSheetIndex(0)->setCellValue('T' . (19 + $k), number_format(sprintf("%01.2f", $val->summ), 2, '.', ' '));
                $objectExcel->setActiveSheetIndex(0)->setCellValue('X' . (19 + $k), number_format(sprintf("%01.2f", $val->count * $val->summ), 2, '.', ' '));
                $objectExcel->setActiveSheetIndex(0)->setCellValue('AG' . (19 + $k), number_format(sprintf("%01.2f", $val->count * $val->summ), 2, '.', ' '));
                $objectExcel->setActiveSheetIndex(0)->setCellValue('Z' . (19 + $k), 'Без НДС');
                
                $total_summ += $val->total_summ;
            }
        }
        $objectExcel->setActiveSheetIndex(0)->setCellValue('X' . (19 + count($body)), $total_summ);
        $objectExcel->setActiveSheetIndex(0)->setCellValue('AG' . (19 + count($body)), $total_summ);
        $objectExcel->setActiveSheetIndex(0)->setCellValue('X' . (20 + count($body)), $total_summ);
        $objectExcel->setActiveSheetIndex(0)->setCellValue('AG' . (20 + count($body)), $total_summ);
        $objectExcel->setActiveSheetIndex(0)->setCellValue('F' . (36 + count($body) - 1), '"' . Yii::$app->formatter->asDate($head->date, 'php:d') . '"');
        $objectExcel->setActiveSheetIndex(0)->setCellValue('I' . (36 + count($body) - 1), Yii::$app->formatter->asDate($head->date, 'php:Y') . ' года');
        $objectExcel->setActiveSheetIndex(0)->setCellValue('G' . (36 + count($body) - 1), Yii::$app->formatter->asDate($head->date, 'php:F'));
        $objectExcel->setActiveSheetIndex(0)->setCellValue('B' . (29 + count($body) - 1), Sum::toStr($total_summ));
        $objectExcel->setActiveSheetIndex(0)->setCellValue('E' . (23 + count($body) - 1), Sum::toStr(count($body), false));
        $objectExcel->setActiveSheetIndex(0)->setCellValue('AG' . (33 + count($body) - 1), $parameters['shortName']);
        
        $objectWriter = \PHPExcel_IOFactory::createWriter($objectExcel, 'Excel5');

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $attachmentName .'"');
        header('Cache-Control: max-age=0');

        $objectWriter->save('php://output');

        exit();
    }
}
