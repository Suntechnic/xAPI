<?
// $hld = \Model\Hld::getInstance(1,'ru');
namespace Model {
    class Hld extends \X\Abstraction\HLBModel {
        
        
        
        const TRASLATE_LST = [ // список переводимых полей
                'UF_NAME',
                'UF_DESCRIPTION',
                'UF_FULL_DESCRIPTION'
            ];
        
        private $lang = '';
        private $map = [];
        
        public static function getInstance ($ID=false, $lang=false) {
            $self = parent::getInstance($ID);
            
            if (!$lang) {
                $lang = LANGUAGE_UID;
            } else {
                $lang = strtoupper($lang);
            }
            $self->lang = $lang;
            return $self;
        }
        #
        
        
        // возвращает один первый элемент
        public function getElement ($arParams=[]) {
            $elm = parent::getElement($arParams);
            $elm = $this->loc([$elm])[0];
            return $elm;
        }
        #
        
        
        // возвращает список элементов
        public function getList ($arParams=[]) {
            $lst = parent::getList($arParams);
            $this->loc($lst);
            return $lst;
        }
        #
        
        
        /**
         * возвращает справочник
         *
         */
        public function getReference ($key=false,$arParams=[])
        {
            $ref = parent::getReference($key,$arParams);
            $this->loc($ref);
			return $ref;
		}
        
        // перевод списка элементо
        private function loc ($ar)
        {
            //$map = [];
            //foreach ($this::TRASLATE_LST as $field) $map[$field] = $field.'__'.$this->lang;
            //
            foreach ($ar as $k=>$dctItem) {
                foreach ($this->map as $field=>$field_lng) {
                    if ($dctItem[$field_lng]) $ar[$k][$field] = $dctItem[$field_lng];
                }
            }
			return $ar;
		}
        
        
        public function getParams (&$arParams)
        {
            foreach ($this::TRASLATE_LST as $field) {
                $field_lng = $field.'__'.$this->lang;
                if (in_array($field,$arParams['select'])) {
                    $this->map[$field] = $field_lng;
                    if (!in_array($field_lng,$arParams['select'])) $arParams['select'][] = $field_lng;
                }
            }
            
            $arParams = parent::getParams($arParams);
			return $arParams;
		}
        
    }
}
