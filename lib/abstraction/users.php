<?
/* абстрактная аобстракция, общая для пользователия и пользователей */
namespace X\Abstraction {
    abstract class Users {
    
        
        protected $SelectFields=array();
        protected $SelectUF=array();
        
        /*
         * Возвращает список извлекаемых полей
        */
        public function getSelectFields ()
        {
            if (!is_array($this->SelectFields) || !count($this->SelectFields)) $this->SelectFields=array('ID');
            return $this->SelectFields;
        }
        #
        
        /*
         * Устаналивает список извлекаемых полей
        */
        public function setSelectFields ($arSelectFields) {$this->SelectFields=$arSelectFields; return $this;}
        
        /*
         * Устаналивает добавляем поле или массив полей в список извлекаемых
        */
        public function add2SelectFields ($arSelectFields)
        {
            $this->SelectFields=array_merge($this->SelectFields,$arSelectFields);
            return $this;
        }
        #
        
        /*
         * Возвращает список извлекаемых пользовательских полей
        */
        public function getSelectUF ()
        {
            if (!is_array($this->SelectUF)) $this->SelectUF=array();
            return $this->SelectUF;
        }
        #
        
        /*
         * Устаналивает список извлекаемых пользовательских полей
        */
        public function setSelectUF ($arSelectUF) {$this->SelectUF=$arSelectUF; return $this;}
        
        /*
         * Устаналивает добавляем поле или массив полей в список извлекаемых
        */
        public function add2SelectUF ($arSelectUF) {$this->SelectUF=array_merge($this->SelectUF,$arSelectUF); return $this;}
        
        // возвращает справочник пользовательских полей объекта USER
        public function getFields ()
        {
            
            $rsData = \CUserTypeEntity::GetList(array($by=>$order), array('ENTITY_ID'=>'USER'));
            $arFields = array();
            while($arRes = $rsData->Fetch()) {
                
                if ($arRes['USER_TYPE_ID'] == 'enumeration') { // Значения списков
                    $rsEnum = \CUserFieldEnum::GetList(array(), array('USER_FIELD_ID' => $arRes["ID"]));
                    $arRes['ENUM'] = array();
                    while($arEnum = $rsEnum->Fetch()) {
                        $arEnum['LABEL'] = $arEnum['VALUE'];
                        $arRes['ENUM'][$arEnum["ID"]] = $arEnum;
                    }
                } elseif ($arRes['USER_TYPE_ID'] == 'iblock_element') { // значение привзяки к элементам ИБ
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
            
            return $arFields;
        }
        
    }
}