<?
// $lists = \Model\Lists::getInstance();

namespace Model
{
    class Lists
    {
        
		static $instances;
        
        public static function getInstance() {
            if (!isset(static::$instances)) {
                static::$instances = new static();
            }
            return static::$instances;
        }
        
        protected function __construct()
        {
            if (isset(static::$instances)) return;
            $this->ListTable = '\Entity\ListTable';
            $this->ULTable = '\Entity\ULTable';
            
            $this->ListTable::verifeTable();
            $this->ULTable::verifeTable();
        }
        
        protected final function __clone() {}
        protected final function __wakeup() {}
        
        /**
         * Создает список
         *
         */
		public function add ($name)
        {
			$arFieldsList = [
                    'NAME' => $name,
                ];
			$arFieldsUL = [];
			
			$connection = \Bitrix\Main\Application::getInstance()->getConnection();
            $connection->startTransaction(); {
				$result_list = $this->ListTable::add($arFieldsList);
				if ($result_list->isSuccess()) {
					$arFieldsUL['LIST'] = $result_list->getId();
					$result_ul = $this->ULTable::add($arFieldsUL);
					if ($result_list->isSuccess()) {
						
					} else {
						$connection->rollbackTransaction();
						return false;
					}
				} else {
					$connection->rollbackTransaction();
					return false;
				}
			} $connection->commitTransaction(); // коммитим
            
			return $result_list;
		}
        
        /**
         * возвращает список списков, доступных пользователю
         *
         */
		public function get ()
        {
			$res = $this->ListTable::getList([
                    'runtime' => array(
                        'UL_TABLE' => [
                            'data_type' => \Entity\ULTable::class,
                            'reference' => array(
                                '=this.ID' => 'ref.LIST',
                            ),
                            //'join_type' => "left"
                        ],
                    ),
                    'filter'=>['UL_TABLE.USER'=>\Model\User::getInstance()->getId()]
                ]);
            while ($arList = $res->fetch()) {
                $lstLists[] = $arList;
            }
			return $lstLists;
		}
	}

}

