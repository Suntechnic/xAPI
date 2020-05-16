<?
namespace X\Abstraction {
    abstract class App {
        
        static $instance;
    
        public static function getInstance() {
            if (!isset(static::$instance)) {
                static::$instance = new static();
            }
            
            return static::$instance;
        }
        
        protected final function __clone() {}
        protected final function __wakeup() {}
        protected function __construct()
        {
            AddEventHandler('main', 'OnBeforeProlog', array('App','init'), 1);
            
            \XDebug::log($_REQUEST,'$_REQUEST');
            \XDebug::log($_SERVER,'$_SERVER');
            //
            $cookie_name = \COption::GetOptionString('main', 'cookie_name', 'BITRIX_SM');
            $this->data = [
                    'env' => [
                        'state' => APPLICATION_ENV,
                        'serverinterface' => P_INTERFACE.'/',
                        'css' => P_CSS.'/',
                        'js' => P_JS.'/',
                        'pictures' => P_PICTURES.'/',
                        'images' => P_IMAGES.'/',
                        'lang' => LANGUAGE_ID,
                        'cookie_name' => $cookie_name,
                        'last_login' => $_COOKIE[$cookie_name.'_LOGIN']
                    ]
                ];
            
            \XDebug::log($this->data, 'App construct');
            
            return $this;
        }
        
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
        
        protected $register=[];
        protected $iterators=[]; // интераторы добавления, чтобы соблюсти очередь
        
        protected $data=[];
        
        public static function init() {}
        
        public function getData() {
            return $this->data;
        }
        
        /*
         * добавляет данные в массив
         * если ключ уже существует - он не будет перезаписан
        */
        public function addData($arData) {
            // добавить лок
            foreach ($arData as $key=>$val) {
                if (isset($this->data[$key])) {
                    if (is_array($this->data[$key])
                            && is_array($val)
                        ) $this->data[$key] = array_merge($val,$this->data[$key]);
                } else $this->data[$key] = $val;
            }
            return $this->data;
        }
        #
        
        /*
         * добавляет данные в массив
         * в существующие ключи записываются новые значения
        */
        public function upgradeData($arData) {
            // добавить лок
            foreach ($arData as $key=>$val) {
                if (isset($this->data[$key])) {
                    if (is_array($this->data[$key])
                            && is_array($val)
                        ) $this->data[$key] = array_merge($this->data[$key],$val);
                } else $this->data[$key] = $val;
            }
            return $this->data;
        }
        #
        
        /*
         * Проверяет был ли добавлени уже этот файл/контент
         * и возвращает True, если был, и False если нет
         * служит для блокироваиня повторного добавления
        */
        private function _register(
                string $shash,
                bool $uid=false
            )
        {
            if (!$uid) $shash = md5($shash);
            if ($this->register[$shash]) return true;
            $this->register[$shash] = true;
            return false;
        }
        #
        
        /*
         * Добавляет контент как AddViewContent, обеспечивая порядок вывода
        */
        private function _addContent ($mark, $str) {
            if (!$this->iterators[$mark]) {
                $this->iterators[$mark] = 1;
            } else $this->iterators[$mark]++;
            
            global $APPLICATION; $APPLICATION->AddViewContent($mark,$str,$this->iterators[$mark]);
        }
        #
        
        /*
         * добавляет стиль
        */
        public function addCssSource($path,$place='head',$attrs=[]) { if($this->_register($path)) return;
            if (!strpos($path,'?')) $path = $path.'?v='.(APPLICATION_ENV=='dev'?time():APPLICATION_VERSION);
            $str = '<link href="'.$path.'" type="text/css"  rel="stylesheet"';
            if ($attrs) $str.= ' '.\X\Helpers\Html::attrs($attrs);
            $str.= ' />';
            
            $this->_addContent('x_'.$place.'_css', $str);
        }
        #
        /*
         * добавляет стиль
        */
        public function addCssContetn($content,$place='head',$attrs=[]) { if($this->_register($content)) return;
            
        }
        #
        
        /*
         * добавляет js
        */
        public function addJsSource($path,$place='head',$attrs=[]) { if($this->_register($path)) return;
            if (!strpos($path,'?')) $path = $path.'?v='.(APPLICATION_ENV=='dev'?time():APPLICATION_VERSION);
            $str = '<script type="text/javascript" src="'.$path.'"';
            if ($attrs) $str.= ' '.\X\Helpers\Html::attrs($attrs);
            $str.= '></script>';
            
            $this->_addContent('x_'.$place.'_js', $str);
        }
        #
        
        /*
         * добавляет js
        */
        public function addJsContent($path,$place='head',$attrs=[]) { if($this->_register($path)) return;
            
        }
        #
        
        
        /*
         * добавляет html
        */
        public function addHtmlContent($content,$place='head',$attrs=[]) { if($this->_register($content)) return;
            ob_start();
            include(S_.$content);
            $str = ob_get_contents();
            ob_end_clean();
            
            // порядок не важен
            global $APPLICATION; $APPLICATION->AddViewContent('x_'.$place.'_html',$str);
        }
        #
        
        /*
         * добавляет json
        */
        public function addJsonContent($content,$place='head',$attrs=[]) { if($this->_register($content)) return;
            
        }
        #
    }
}

