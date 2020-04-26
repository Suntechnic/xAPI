<?
// \X\Helpers\Log::add($msg, $file, $agent);
namespace X\Helpers
{
    class Log
    {
        
        const LOCKDIR = 'locks';
        
        public static function add($msg, $file, $agent='x', $rewrite=false) {  
            if (!defined('S_P_LOG')) return;
            $dir = S_P_LOG.'/'.$agent;
            if (!file_exists($dir)) mkdir($dir);
            if (is_array($msg)) $msg = print_r($msg,true);
            $msg = '['.date('d-m-Y H:i:s').'] '.$msg;
            if ($rewrite) {
                file_put_contents($dir.'/'.$file.'.txt',$msg."\n");
            } else file_put_contents($dir.'/'.$file.'.txt',$msg."\n",FILE_APPEND);
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

