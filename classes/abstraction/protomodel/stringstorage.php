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
        public function getStringVal ($xml_id) {
            $arElement = $this->setFilter(['UF_XML_ID'=>$xml_id])->getElement();
            return $arElement['UF_STRING'];
        }
        #
        
    }
}