<?php

use kartik\helpers\Html;

      
$constants = require(__DIR__ . '/../../../../../config/constants.php');
$web = $constants['WEB'];


/* @var $this yii\web\View */
$this->title = 'Регистрация';
$this->params['breadcrumbs'] = [$this->title];
?>

<?php echo Html::pageHeader(Html::encode($this->title), '', ['id' => 'page-header-register-telegram']) ?>

<?php
// $get = $request->get();

if ($get && $get["agent"]) { // либо member, либо provider
?>
    <div style="text-align:center;">
        <div>
            <p>Для возможности получать уведомления обо всех операциях с вашим счётом необходимо подключиться к нашему телеграм-боту.</p>
            <p>Для этого нажмите кнопку ниже. После этого Вам будет предложенно перейти в телеграм, а в телеграм Вам бот подскажет что делать далее.</p>
            <br/>            
        </div>
        <div>
            <a href="https://t.me/bud_zdorov_rus_bot?start=<?php echo $get["agent"]; ?>">
                <!-- <button>Подключиться к боту</button> -->   
                <?= Html::submitButton('Подключиться к боту', ['class' => 'btn btn-primary pull-center', 'name' => 'register-bot-button', 'id' => 'register-bot-button', 'disabled' => false]) ?>
            </a>
            <br/>            
            <br/>            
            <br/>            
        </div>
        <div>
            <?php 
            if ($get["agent"] == "member") {
                ?>
                <a href="<?=$web?>/profile/register?tg=false">
                    <!-- <button>Регистрация без уведомлений</button> -->
                    <?= Html::submitButton('Регистрация без уведомлений', ['class' => 'btn btn-primary pull-center', 'name' => 'register-n-button', 'id' => 'register-n-button', 'disabled' => false]) ?>
                </a>
            <?php 
            }else if ($get["agent"] == "provider") {
                ?>
                <a href="<?=$web?>/profile/register-provider?tg=false">
                    <!-- <button>Регистрация без уведомлений</button> -->
                    <?= Html::submitButton('Регистрация без уведомлений', ['class' => 'btn btn-primary pull-center', 'name' => 'register-n1-button', 'id' => 'register-n1-button', 'disabled' => false]) ?>
                </a>
            <?php 
            }
            ?>
            <br/>            
        </div>
    </div>
<?php
}else {
    echo "<p>Отсутствует параметр agent.</p>"; 
}

?>