<?
namespace X\Abstraction {
    abstract class Singleton {
        
        static $instances = [];
        public static function getInstance() {
            $class = get_called_class();
            
            if (!isset(static::$instances[$class])) {
                static::$instances[$class] = new static($class);
            }
            return static::$instances[$class];
        }
        
        protected final function __clone() {}
        protected final function __wakeup() {}
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
    }
}