<?
namespace X\Helpers
{
    class HLReference
    {
        static $instances = [];
        public static function getInstance($id) {
            if (!isset(static::$instances[$id])) {
                static::$instances[$id] = new static($id);
            }
            return static::$instances[$id];
        }
        
        public static function getReference($id)
        {
            return self::getInstance($id);
        }
        
        protected final function __construct($id) {
            \Bitrix\Main\Loader::includeModule('highloadblock');
            
            if (is_numeric($id)) {
                $hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById($id)->fetch();
            } elseif ($id != '') {
                $hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getList(array(
                        'filter' => array('=TABLE_NAME' => $id)
                    ))->fetch();
            }
            
            $entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
            $entity_data_class = $entity->getDataClass();
            $rsData = $entity_data_class::getList(array(
               'select' => array('*')
            ));
            while($arRow = $rsData->Fetch()) {
                $arData[] = $arRow;   
            }
            $this->data = $arData;
        }
        
        protected final function __clone() {}
        protected final function __wakeup() {}
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
        protected $data = [];
        protected $maps = [];
    
        
        
        //protected final function __clone() {}
        //protected final function __wakeup() {}
        
        /* Возвращает список */
        public function data ($key=false)
        {
            $arData = $this->data;
            if ($key) {
                $kData = array();
                foreach ($arData as $row) {
                    $kData[$row[$key]] = $row;
                }
                $arData = $kData;
            }
            return $arData;
        }
        
        /* Возвращает dict по ключу $key */
        public function By ($key,$column=false)
        {
            $arData = $this->data;
            if ($column) return array_column($arData,$column,$key);
            
            if (!$this->maps[$key]) {
                $this->maps[$key] = array_flip(
                        array_map(
                                function ($e) use ($key) {return $e[$key];},
                                $arData
                            )
                    );
            }
            $arDict = array_map(
                    function ($i) use ($arData) {return $arData[$i];},
                    $this->maps[$key] 
                );
            
            return $arDict;
        }
        
        /* фильтрует данные по значениям $val ключа $key */
        public function Select ($key,$val)
        {
            if (!is_array($val)) {
                $arr = array($val);
            } elseif (count($arr) > 1) {
                $arr = array_unique($val);
            }
            
            $arDict = array_intersect_key($this->By($key), array_flip($arr));
            
            return $arDict;
        }
    }
}

