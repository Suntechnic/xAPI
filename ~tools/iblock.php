<?
require_once('.prolog.php');
CModule::IncludeModule("iblock");

if ($_REQUEST['IBLOCK_ID']) {
    $IBLOC_ID = intval($_REQUEST['IBLOCK_ID']);
    
    
    //
    $res = CIBlock::GetByID($IBLOC_ID);
    if($arIBlock = $res->fetch()) {
        
        
        // свойства ИБ
        $rsProp = CIBlockProperty::GetList(Array("sort"=>"asc", "id"=>"asc"), Array("IBLOCK_ID"=>$IBLOCK_ID));
        $arProps = array();
        while ($arProp = $rsProp->fetch()) {
            $arProps[] = $arProp;
        }
        //die('<pre>'.print_r((array)$arProps,true).'</pre>');
        
    $className = ucfirst(strtolower($arIBlock['CODE']));
    $classPath = '\\Modedl\\'.$className;
    $fileName = strtolower($arIBlock['CODE']).'.php';
    $filePath = S_P_X.'/model/'.$fileName;
    
    if (file_exists($filePath)):
        //require_once($filePath);
        //die('<pre>'.print_r((array)$classPath,true).'</pre>');
        //if (class_exists($className)):?>
        <h2>Файл класса модели <span style="color:green"><?=$className?></span> уже существует</h2>
        <?/*else:?>
        <h2>Классовая ошибка - файл <?=$filePath?> не пораждает класс <span style="color:red"><?=$className?></span>.</h2>
        <?endif*/?>
    <?else:?>
    <h2>Файл класса модели <span style="color:#935B00;"><?=$className?></span> не существует.</h2>
    <?
    $classCode = '
// автоматически созданный класс модели
// инфоблока '.$arIBlock['NAME'].'
// $'.strtolower($arIBlock['CODE']).' = \\Model\\'.$className.'::getInstance();

namespace Model {
    class '.$className.' extends \X\Abstraction\IBModel {
        
        const IDIB=IDIB_'.strtoupper($arIBlock['CODE']).';

        protected $Filter=array(
                
            );
        
        protected $Select=array(
                \'IBLOCK_ID\',
                \'CODE\',
                \'ID\',
                \'NAME\',
                \'PREVIEW_PICTURE\',
                \'PREVIEW_TEXT\',';
    foreach($arProps as $arProp) {
        $classCode.= "\n                'PROPERTY_".$arProp['CODE']."', // ".$arProp['NAME'];
    }
     $classCode.= '
            
            );
            
        //protected $userGroups = false;
        
        //public function cacheKey($params) {
        //    if (!$userGroups) {
        //        global $USER;
        //        $this->userGroups = serialize($USER->GetUserGroupArray());
        //    }
        //    $params[\'ext\'] = $this->userGroups;
        //    return parent::cacheKey($params);
        //}
            
        //function getDict ($key=\'ID\',$arSelect=false, $params=\'getDict\') {
        //    $arDict = parent::getDict($key,$arSelect, $params);
        //    return $arDict;
        //}
        
        //function listPreprocessing ($arElements,$params) {
        //    $arSectIds = array_map(function($e){return $e[\'IBLOCK_SECTION_ID\'];},$arElements);
        //    $arSectIds = array_filter(array_unique($arSectIds),function($e){return $e>0;});
        //    
        //    $arSection = $this->getSections(
        //            array(\'ID\'=>$arSectIds),
        //            array(\'ID\',\'NAME\',\'SECTION_PAGE_URL\'),
        //            $arOrder=false,
        //            $count=false,
        //            $params=\'getSections\'
        //        );
        //    
        //    foreach ($arElements as $k=>$arElm) {
        //        if ($arElm[\'IBLOCK_SECTION_ID\']) {
        //            $arElements[$k][\'IBLOCK_SECTION\'] = $arSection[$arElm[\'IBLOCK_SECTION_ID\']];
        //        }
        //    }
        //    
        //    return $arElements;
        //}
        
        //function elementPreprocessing ($arElement,$params) {
        //    if ($arElement[\'PREVIEW_PICTURE\'])  {
        //        $arElement[\'PREVIEW_PICTURE\'] = \CFile::GetFileArray($arElement[\'PREVIEW_PICTURE\']);
        //    }
        //    return $arElement;
        //}
        
    }
}';

    if ($_REQUEST['createclass'] == 'y') {
        $bytes = file_put_contents($filePath,"<?\n".$classCode);

        if (!$bytes):?>
            <h2 style="color:red">Не удалось записать файл</h2>
        <?endif?>
        <?if (file_exists($filePath)):?>
            <h2 style="color:green">Класс модели создан</h2>
        <?else:?>
            <h2 style="color:red">Что-то пошло не так :(</h2>
            <p>Файл <?=$filePath?> не обноружен после создания...</p>
        <?endif;
    } else {
        ?><a href="?IBLOCK_ID=<?=$IBLOC_ID?>&createclass=y">создать из снипета</a><?
    }
    ?>
    
    <pre><?=$classCode?></pre>
    <?endif;
    }
}
require_once('.epilog.php');