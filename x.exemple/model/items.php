<?
// $items = \Model\Items::getInstance();

namespace Model
{
    class Items
    {
        
		static $instances;
		
		private $list_id;
        
        public static function getInstance($list_id) {
            if (!isset(static::$instances[$list_id])) {
                static::$instances[$list_id] = new static($list_id);
            }
            return static::$instances[$list_id];
        }
        
        protected function __construct($list_id)
        {
            if (isset(static::$instances[$list_id])) return;
            $this->ItemTable = '\Entity\ItemTable';
            
            $this->ItemTable::verifeTable();
			
			#TODO: проверка доступа к списку
			$this->list_id = $list_id;
        }
        
        protected final function __clone() {}
        protected final function __wakeup() {}
        
        /**
         * Создает пункт
         *
         */
		public function add (
				string $name
			)
        {
			
			$arFieldsItem = [
					'LIST' => $this->list_id,
					'NAME' => $name
				];
			
			$result_item = $this->ItemTable::add($arFieldsItem);
			return $result_item;
		}
	}

}

