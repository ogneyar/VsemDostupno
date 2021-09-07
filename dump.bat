@echo off

rem скачивание dump'a БД u1366210_bless

"C:/Program Files/MySQL/MySQL Server 5.5/bin/mysqldump" --host=127.0.0.1 --password= -u root --disable-keys --add-drop-table --default-character-set=utf8 --result-file=u1366210_bless.sql u1366210_bless

echo =================SUCCESS========================

@pause