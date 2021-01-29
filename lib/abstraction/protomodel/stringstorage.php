<?
// \Model\Stringstorage::getInstance()->getStringVal();
/*
Список полей:
UF_XML_ID	Строка
UF_STRING	Строка
UF_NAME	Строка
*/
namespace X\Abstraction\Protomodel {
    class Stringstorage extends \X\Abstraction\HLBModel {
        
        // возвращает Значение строки по коду
        public function getStringVal (
                $xml_id,
                $search=false,
                $replace=''
            ) {
            $arElement = $this->setFilter(['UF_XML_ID'=>$xml_id])->getElement();
            $str = $arElement['UF_STRING'];
            if ($search) $str = str_replace($search,$replace,$str);
            return $str;
        }
        #
        
    }
}