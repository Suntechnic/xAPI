<?
namespace X\Abstraction {
    abstract class HLBModel {
        
        static $instances = [];
        public static function getInstance() {
            if (!isset(static::$instances[static::IDHLB])) {
                static::$instances[static::IDHLB] = new static(static::IDHLB);
            }
            return static::$instances[static::IDHLB];
        }
        
        protected function __construct($uid)
        {
            if (!$this::IDHLB) die('Invalid HLBlock Id: '.$this::IDHLB);
            \Bitrix\Main\Loader::includeModule('highloadblock');
            $this->hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById($this::IDHLB)->fetch();
            $this->entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($this->hlblock);
            $this->entity_data_class = $this->entity->getDataClass();
        }
        
        protected final function __clone() {}
        protected final function __wakeup() {}
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
        protected $hlblock;
        protected $entity;
        protected $entity_data_class;
        
        // возвращает ID инфоблока
        public function getId() {
            return $this::IDHLB;
        }
        #
        
        // возвращает сущность
        public function getEntity() {
            return $this->entity;
        }
        #
        
        // возвращает ДатаКласс
        public function getDataClass() {
            return $this->entity_data_class;
        }
        #
        
        // возвращает список элементов
        public function getList () {
            return $this->getDict();
        }
        #
        
        // возвращает один первый элеметм
        public function getElement () {
            //$entity_data_class = $this->entity->getDataClass();
            $entity_data_class = $this->entity_data_class;
            $rsData = $entity_data_class::getList(array(
               "select" => array("*"),
               "filter" => $this->getFilter(),
               "order" => $this->getOrder()
            ));
            if ($arRow = $rsData->Fetch()) return $arRow;   
            return false;
        }
        #
        
        // возвращает словать элементов по указанному ключу
        public function getDict ($key=false,$arSelect=['*']) {
            //$entity_data_class = $this->entity->getDataClass();
            $entity_data_class = $this->entity_data_class;
            $rsData = $entity_data_class::getList(array(
               "select" => $arSelect,
               "filter" => $this->getFilter(),
               "order" => $this->getOrder()
            ));
            if ($key) {
                while($arRow = $rsData->Fetch()) {
                    $arList[$arRow[$key]] = $arRow;   
                }
            } else {
                while($arRow = $rsData->Fetch()) {
                    $arList[] = $arRow;   
                }
            }
            
            return $arList;
        }
        #
        
        // возвращает справочник ключ-значение
        public function getReference ($key,$val) {
            //$entity_data_class = $this->entity->getDataClass();
            $entity_data_class = $this->entity_data_class;
            $rsData = $entity_data_class::getList(array(
               "select" => array($key,$val),
               "filter" => $this->getFilter(),
               "order" => $this->getOrder()
            ));
            while($arRow = $rsData->Fetch()) {
                $arRef[$arRow[$key]] = $arRow[$val];   
            }
            
            return $arRef;
        }
        #
        
        
        // добавляем элемент
        public function add ($arFields) {
            $entity_data_class = $this->entity_data_class;
            $result = $this->entity_data_class::add($arFields);
            return array(
                    'ID' => $result->getID(),
                    'rs' => $result->isSuccess()
                );;
        }
        #
        
        // обновляет элемент
        public function update ($arFields) {
            $entity_data_class = $this->entity_data_class;
            $result = $this->entity_data_class::update($arFields['ID'],$arFields);
            return array(
                    'ID' => $result->getID(),
                    'rs' => $result->isSuccess()
                );;
        }
        #
        
        // удаляем элементы
        public function delete ($arIDs) {
            if (!is_array($arIDs)) $arIDs = [$arIDs];
            $entity_data_class = $this->entity_data_class;
            foreach ($arIDs as $id) {
                $this->entity_data_class::delete($id);
            }
            return true;
        }
        #
        
        
        public function getFilter () {
            if (!is_array($this->Filter)) $this->Filter=array();
            return $this->Filter;
        }
        
        public function setFilter ($arFilter) {$this->Filter=$arFilter; return $this;}
        public function add2Filter ($arFilter) {$this->Filter=array_merge($this->Filter,$arFilter); return $this;}
        
        public function getOrder () {
            if (!is_array($this->Order)) $this->Order=array();
            return $this->Order;
        }
        
        public function setOrder ($arOrder) {$this->Order=$arOrder; return $this;}
        public function add2Order ($arOrder) {$this->Order=array_merge($this->Order,$arOrder); return $this;}
        
        
    }
}

