<? // /local/x/tools/iblocks_structure.php
require_once('.prolog.php');

CModule::IncludeModule("iblock");

// выберем все активные информационные блоки для текущего сайта типа catalog
// у которых символьный код не my_products, со счетчиком активных элементов.
$res = CIBlock::GetList(
    Array(), 
    Array(
        'ACTIVE'=>'Y', 
        'CNT_ACTIVE'=>'Y',
        'CHECK_PERMISSIONS' => 'N'
    ), true
);
$arIBlocks = array();
while($ar_res = $res->Fetch()) {
    $arIBlocks[$ar_res['ID']] = $ar_res;
}


$properties = CIBlockProperty::GetList(Array(), Array("ACTIVE"=>"Y"));
$arProperties = array();
while ($prop_fields = $properties->Fetch()) {
    $arIBlocks[$prop_fields['IBLOCK_ID']]['PROPERTIES'][] = $prop_fields; //
    
    // добавление связей
    if ($prop_fields['PROPERTY_TYPE'] == 'E' && $prop_fields['LINK_IBLOCK_ID'] > 0) {
        $arIBlocks[$prop_fields['IBLOCK_ID']]['LINKS']['OUT'][] =  $prop_fields['LINK_IBLOCK_ID']; // ссылаетеся
        $arIBlocks[$prop_fields['LINK_IBLOCK_ID']]['LINKS']['IN'][] =  $prop_fields['IBLOCK_ID']; // ссылаетеся
    }
}

?>
<div class="iblock_panel">
    <button id="toggle_empty">Показать/Скрыть пустые</button>
    <?foreach($arIBlocks as $arIBlock):?>
        <a href="#iblock_<?=$arIBlock['ID']?>" class="iblock_link<?if(!$arIBlock['ELEMENT_CNT']):?> empty<?endif;?>"><?=$arIBlock['ID']?></a>
    <?endforeach;?>
</div>

<section>
    
<?foreach($arIBlocks as $arIBlock):?>
<div id="iblock_<?=$arIBlock['ID']?>" class="iblock<?if(!$arIBlock['ELEMENT_CNT']):?> empty<?endif;?>">
    <h2 class="title-iblock">
        <a name="iblock_<?=$arIBlock['ID']?>" title="Настройки" target="_blank" href="/bitrix/admin/iblock_edit.php?type=<?=$arIBlock['IBLOCK_TYPE_ID']?>&lang=ru&ID=<?=$arIBlock['ID']?>&admin=Y"><?=$arIBlock['ID']?>(<?=$arIBlock['CODE']?>)</a>:<a title="Элементы" target="_blank" href="/bitrix/admin/iblock_list_admin.php?IBLOCK_ID=<?=$arIBlock['ID']?>&type=<?=$arIBlock['IBLOCK_TYPE_ID']?>&lang=ru&find_section_section=0"><?=$arIBlock['NAME']?></a>
    </h2>
    <a href="iblock.php?IBLOCK_ID=<?=$arIBlock['ID']?>">Обзор модели</a><br>
    <small>Версия:<?=$arIBlock['VERSION']?>, Тип: <?=$arIBlock['IBLOCK_TYPE_ID']?>, Активных элементов: <?=$arIBlock['ELEMENT_CNT']?>, <?if (defined('IDIB_'.strtoupper($arIBlock['CODE']))):?>Константа: IDIB_<?=strtoupper($arIBlock['CODE']);?><?endif?></small><br>
    
    <?if (count($arIBlock['LINKS']['OUT']) > 0):?>
    <div class="out_links">
        => ссылается на
        <ul>
            <?foreach($arIBlock['LINKS']['OUT'] as $id): $arIBlockOut=$arIBlocks[$id]?>
            <li>
                <a class="iblock_link" href="#iblock_<?=$id?>"><?=$arIBlockOut['ID']?>(<?=$arIBlockOut['CODE']?>):<?=$arIBlockOut['NAME']?></a>
            </li>
            <?endforeach;?>
        </ul>
    </div>
    <?endif?>
    
    <?if (count($arIBlock['LINKS']['IN']) > 0):?>
    <div class="in_links">
        <= Внешние ссылки сюда
        <ul>
            <?foreach($arIBlock['LINKS']['IN'] as $id): $arIBlockIn=$arIBlocks[$id]?>
            <li>
                <a class="iblock_link" href="#iblock_<?=$id?>"><?=$arIBlockIn['ID']?>(<?=$arIBlockIn['CODE']?>):<?=$arIBlockIn['NAME']?></a>
            </li>
            <?endforeach;?>
        </ul>
    </div>
    <?endif?>
    
    <?if (count($arIBlock['PROPERTIES']) > 0):?>
    <a href="props">свойства</a>
    <div class="props">
        <?foreach($arIBlock['PROPERTIES'] as $arProp):?>
            <div class="prop">
                <h3 class="title-props"><?=$arProp['ID']?>(<?=$arProp['CODE']?>):<?=$arProp['NAME']?></h3>
                <?if ($arProp['PROPERTY_TYPE'] == 'E' && $arProp['LINK_IBLOCK_ID'] > 0):?>
                <small>Привязка к элементам инфоблока <a class="iblock_link" href="#iblock_<?=$arProp['LINK_IBLOCK_ID']?>"><?=$arProp['LINK_IBLOCK_ID']?>:<?=$arIBlocks[$arProp['LINK_IBLOCK_ID']]['NAME']?></a></small>
                <?else:?>
                <small>Тип: <?=$arProp['PROPERTY_TYPE']?></small>
                <?endif?>
            </div>
        <?endforeach;?>
    </div>
    <?endif?>
    
    <a href="raw">RAW</a>
    <pre class="raw">
        <?print_r($arIBlock);?>
    </pre>
</div>
<?endforeach;?>
</section>
</body>
<foot>
	<script defer="defer">
        $('a[href="props"]').click(function(){
            $(this).next('.props').slideToggle();
            return false;
        })
        
        $('a[href="raw"]').click(function(){
            $(this).next('.raw').slideToggle();
            return false;
        })
        
        $('#toggle_empty').click(function(){
            $('.iblock.empty').slideToggle();
            return false;
        })
        
        $(".iblock_link").click(function () {
            var elementClick = $(this).attr("href");
            var $tb = $(elementClick)
console.log($tb.offset());
            var destination = parseInt($tb.offset().top)+64;
console.log(destination);
            $tb.addClass('highlighted');
            $('html').animate({ scrollTop: destination }, 800,'',function(){$tb.removeClass('highlighted')});
            
            return false; 
        });
    </script>
</foot>
</html>
