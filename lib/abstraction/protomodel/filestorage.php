<?
// \Model\Filestorage::getInstance()->getFileSrc();

/*
Список полей:
UF_NAME	Строка
UF_FILE	Файл
UF_URL	Строка
UF_XML_ID	Строка
*/

namespace X\Abstraction\Protomodel {
    class Filestorage extends \X\Abstraction\HLBModel {
        
        
        // возвращает src файла по xml_id
        public function getFileSrc ($xml_id) {
            $arElement = $this->setFilter(['UF_XML_ID'=>$xml_id])->getElement();
            return $arElement['SRC'];
        }
        #
        
        // возвращает src файла по xml_id
        public function getFileArray ($xml_id) {
            $arElement = $this->setFilter(['UF_XML_ID'=>$xml_id])->getElement();
            return $arElement;
        }
        #
        
        // возвращает запись по xml_id
        public function getFile ($xml_id) {
            $arFilter = $this->getFilter();
            $arElement = $this->setFilter(['UF_XML_ID'=>$xml_id])->getElement();
            $this->setFilter($arFilter);
            return $arElement;
        }
        #
        
        // возвращает один первый элеметм
        public function getElement () {
            $arElament = parent::getElement();
            return $this->__procElement($arElament);
        }
        #
        
        // возвращает словать элементов по указанному ключу
        public function getDict ($key=false) {
            $arList = parent::getDict($key);
            foreach ($arList as $k=>$arElm) $arList[$k] = $this->__procElement($arElm);
            return $arList;
        }
        #
        
        //
        private function __procElement ($arElement) {
            if ($arElement['UF_URL']) {
                $arElement['SRC'] = $arElement['UF_URL'];
            } else if ($arElement['UF_FILE']) {
                $arElement['UF_FILE'] = \CFile::GetFileArray($arElement['UF_FILE']);
                $arElement['SRC'] = $arElement['UF_FILE']['SRC'];
                $arElement['S_SRC'] = S_.$arElement['SRC'];
                $size = intval($arElement['UF_FILE']['FILE_SIZE']);
                
                $arElement['SIZE'] = [
                        'B' => $size,
                        'KiB' => $size/1024,
                        'MiB' => ($size/1024)/1024,
                        'GiB' => (($size/1024)/1024)/1024
                    ];
                foreach ($arElement['SIZE'] as $unit=>$size) {
                    if ($size > 1) {
                        $arElement['SIZE']['ACTUAL_SIZE'] = $size;
                        $arElement['SIZE']['ACTUAL_UNIT'] = $unit;
                    } else break;
                }
                
                if ($arElement['SIZE']['ACTUAL_SIZE'] > 10) {
                    $arElement['SIZE']['ACTUAL_SIZE'] = round($arElement['SIZE']['ACTUAL_SIZE']);
                } else $arElement['SIZE']['ACTUAL_SIZE'] = round($arElement['SIZE']['ACTUAL_SIZE'],1);
                
            }
            return $arElement;
        }
        #
        
    }
}