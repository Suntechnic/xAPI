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
        
        
		private function update (
                array $dct
            )
        {
            $result = new \X\Result;
            $result->add($this->EntityClass::update($dct[$this->Primary],$dct));
			return $result;
		}
        
        
        /**
         * возвращает список
         *
         */
        public function lst ($arParams=[])
        {
            
			$arParams = $this->getParams($arParams);
			$res = $this->EntityClass::getList($arParams);
            
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

