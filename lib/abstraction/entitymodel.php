<?
namespace X\Abstraction {
    abstract class EntityModel extends Model {
    
        const MODEL = 'entity';
        
        public static function getInstance($Table=false) {
            if ($Table) {
                $Table = $Table;
            } else {
                $Table = static::Table;
            }
            
            return parent::getInstance($Table); 
        }
        
        /* Пример установки кэша
         * по умолчанию
        protected $cTime = 14400; // 4 часа
         * для отдельного метода
        protected $cTimes = [
                'getCnt' => 18000 // 5 часов
            ];
        */
        
        protected function __construct($Table) {
            if (!$Table) die('Invalid IBlock Id: '.$Table);
            $this->Table = $Table;
            $this->EntityClass = '\Entity\\'.$Table.'Table';
            $this->EntityClass::verifeTable();
            
            return parent::__construct($Table);
        }
        
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
        public function getEntity ()
        {
			if (!$this->entity) $this->entity = $this->EntityClass::getEntity();
            return $this->entity;
		}
        
        public function getPrimary ()
        {
			if (!$this->primary) $this->primary = $this->getEntity()->getPrimary();
            return $this->primary;
		}
        
		public function add (
                array $dct
            )
        {
			$result = new \X\Result;
			$result->add($this->EntityClass::add($dct));
			return $result;
		}
        
        
		public function upd (
                array $dct
            )
        {
            $result = new \X\Result;
            $result->add($this->EntityClass::update($dct[$this->Primary],$dct));
			return $result;
		}
        
        
        public function del (
                $primary
            )
        {
            $result = new \X\Result;
            if (!is_array($primary)) $primary = [$primary];
            foreach ($primary as $prim) {
                $this->EntityClass::delete($prim);
            }
            
			return true;
		}
        
        
        
        /**
         * возвращает объект навигации
         * создавая новый если необходимо
         */
        public function nav ($idnav=false)
        {
            if ($idnav) {
                if (!$this->navObject || $this->navObject->getId() != $idnav)
                        $this->navObject = new \Bitrix\Main\UI\PageNavigation($idnav);
            } else {
                if (!$this->navObject) return false;
            }
			
			return $this->navObject;
		}
        
        /**
         * уничтожает текущую нафигацию
         *
         */
        public function navDestroy ($idnav)
        {
			$this->navObject = false;
		}
        
        
        /**
         * возвращает количество элементов
         *
         */
        public function cnt ($arParams=[])
        {
            
			$arParams = $this->getParams($arParams);
			$res = $this->EntityClass::getList($arParams);
            
            $cnt = 0;
            if ($cntr = $res->getSelectedRowsCount()) $cnt = $cntr;
            
            $cacheKey = false;
            \XDebug::log(
                    array(
                            'options'=>$arParams,
                            'result'=>$cnt
                        ),
                    'call cnt for '.$this->Table.($cacheKey?' (from cache)':'')
                );
            
			return $cnt;
		}
        
        /**
         * возвращает список
         *
         */
        public function lst ($arParams=[])
        {
            // параметры метода
            // если в $arParams нет filter, select или order
            // то будут подставлены текущие
			$arParams = $this->getParams($arParams);
            
            if ($this->navObject) {
                $arParams['count_total'] = true;
                $arParams['offset'] = $this->navObject->getOffset();
                $arParams['limit'] = $this->navObject->getLimit();
            }
            
            $res = $this->EntityClass::getList($arParams);
            
            if ($arParams['count_total'] && $this->navObject) $this->navObject->setRecordCount($res->getCount());
            
            $lst = [];
            while ($dct = $res->fetch()) $lst[] = $dct;
            
            $cacheKey = false;
            \XDebug::log(
                    array(
                            'options'=>$arParams
                        ),
                    'call lst for '.$this->Table.($cacheKey?' (from cache)':'')
                );
            
			return $lst;
		}
        
		/**
         * возвращает справочник
         *
         */
        public function ref ($key=false,$arParams=[])
        {
            
            if ($key === false) $key = $this->getPrimary();
            
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
                    'call lst for '.$this->Table.($cacheKey?' (from cache)':'')
                );
            
			return $ref;
		}
        
        
    }
}

