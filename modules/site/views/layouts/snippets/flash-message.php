<?php

$message = Yii::$app->session->getFlash('message')
?>

<?php if ($message): ?>
    <script type="text/javascript">
        $(function () {
            WidgetHelpers.showFlashDialog('<?= $message ?>');
        });
    </script>
<?php endif ?>
