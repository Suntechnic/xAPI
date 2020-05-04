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
        protected function __construct() {
            $uid = 0;
            global $USER;
            if (is_a($USER,'CUser')) $uid = intval($USER->GetID());
            $this->id = $uid;
        }
        
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
        protected $id;
        protected $Data=false;
        protected $Groups=false;
        
        // 
        public function GetID()
        {
            if ($this->id == 0) { // возомжно юзер залогинился
                global $USER;
                if (is_a($USER,'CUser')) $uid = intval($USER->GetID());
                if ($uid) $this->id = $uid;
            }
            return $this->id;
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
            if ($this->id > 0) {
                
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
                
                
                if (!$this->Data[$memokey]) {
                    $arData = [];
                    
                    $rsUsers = \CUser::GetList(
                            ($by='id'), ($order='desc'),
                            array('ID'=>$this->id),
                            array(
                                    'FIELDS' => $arSelectFields,
                                    'SELECT' => $arSelectUF
                                )
                        ); //
                    if ($arUser = $rsUsers->Fetch()) $arData = $arUser;
                    $this->Data[$memokey] = $arData;
                }
                
                return $this->Data[$memokey];
            } else return array();
        }
        
        
        // возвращает массив групп в которые входит пользователь
        public function getGroups () {
            if ($this->id > 0) {
                if (!$this->Groups) {
                    $this->Groups = \CUser::GetUserGroup($this->id);
                }
            } else return array();
            
            return $this->Groups;
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

