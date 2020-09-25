<?
/* пользователи */
namespace X\Abstraction {
    abstract class UsersModel extends \X\Abstraction\Users {
        
        static $instances = [];
        public static function getInstance($uid='') {
            if (!isset(static::$instances[$uid])) {
                static::$instances[$uid] = new static($uid);
            }
            return static::$instances[$uid];
        }
        
        
        protected final function __clone() {}
        protected final function __wakeup() {}
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
        protected $Filter=array();
        protected function __uid() {return __CLASS__;}
        protected function __construct() {
            
        }
        
        // 
        public function getDict ($key='ID',$sort=['by'=>'ID','order'=>'DESC']) {
            $arFilter = $this->getFilter();
            $arSelectUF = $this->getSelectUF();
            $arSelectFields = $this->getSelectFields();
            $rsUsers = \CUser::GetList(
                    ($by=$sort['by']), ($order=$sort['order']),
                    $arFilter,
                    array(
                            'FIELDS' => $arSelectFields,
                            'SELECT' => $arSelectUF
                        )
                ); //
            
            \XDebug::log(
                    array(
                            'filter'=>$arFilter,
                            'fields'=>$arSelectFields,
                            'select'=>$arSelectUF
                        ),
                    'call Users->getDict'
                );
            
            $arUsers = array();
            while ($arUser = $rsUsers->Fetch()) {
                $arUsers[$arUser[$key]] = $arUser;
            }
            
            return $arUsers;
        }
        
        // 
        public function getReference ($key='ID',$val='EMAIL',$sort=['by'=>'ID','order'=>'DESC']) {
            $arFilter = $this->getFilter();
            $rsUsers = \CUser::GetList(
                    ($by=$sort['by']), ($order=$sort['order']),
                    $arFilter,
                    array(
                            'FIELDS' => [$val],
                            'SELECT' => [$val]
                        )
                ); //
            
            \XDebug::log(
                    array(
                            'filter'=>$arFilter,
                            'key' => $key,
                            'val'=> $val
                        ),
                    'call Users->getReference'
                );
            
            $arUsers = array();
            while ($arUser = $rsUsers->Fetch()) {
                $arUsers[$arUser[$key]] = $arUser[$val];
            }
            
            return $arUsers;
        }
        
        // 
        public function getCnt () {
            $arFilter = $this->getFilter();
            $rsUsers = \CUser::GetList(
                    ($by=$sort['by']), ($order=$sort['order']),
                    $arFilter,
                    array(
                            'SELECT' => ['ID']
                        )
                ); //
            $cnt = $rsUsers->SelectedRowsCount();
            \XDebug::log(
                    array(
                            'filter' => $arFilter,
                            'result' => $cnt
                        ),
                    'call Users->getCnt'
                );
            return $cnt;
        }
        
        public function getFilter () {if (!is_array($this->Filter)) $this->Filter=array();return $this->Filter;}
        public function setFilter ($arFilter) {$this->Filter=$arFilter; return $this;}
        public function add2Filter ($arFilter) {$this->Filter=array_merge($this->Filter,$arFilter); return $this;}
        
    }
}