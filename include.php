<?
define('BX_COMP_MANAGED_CACHE', true);
include(__DIR__.'/version.php');
define('XDEFINE_VERSION', $arModuleVersion['VERSION']);
define('XDEFINE_STARTMICROTIME',microtime(true));

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// init config
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$local_dir = '/local';
$root_dir = $_SERVER['DOCUMENT_ROOT'];

$arConstants = include($root_dir.$local_dir.'/x/config.php'); // загрузка конфигурации проекта

$arConstantsDefault = array(
        // окружение приложения
        'APPLICATION_ENV' => 'production',
        // версия реализации
        'APPLICATION_VERSION' => '0',
        // файл версионирования
        //'APPLICATION_VERSION_FILE' => '/.git/logs/HEAD',
        // директория приложения
        'P_' => $local_dir,
        // системный путь к корневой папке
        'S_' => $root_dir,
        // соль приложения
        'XDEFINE_SALT' => 'salt',
        'XDEFINE_CACHETIME' => 129600
    );

foreach ($arConstantsDefault as $name=>$val) if (!isset($arConstants[$name])) $arConstants[$name] = $arConstantsDefault[$name];

// версия имплементации приложения
if ($arConstants['APPLICATION_VERSION_FILE']
        && file_exists($_SERVER['DOCUMENT_ROOT'].$arConstants['APPLICATION_VERSION_FILE'])) {
    $arConstants['APPLICATION_VERSION'] = filemtime($_SERVER['DOCUMENT_ROOT'].$arConstants['APPLICATION_VERSION_FILE']);
} elseif (!isset($arConstants['APPLICATION_VERSION'])) $arConstants['APPLICATION_VERSION'] = 0;

// переопределение времени кэширования на сервере разработки
if ($arConstants['APPLICATION_ENV'] != 'production') {
    $arConstants['XDEFINE_CACHETIME'] = 120;
    if ($arConstants['APPLICATION_ENV'] == 'dev') $arConstants['XDEFINE_CACHETIME'] = 8;
}

// id инфоблоков
if (\Bitrix\Main\Loader::includeModule('iblock')) {
	$obCache = new \CPHPCache();
    if ($obCache->InitCache(
            $arConstants['XDEFINE_CACHETIME']*10,
            'iblocks_'.md5(serialize($arConstants)),
            '/x/app'
            )) {
        $arConstants = $obCache->GetVars();
    } elseif ($obCache->StartDataCache() ) {
        
        $res = CIBlock::GetList(
				Array(), 
				Array(
						'ACTIVE'=>'Y',
						'CHECK_PERMISSIONS' => 'N'
					),
				true
			);
        while($ar_res = $res->Fetch()) {
            if ($ar_res['CODE'] == '') continue;
            $constName = 'IDIB_'.strtoupper($ar_res['CODE']);
            if (isset($arConstants[$constName])) die($constName.' уже ожидается <pre>'.print_r($arConstants,true).'</pre>');
            $arConstants[$constName] = $ar_res['ID'];
        }
        
        $obCache->EndDataCache($arConstants);
    }
}

// определяем константы
foreach ($arConstants as $constName=>$val) {
    //if (defined($constName)) die($constName.' defined'); // иначе невозоможно предопределить APPLICATION_ENV
    define($constName, $val);
}

// Режим работы "продакшен если что-то пошло не так"
defined('APPLICATION_ENV') || define('APPLICATION_ENV', 'production');

// дефайн путей
// P_* путь к папке проекта - как правило local
define('P_X',           P_.'/x'); // X - имплементации модели и сущностей
define('P_INTERFACE',   P_.'/interface'); // интерфейс AJAX и REST сервисов
define('P_LAYOUT',    	P_.'/templates/.default'); // шаблон шаблонов
define('P_MEDIA',    	P_.'/sources'); // медифайлы
define('P_CSS',     	P_MEDIA.'/css'); // папка стилей
define('P_JS',    		P_MEDIA.'/js'); // папка скриптов
define('P_IMAGES',    	P_MEDIA.'/img'); // папка с изображениями (например бэкграунды и банеры)
define('P_PICTURES',  	P_MEDIA.'/pictures'); // папка с картинками (напр. элементы интерфейса и иконки)
define('P_INCLUDES',  	P_.'/includes'); // папка с другими подключаемыми файлами (svg и tmpl используся X\Helpers\Html)
define('P_LOG',         P_.'/logs'); // каталог логов
define('P_SOURCESDUMP', P_.'/__dump'); // свалка данных

// остальные пути
define('P_SVG',  	    P_INCLUDES.'/svg'); // папка с svg (используется хелпером)
define('P_TMPL',  	    P_INCLUDES.'/tmpl'); // микрошаблоны (используется хелпером)
define('P_CONTENT',  	P_INCLUDES.'/content'); // Контентные вставки

// S_P_* абсолютные системные пути
define('S_P_X',             S_.P_X);
define('S_P_INTERFACE',    	S_.P_INTERFACE);
define('S_P_LAYOUT',    	S_.P_LAYOUT);
define('S_P_INCLUDES',  	S_.P_INCLUDES);
define('S_P_SVG',  	        S_.P_SVG);
define('S_P_TMPL',  	    S_.P_TMPL);
define('S_P_CONTENT',  	    S_.P_CONTENT);
define('S_P_CSS',  	        S_.P_CSS);
define('S_P_JS',  	        S_.P_JS);

define('S_P_LOG',  	        S_.P_LOG);
define('S_P_SOURCESDUMP',  	S_.P_SOURCESDUMP);


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// autoload
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (APPLICATION_ENV != 'production'
        || !file_exists(S_P_X.'/config_cache/autoload')) {
    
    Bitrix\Main\Loader::registerAutoLoadClasses('x.api',  array(
            // абстрация
            '\X\Abstraction\App'                        => 'lib/abstraction/app.php',
            '\X\Abstraction\Singleton'                  => 'lib/abstraction/singleton.php',
            '\X\Abstraction\HLBModel'                   => 'lib/abstraction/hlbmodel.php',
            '\X\Abstraction\IBModel'                    => 'lib/abstraction/ibmodel.php',
            '\X\Abstraction\EntityTable'                => 'lib/abstraction/entitytable.php',
            '\X\Abstraction\Users'                      => 'lib/abstraction/users.php',
            '\X\Abstraction\UsersModel'                 => 'lib/abstraction/usersmodel.php',
            '\X\Abstraction\CurrentUser'                => 'lib/abstraction/currentuser.php',
            
            // продтомодель
            '\X\Abstraction\Protomodel\Filestorage'     => 'lib/abstraction/protomodel/filestorage.php',
            '\X\Abstraction\Protomodel\Stringstorage'   => 'lib/abstraction/protomodel/stringstorage.php',
            
            // хелперы
            '\X\Helpers\Debug'                          => 'lib/helpers/debug.php',
            '\X\Helpers\HLReference'                    => 'lib/helpers/hlreference.php',
            '\X\Helpers\Html'                           => 'lib/helpers/html.php',
            '\X\Helpers\Log'                            => 'lib/helpers/log.php',
        ));
    
    $arAutoload = [];
    // модель
    $arFilesModel = glob(S_P_X.'/model/[abcdefghijklmnopqrstuvwxyz]*.php');
    foreach ($arFilesModel as $file) {
        $arAutoload['\\Model\\'.ucfirst(str_replace('.php','',basename($file)))] = 
                str_replace(S_,'',$file); // ! Внимание - наивное предположение
    }
    
    // сущности
    $arFilesEntity = glob(S_P_X.'/entities/[abcdefghijklmnopqrstuvwxyz]*.php');
    foreach ($arFilesEntity as $file) {
        $entety = '\\Entity\\'.ucfirst(str_replace('.php','',basename($file)));
        $arAutoload[$entety] = str_replace(S_,'',$file); // ! Внимание - наивное предположение
    }
    
	// на продакшене сохраняем в папку 
    if (APPLICATION_ENV == 'production') {
        file_put_contents(S_P_X.'/config_cache/autoload',serialize($arAutoload));
    }
	
} else {
    $arAutoload = unserialize(file_get_contents(S_P_X.'/config_cache/autoload'));
}

if (count($arAutoload)) Bitrix\Main\Loader::registerAutoLoadClasses(null, $arAutoload);

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// init other
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// отладчик
if (APPLICATION_ENV == 'dev' || APPLICATION_ENV == 'combo') { // сервер разработки или комбо режим
    require_once(__DIR__.'/classes/general/xdebug.php');
    if (ERROR_404 == 'Y') { // если Y - Проверка на сваленный ресурс
        require_once(__DIR__.'/xsdm.php'); // обработчик свалки ресурсов
    }
} else { // если не сервер разработки - поднимаем заглушку на дебаг
    require_once(__DIR__.'/classes/general/xdebug_dummy.php');
}

// экземпляр приложения
if (!file_exists(S_P_X.'/app.php')) {
    file_put_contents(S_P_X.'/app.php','<?class App extends \X\Abstraction\App {}');
}

require_once(S_P_X.'/app.php');
\App::getInstance();


// Перехватчики
if (file_exists(S_P_X.'/handlers.php')) {
    require_once(S_P_X.'/handlers.php');
    $arMethods = get_class_methods('XHandlers');
    
    foreach ($arMethods as $method) {
        $debris = explode('_',$method);
        if (count($debris) == 3
                && $debris[2]>0
            ) AddEventHandler($debris[0], $debris[1], ['XHandlers',$method], (int)$debris[2]);
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Exception
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//class CBitrixCloudException extends Exception
//{
//	protected $error_code = "";
//	protected $debug_info = "";
//	public function __construct($message = "", $error_code = "", $debug_info = "")
//	{
//		parent::__construct($message);
//		$this->error_code = $error_code;
//		$this->debug_info = $debug_info;
//	}
//	final public function getErrorCode()
//	{
//		return $this->error_code;
//	}
//	final public function getDebugInfo()
//	{
//		return $this->debug_info;
//	}
//}
