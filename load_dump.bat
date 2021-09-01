@echo off

rem загрузка БД u1366210_bless в MySQL

"C:/Program Files/MySQL/MySQL Server 5.5/bin/mysql" --host=127.0.0.1 --password= -u root --default-character-set=utf8 u1366210_bless < u1366210_bless.sql

echo ================SUCCESS=========================

@pause