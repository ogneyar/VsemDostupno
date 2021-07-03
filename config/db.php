<?php
$host = getenv ("DB_HOST");
if ($host) {
    $name = getenv ("DB_NAME");
    $login = getenv ("DB_LOGIN");
    $pass = getenv ("DB_PASS");
}else {
    $host = "localhost";
    $name = "u1366210_bless";
    $login = "u1366210_bless";
    $pass = "u1366210_bless";
}

return [
        'class' => 'yii\db\Connection',
        'dsn' => "mysql:host={$host};dbname={$name}",
        'username' => $login,
        'password' => $pass,
        'charset' => 'utf8',
    ];

    