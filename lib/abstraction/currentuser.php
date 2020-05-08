<?
/* текущий пользователь */
namespace X\Abstraction {
    abstract class CurrentUser extends \X\Abstraction\Users {
        
        static $instance;
        public static function getInstance() {
            if (!isset(static::$instance)) {
                static::$instance = new static();
            }
            
            return static::$instance;
        }
        
        protected final function __clone() {}
        protected final function __wakeup() {}
        protected function __construct() {}
        
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
        protected $Data=false;
        protected $Groups=false;
        
        // 
        public function GetID()
        {
            global $USER;
            if (is_a($USER,'CUser')) return intval($USER->GetID());
            return 0;
        }
        
        // 
        public function update ($fields) {
            if ($this->GetID()) {
                $user = new \CUser;
                $user->Update($this->GetID(), $fields);
                $this->Data = false;
            } else return false;
        }
        
        // 
        public function getData ($arParams=[]) {
            $id = $this->GetID();
            if ($id > 0) {
                if ($arParams['select'] || $arParams['select_uf'])  {
                    $arSelectFields = is_array($arParams['select'])?$arParams['select']:[];
                    $arSelectUF = is_array($arParams['select_uf'])?$arParams['select_uf']:[];
                    sort($arSelectFields); sort($arSelectUF);
                    $memokey = md5(serialize($arSelectFields).':'.serialize($arSelectUF));
                } else {
                    $arSelectFields = $this->getSelectFields();
                    $arSelectUF = $this->getSelectUF();
                    $memokey = '_';
                }
                
                if (!$this->Data[$id][$memokey]) {
                    $arData = [];
                    
                    $rsUsers = \CUser::GetList(
                            ($by='id'), ($order='desc'),
                            array('ID'=>$id),
                            array(
                                    'FIELDS' => $arSelectFields,
                                    'SELECT' => $arSelectUF
                                )
                        ); //
                    if ($arUser = $rsUsers->Fetch()) $arData = $arUser;
                    $this->Data[$id][$memokey] = $arData;
                }
                
                return $this->Data[$id][$memokey];
            } else return array();
        }
        
        
        // возвращает массив групп в которые входит пользователь
        public function getGroups () {
            $id = $this->GetID();
            if ($id > 0) {
                if (!$this->Groups[$id]) {
                    $this->Groups[$id] = \CUser::GetUserGroup($id);
                }
            } else return array();
            
            return $this->Groups[$id];
        }
        
        // возвращает справочник пользовательских полей
        public function getFields () {
            $arFields = parent::getFields();
            // кэширование можно сделать до этой точки - здесь заполнение значений
            $arUserData = $this->getData();
            
            foreach ($arFields as $code=>$arField) {
                $arField['VALUE'] = $arUserData[$arField['FIELD_NAME']];
                if ($arField['USER_TYPE_ID'] == 'enumeration'
                        || $arField['USER_TYPE_ID'] == 'iblock_element'
                    ) {
                    foreach ($arField['ENUM'] as $k=>$null) {
                        $selected = 'N';
                        if ($arField['MULTIPLE'] == 'Y') {
                            if (in_array($k,$arField['VALUE'])) $selected = 'Y';
                        } else if ($k == $arField['VALUE'])  $selected = 'Y';
                        $arField['ENUM'][$k]['SELECTED'] = $selected;
                    }
                }
                $arFields[$code] = $arField;
            }
            return $arFields;
        }
        
    }
}

