### Средство создания обновления для CMS базы данных

Данный проект содержит набор скриптов, которыми можно управлять базой, сгружать, выполнять скрипты, обновлять.

Все исходные настройки заполняются в .env файле стилем `.ini` файла

#### Основные разделы .env
+ [MYSQL] - в данном разделе хранятся переменные подключения к базе данных
+ [NEEDED] - загрузка необходимых таблиц
+ [USE_COLUMN] - использование колонок, по котором сгружается таблица. По умолчанию: `id`
+ [CUSTOM_REQUESTS] - запросы которые можно выполнить 
+ [UPDATE_ORDER] - обновление ресурсов

#### Использование разделов

+ [MYSQL]
+ + HOST = "host"
+ + USER = "user"
+ + PASSWORD = "password"
+ + DATABASE = "database"
######
+ [NEEDED]
+ + TABLES = ""
######
+ [EXCEPTION]
+ + TABLES = ""
######
+ [USE_COLUMN]
+ + TABLE1 = "id"
######
+ [ORDER_BY]
+ + TABLE1 = "\`col1\` ASC"
+ + TABLE2 = "\`col2\` DESC, \`col1\` ASC"
######
+ [CUSTOM_REQUESTS]
+ + TASK = "QUERY"
######
+ [UPDATE_ORDER]
+ + TABLE1 = "id"
+ + TABLE2 = "id,title"