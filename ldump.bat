@echo off

rem загрузка БД bless в MySQL

mysql --host=127.0.0.1 --password=root -u root --default-character-set=utf8 bless < bless.sql

echo =========================================

@pause