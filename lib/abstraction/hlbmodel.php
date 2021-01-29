<?

namespace X\Abstraction {
    abstract class HLBModel extends Model {
        
        const MODEL = 'hlblock';
        
        
        public static function getInstance($ID=false) {
            if ($ID) {
                $ID = $ID;
            } else {
                $ID = static::IDHLB;
            }
            
            return parent::getInstance($ID);
        }
        
        protected function __construct($ID) {
            if (!$ID) die('Invalid HLBlock Id: '.$ID);
            $this->ID = $ID;
            
            \Bitrix\Main\Loader::includeModule('highloadblock');
            $this->hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById($this->ID)->fetch();
            $this->entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($this->hlblock);
            $this->EntityClass = $this->entity->getDataClass();
            
            return parent::__construct($ID);
        }
        
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
        protected $hlblock;
        protected $entity;
        protected $entity_data_class;
        
        // возвращает ID инфоблока
        public function getId() {
            return $this->ID;
        }
        #
        
        
        // возвращает один первый элемент
        public function getElement ($arParams=[]) {
            // параметры метода
            // если в $arParams нет filter, select или order
            // то будут подставлены текущие
			$arParams = $this->getParams($arParams);
            
            $res = $this->EntityClass::getList($arParams);
            
            $lst = [];
            if ($dct = $res->fetch()) return $dct;
            
			return false;
        }
        #
        
        
        // возвращает список элементов
        public function getList ($arParams=[]) {
            // параметры метода
            // если в $arParams нет filter, select или order
            // то будут подставлены текущие
			$arParams = $this->getParams($arParams);
            
            $res = $this->EntityClass::getList($arParams);
            
            $lst = [];
            while ($dct = $res->fetch()) $lst[] = $dct;
            
            $cacheKey = false;
            \XDebug::log(
                    array(
                            'options'=>$arParams
                        ),
                    'call lst for '.$this->EntityClass.($cacheKey?' (from cache)':'')
                );
            
			return $lst;
        }
        #
        
        
        /**
         * возвращает справочник
         *
         */
        public function getReference ($key=false,$arParams=[])
        {
            
            if ($key === false) $key = 'ID';
            
            $arParams = $this->getParams($arParams);
            if (is_array($arParams['select']) // если селект установлен
                    && count($arParams['select']) // и не пуст
                    && !in_array($key,$arParams['select']) // но в нем нет ключа
                ) $arParams['select'][] = $key; // необходимо его добавить
            
			$res = $this->EntityClass::getList($arParams);
            
            $ref = [];
            while ($dct = $res->fetch()) $ref[$dct[$key]] = $dct;
            
            $cacheKey = false;
            \XDebug::log(
                    array(
                            'options'=>$arParams
                        ),
                    'call lst for '.$this->EntityClass.($cacheKey?' (from cache)':'')
                );
            
			return $ref;
		}
        
        
        ###################################### DEPRICATED ######################################
        // возвращает сущность
        public function getEntity() {return $this->entity;}
        #
        
        // возвращает ДатаКласс
        public function getDataClass() {return $this->EntityClass;}
        #
        
        
        // возвращает словать элементов по указанному ключу
        public function getDict ($key=false,$arSelect=['*']) {
            //$entity_data_class = $this->entity->getDataClass();
            $entity_data_class = $this->EntityClass;
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
        

        
        
        // добавляем элемент
        public function add ($arFields) {
            $entity_data_class = $this->EntityClass;
            $result = $this->EntityClass::add($arFields);
            return array(
                    'ID' => $result->getID(),
                    'rs' => $result->isSuccess()
                );;
        }
        #
        
        // обновляет элемент
        public function update ($arFields) {
            $entity_data_class = $this->EntityClass;
            $result = $this->EntityClass::update($arFields['ID'],$arFields);
            return array(
                    'ID' => $result->getID(),
                    'rs' => $result->isSuccess()
                );;
        }
        #
        
        // удаляем элементы
        public function delete ($arIDs) {
            if (!is_array($arIDs)) $arIDs = [$arIDs];
            $entity_data_class = $this->EntityClass;
            foreach ($arIDs as $id) {
                $this->EntityClass::delete($id);
            }
            return true;
        }
        #
        
        
    }
}

