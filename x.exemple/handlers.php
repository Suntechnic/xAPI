<?
class XHandlers {
    
    /*
    Цепляемся к событию OnBeforeProlog,
    модуля main
    с сортировкой 100
    */
    public static function main_OnBeforeProlog_100 ()
    {
        
    }
    
    /*
    Цепляемся к событию OnBeforeProlog,
    модуля main
    с сортировкой 100
    
    #ID# - id обращения
    #NAME# - имя
    #PREVIEW_TEXT# - текст
    #PROPERTY_EMAIL_VALUE# - Эл. почта
    #PROPERTY_PHONE_VALUE# - телефон
    #PROPERTY_CITY_VALUE# - Город
    */
    public static function iblock_OnAfterIBlockElementAdd_100 (&$arFields)
    {
        
        
        if($arFields['IBLOCK_ID'] == IDIB_FEEDBACK && $arFields["ID"] > 0) {

            # http://dev.1c-bitrix.ru/api_help/iblock/classes/ciblockelement/getlist.php
            $arSelect = Array(
                    'IBLOCK_ID',
                    'ID',
                    'NAME',
                    'PREVIEW_TEXT',
                    'PROPERTY_EMAIL',
                    'PROPERTY_PHONE',
                    'PROPERTY_CITY'
                );
            $arFilter = Array(
                    'IBLOCK_ID'=> IDIB_FEEDBACK,
                    'ID' => $arFields["ID"]
                );
            $db_res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
            
            if ($arElement = $db_res->fetch()) {
                \CEvent::Send('NEW_FEEDBACK','s1',$arElement);
            }
            
            
        }
    }
    
    
}