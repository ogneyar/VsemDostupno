@echo off

rem скачивание dump'a БД bless

mysqldump --host=127.0.0.1 --password=root -u root --disable-keys --add-drop-table --default-character-set=utf8 --result-file=bless.sql bless

echo =========================================

@pause