<?
// \X\Helpers\Log::add($msg, $file, $agent);


//CEventLog::Add(array(
//        "SEVERITY" => "SECURITY",
//        /*
//        SEVERITY
//        - SECURITY
//        - ERROR
//        - WARNING
//        - INFO
//        - DEBUG
//        */
//        "AUDIT_TYPE_ID" => "MY_OWN_TYPE",
//        "MODULE_ID" => "main",
//        "ITEM_ID" => 123,
//        "DESCRIPTION" => "Какое-то описание",
//    ));

namespace X\Helpers
{
    class Log
    {
        
        const LOCKDIR = 'locks';
        
        public static function add($msg, $file, $agent='x', $rewrite=false) {

            if (!defined('S_P_LOG')) return;
            $dir = S_P_LOG.'/'.$agent;
            if (!file_exists($dir)) mkdir($dir, 0755, true);
            if (is_array($msg)) $msg = print_r($msg,true);
            $msg = '['.date('d-m-Y H:i:s').'] '.$msg."\n";
            $path = $dir.'/'.$file.'.txt';
            if ($rewrite) {
                if ($stream = fopen($path, 'w')) {
                    self::_write($stream, $msg);
                    fclose($stream);
                }
            } else {
                if ($stream = fopen($path, 'a')) {
                    self::_write($stream, $msg);
                    fclose($stream);
                }
            }
        }
        
        private static function _write ($stream, $data) {  
            flock($stream, LOCK_EX);
            fwrite($stream, $data);
            fflush($stream);
            flock($stream, LOCK_UN);
        }
        
        /*
        ставим блокировку ключ
        возвращет true если удалось получить блокировку
        */
        public static function lock($key) {  
            $lockFile = self::_getLockFile($key);
            if ($lockFile === false) {
                self::add('Попытка получить блокировку '.$key.' когда блокировки не работают', 'locks');
                return false; // неудалось получить блокировку
            }
            if (file_exists($lockFile)) {
                self::add('Неудачная попытка захватить блокировку '.$key, 'locks');
                return false; // неудалось захватить блокировку
            }
            $r = file_put_contents($lockFile,time());
            if ($r) return true;
            self::add('Неудалось предоставить блокировку '.$key, 'locks');
            return false;
        }
        
        public static function unlock($key) {  
            $lockFile = self::_getLockFile($key);
            if (file_exists($lockFile)) {
                unlink($lockFile);
                return true;
            } else return false;
        }
        
        public static function islocked($key) {  
            $lockFile = self::_getLockFile($key);
            return file_exists($lockFile);
        }
        
        private static function _getLockFile ($key) {  
            if (!defined('S_P_LOG')) return false;
            $dir = S_P_LOG.'/'.self::LOCKDIR;
            if (!file_exists($dir)) mkdir($dir);
            if (is_dir($dir)) {
                $lockFile = $dir.'/'.$key;
                return $lockFile;
            }
            return false;
        }
        
        
    }
}

