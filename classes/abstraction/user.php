<?
# DEPRICATED #
namespace X\Abstraction {
    abstract class User extends \X\Abstraction\Singleton {
        
        protected $id;
        
        protected $Filter=array();
        protected $Select=array();
        
        protected $Data=false;
        
        protected function __construct($uid) {
            if ($uid > 0) {} else {
                $uid = 0;
                global $USER;
                if (is_a($USER,'CUser')) $uid = intval($USER->GetID());
            }
            $this->id = $uid;
        }
        
        // 
        public function GetID() {
            return $this->id;
        }
        
        // 
        public function getData () {
            if (!$this->Data) {
                if ($this->id > 0) {
                    $rsUsers = \CUser::GetList(
                            ($by='id'), ($order='desc'),
                            array('ID'=>$this->id),
                            array(
                                    'SELECT' => $this->getSelect()
                                )
                        ); // 
                    if ($arUser = $rsUsers->Fetch()) $this->Data = $arUser;
                } else $this->Data = array();
                
            }
            return $this->Data;
        }
        
        // 
        public function getDict ($key='ID',$sort=['by'=>'ID','order'=>'DESC']) {
            $rsUsers = \CUser::GetList(
                    ($by=$sort['by']), ($order=$sort['order']),
                    $this->getFilter(),
                    array(
                            'SELECT' => $this->getSelect()
                        )
                ); //
            
            \XDebug::log(
                    array(
                            'filter'=>$this->getFilter(),
                            'select'=>$this->getSelect()
                        ),
                    'call User->getDict'
                );
            
            $arUsers = array();
            while ($arUser = $rsUsers->Fetch()) {
                $arUsers[$arUser[$key]] = $arUser;
            }
            
            return $arUsers;
        }
        
        // возвращает справочник пользовательских полей
        public function getFields () {
            
            $rsData = \CUserTypeEntity::GetList(array($by=>$order), array('ENTITY_ID'=>'USER'));
            $arFields = array();
            while($arRes = $rsData->Fetch()) {
                
                if ($arRes['USER_TYPE_ID'] == 'enumeration') {
                    $rsEnum = \CUserFieldEnum::GetList(array(), array('USER_FIELD_ID' => $arRes["ID"]));
                    $arRes['ENUM'] = array();
                    while($arEnum = $rsEnum->Fetch()) {
                        $arEnum['LABEL'] = $arEnum['VALUE'];
                        $arRes['ENUM'][$arEnum["ID"]] = $arEnum;
                    }
                } elseif ($arRes['USER_TYPE_ID'] == 'iblock_element') {
                    \CModule::IncludeModule("iblock");
                    $arSelect = Array(
                            'ID',
                            'NAME',
                            'ACTIVE',
                            'CODE'
                        );

                    $arFilter = Array(
                            'IBLOCK_ID'=> $arRes['SETTINGS']['IBLOCK_ID'],
                        );
                    if ($arRes['SETTINGS']['ACTIVE_FILTER'] == 'Y') $arFilter['ACTIVE'] = 'Y';
                    $arRes['ENUM'] = array();
                    $db_res = \CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
                    while($arEnum = $db_res->Fetch()) {
                        $arEnum['LABEL'] = $arEnum['NAME'];
                        $arRes['ENUM'][$arEnum["ID"]] = $arEnum;
                    }
                }
                
                
                $arFields[$arRes['FIELD_NAME']] = $arRes;
            }
            
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
        
        public function getSelect () {if (!is_array($this->Select)) $this->Select=array();return $this->Select;}
        public function setSelect ($arSelect) {$this->Select=$arSelect; return $this;}
        public function add2Select ($arSelect) {$this->Select=array_merge($this->Select,$arSelect); return $this;}
        
        
        public function getFilter () {if (!is_array($this->Filter)) $this->Filter=array();return $this->Filter;}
        public function setFilter ($arFilter) {$this->Filter=$arFilter; return $this;}
        public function add2Filter ($arFilter) {$this->Filter=array_merge($this->Filter,$arFilter); return $this;}
        
    }
}

