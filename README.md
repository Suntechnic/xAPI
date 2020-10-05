# xAPI для bitrix

## version 0.2.0 (beta)

### не предназначено для практического применения!

## Пример установки

Установите модуль из git:
```
cd bitrix/modules/;  
mkdir x.api;  
cd x.api/;  
git clone https://github.com/Suntechnic/xAPI .
```
 
Добавить в /local/php_interface/init.php первой строкой:
```php
<?  
// подключение xAPI  
// https://github.com/Suntechnic/xAPI/blob/master/README.md  
\Bitrix\Main\Loader::includeModule('x.api');
```


## В файле /local/x/config.php можно переопределить ряд констант
Разместите в нём следующий код возвращающий массив констант:  
```php
<?  
return array(  
        // окружение приложения  
        'APPLICATION_ENV' => 'dev',  
        // версия реализации  
        'APPLICATION_VERSION' => '0',  
        // файл версионирования  
        //'APPLICATION_VERSION_FILE' => '/.git/logs/HEAD',  
        // соль приложения  
        'XDEFINE_SALT' => 'salt',  
        'XDEFINE_CACHETIME' => 129600  
    );
```  

## Список констант

APPLICATION_ENV - состояне приложения - [dev|combo|production]. В состояниях dev и combo загружается отладчик  
  
### Константы путей

P_ - путь к родительскому каталогу xAPI  
S_ - абсолютный путь к DOCUMENT_ROOT сервера  
  
P_X - каталог xAPI  
P_INTERFACE - интерфейс AJAX и REST сервисов приложения  
P_LAYOUT - каталог шаблона шаблонов  
P_MEDIA - каталог со статичными файлами  
P_CSS - каталог стилей  
P_JS - каталог скриптов  
P_IMAGES - каталог с изображениями (например бэкграунды и банеры)  
P_PICTURES - каталог с картинками (напр. элементы интерфейса и иконки)  
P_INCLUDES - каталог с другими подключаемыми файлами (svg и tmpl используся X\Helpers\Html)  
P_LOG - каталог логов xAPI  
P_SOURCESDUMP - каталог со "свалкой" ресурсов  
P_SVG - каталог с svg (используется хелпером)  
P_TMPL - каталог микрошаблонов (используется хелпером)  
P_CONTENT - каталог содержащий контентные вставки  

Большинство констант имеют копии вида S_константа - указывающие абсолютный путь:  
S_P_X  
S_P_INTERFACE  
S_P_LAYOUT  
S_P_CSS  
S_P_JS  
S_P_INCLUDES  
S_P_LOG  
S_P_SOURCESDUMP  
S_P_SVG  
S_P_TMPL  
S_P_CONTENT  

### Другие внутренние константы  
XDEFINE_VERSION - версия xAPI  
XDEFINE_SALT - соль используемая xAPI  
XDEFINE_CACHETIME - время внутренних кэшей в секундах. По умолчанию 36800 в режиме production, 8 в режиме dev и 90 во всех иных случаях
  
Кроме того автоматически объявляются проектозависимые константы:  
IDIB_{символьныйКодИнфоблока} - id инфоблока
