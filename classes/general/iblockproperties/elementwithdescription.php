<?

namespace X\IBlockProperties;
class ElementWithDescription 
{
    function GetIBlockPropertyDescription ()
    {
        return [
                'PROPERTY_TYPE' => 'E',
                'USER_TYPE' => 'E:WithDescription',
                'DESCRIPTION' => 'Привязка к элементам с описанием',
                'GetPropertyFieldHtml' => array(__CLASS__, 'GetPropertyFieldHtml'),
                //'ConvertToDB' => array(__CLASS__,'ConvertToDB'),
                //'ConvertFromDB' => array(__CLASS__,'ConvertFromDB'),
            ];
    }
    
    
    function GetPropertyFieldHtml ($arProperty, $arValue, $strHTMLControlName)
    {
         
        // значения по умолчанию
        $arItem = Array(
            "ID" => 0,
            "IBLOCK_ID" => 0,
            "NAME" => ""
        );
         
        // получение информации по выбранному элементу
        if(intval($arValue["VALUE"]) > 0)
        {
            $arFilter = Array(
                "ID" => intval($arValue["VALUE"]),
                "IBLOCK_ID" => $arProperty["LINK_IBLOCK_ID"],
            );
            $mxElement = \CIBlockElement::GetList(
                    [],
                    [
                            'ID' => intval($arValue['VALUE']),
                            'IBLOCK_ID' => $arProperty['LINK_IBLOCK_ID'],
                        ],
                    false, false,
                    ['ID', 'IBLOCK_ID', 'NAME']
                )->Fetch();
        }
         
        // сама строка с товаром и доп.значениями
        $arProperty['LINK_IBLOCK_ID'] = (int)$arProperty['LINK_IBLOCK_ID'];
		$fixIBlock = $arProperty['LINK_IBLOCK_ID'] > 0;
		$windowTableId = 'iblockprop-'.\Bitrix\Iblock\PropertyTable::TYPE_ELEMENT.'-'.$arProperty['ID'].'-'.$arProperty['LINK_IBLOCK_ID'];
        
        $searchUrl = (defined('SELF_FOLDER_URL') ? SELF_FOLDER_URL : '/bitrix/admin/').'iblock_element_search.php';
        $searchUrl.= '?lang='.LANGUAGE_ID.
				'&amp;IBLOCK_ID='.$arProperty['LINK_IBLOCK_ID'].
				'&amp;n='.urlencode($strHTMLControlName['VALUE']).
				($fixIBlock ? '&amp;iblockfix=y' : '').
				'&amp;tableId='.$windowTableId;
                
        if (!is_array($mxElement))
        {
            $strResult = '<input type="text" name="'.htmlspecialcharsbx($strHTMLControlName["VALUE"]).'" id="'.$strHTMLControlName["VALUE"].'" value="" size="5">'.
                '<input type="button" value="..." onClick="jsUtils.OpenWindow(\''.$searchUrl.'\', 900, 700);">'.
                '&nbsp;<span id="sp_'.$strHTMLControlName["VALUE"].'" ></span>';
        }
        else
        {
            $strResult = '<input type="text" name="'.$strHTMLControlName["VALUE"].'" id="'.$strHTMLControlName["VALUE"].'" value="'.$arValue['VALUE'].'" size="5">'.
                '<input type="button" value="..." onClick="jsUtils.OpenWindow(\''.$searchUrl.'\', 900, 700);">'.
                '&nbsp;<span id="sp_'.$strHTMLControlName["VALUE"].'" >'.$mxElement['NAME'].'</span>';
        }
        
        unset($searchUrl);
    
        $strResult.=
        ' : <input type="text" id="desc_'.$strHTMLControlName["VALUE"].'" name="'.$strHTMLControlName["DESCRIPTION"].'" value="'.htmlspecialcharsex($arValue["DESCRIPTION"]).'">';
        return  $strResult;
    }
     
    //function GetAdminListViewHTML($arProperty, $arValue, $strHTMLControlName)
    //{
    //    return;
    //}
     
    //function ConvertToDB ($arProperty, $arValue)
    //{
    //    return $arValue; 
    //}
    // 
    //function ConvertFromDB($arProperty, $arValue)
    //{
    //    return $arValue;
    //}
}


