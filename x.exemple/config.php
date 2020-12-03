<?
return array(
        // x
        'APPLICATION_ENV' => 'dev',
        'APPLICATION_VERSION' => '0',
        'APPLICATION_VERSION_FILE' => '/.git/logs/HEAD',
        'APPLICATION_LOGLEVEL' => 0,
        // pathes
        'XDEFINE_DIRS' => [
                'X'             => '/x',
                //'INTERFACE'     => '/interface', // интерфейс AJAX и REST сервисов
                //'LAYOUT'    	=> '/templates/.default', // шаблон шаблонов
                //'MEDIA'    	    => '/sources', // медифайлы
                //'CSS'     	    => '/css', // папка стилей
                //'FONTS'     	=> '/fonts', // папка с шрифтами
                //'JS'    		=> '/js', // папка скриптов
                //'IMAGES'    	=> '/img', // папка с изображениями (например бэкграунды и банеры)
                //'INCLUDES'  	=> '/includes', // папка с другими подключаемыми файлами (svg и tmpl используся X\Helpers\Html)
                //'LOG'           => '/logs', // каталог логов
                //'SOURCESDUMP'   => '/__dump', // свалка данных
            ], 
        // ohter
        'XDEFINE_SALT' => 'salt',
        'XDEFINE_CACHETIME' => 129600
    );