<?
class XDebug
{
    
    protected static $log = [];
    protected static $ajax = false;
    protected static $mark = 'xdebug';
    protected static $logRow = false;
    protected static $timeStep = false;
    
    
    protected static $init = false;
    
    ////////////////////////////////////////////////////////////////////////////
    
    /*
     * Добавляем сообщение в лог
     */
    public static function log ($msg,$title=false) { self::init();
        self::normalize($msg);
        $micronow = microtime(true);
        $i = (++self::$logRow).' - '.round($micronow-XDEFINE_STARTMICROTIME,1).' - '.round($micronow-self::$timeStep,4);
        self::$timeStep = $micronow;
        if ($title) $i.= ':'.$title;
        self::$log[$i] = $msg;
    }
    
    /*
     * выбрасывает отладочный блок, и завершает работу приложения
     */
    public static function stop ($msg) {
        $bt = debug_backtrace();
        self::step($msg,$bt[0]);
        $arLog = self::getLog();
        die('<div class="xdebug__output"><h2>'.self::$mark.'</h2><pre>'.print_r($arLog,true).'</pre></div>');
    }
    
    /*
     * тоже что и стоп, но работа приложения не завершается
     */
    public static function step ($msg,$bti=false) { self::init(); if (self::$ajax) return; // функция не работает в режиме ajax
        if (!$bti) {
            $bt = debug_backtrace();
            $bti = $bt[0];
        }
        self::normalize($msg);
        print('<div class="xdebug__output"><h2>'.self::$mark.'</h2><p>'.$bti['file'].':'.$bti['line'].'</p><pre>'.print_r((array)$msg,true).'</pre></div>');
    }
    
    /*
     * возвращает логи в виде массива
     */
    public static function getLog () {
        return self::$log;
    }
    ////////////////////////////////////////////////////////////////////////////
    
    //private static function output () {
    //    if (self::$init) return;
    //    self::$init = true;
    //    if ($_REQUEST['appajax'] || $_REQUEST['ajax']) return;
    //    
    //    AddEventHandler('main', 'OnEndBufferContent', array('XDebug','output'), 9999);
    //}
    
    public static function outputLog (&$content) {
        if (count(self::$log) == 0) return;
        
        if (self::$ajax) {
            
        } else { // только если не ajax
            $debugTExt =
                    '<script id="'.self::$mark.'_data" rtype="application/json">'
                    .'var XDEBUG_LOG = '.json_encode(self::$log,true)
                    .'</script>';
            $pos = strripos($content,'</html>');
            if ($pos) {
                $content = str_replace('</html>',$debugTExt.'</html>',$content);
            } else {
                //$content.= $debugTExt;
            }
        }
        
        
        return $content;
    }
    
    /*
     * Вызывается любым методом при старте
     *
     */
    
    private static function init () {
        if (self::$init) return;
        self::$init = true;
        self::$logRow = 0;
        self::$timeStep = XDEFINE_STARTMICROTIME;
        
        if (
                $_REQUEST['ajax'] // битрикс сообщает что он в ajax режиме
                || $_REQUEST['AJAX_CALL'] // битрикс такое сообщает
                || ADMIN_SECTION === true // админка
            ) self::$ajax = true; // работаем в режиме ajax
        
        AddEventHandler('main', 'OnEndBufferContent', array('XDebug','outputLog'), 9999);
    }
    
    private static function normalize (&$msg) {
        if (!is_string($msg) || !is_numeric()) {
            $msg = (array)$msg;
        }
    }
}

