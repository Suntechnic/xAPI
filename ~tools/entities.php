<? // /local/x/tools/entitys_structure.php
require_once('.prolog.php');

// сущности
$arFilesEntity = glob(S_P_X.'/entities/[abcdefghijklmnopqrstuvwxyz]*.php');
$arEntities = [];
foreach ($arFilesEntity as $file) {
	$entetyName = ucfirst(str_replace('.php','',basename($file)));
	$entety = '\\Entity\\'.$entetyName;
	$arEntities[$entetyName] = [
			'class' => $entety,
			'file' => str_replace(S_,'',$file), // ! Внимание - наивное предположение
			'map' => $entety::getMap()
		];
}

?>
<div class="entity_panel">
    <?foreach($arEntities as $entetyName=>$arEntity):?>
        <a href="#entity_<?=$entetyName?>" class="entity_link"><?=$entetyName?></a>
    <?endforeach;?>
</div>

<section>
    
<?foreach($arEntities as $entetyName=>$arEntity):?>
<div id="entity_<?=$arIBlock['ID']?>" class="entity">
    <h2 class="title-entity">
		<?=$entetyName?>
    </h2>
    <a href="?ENTITY=<?=$entetyName?>&action=retable">Пересоздать таблицу</a><br>
    
    <a href="raw">MAP</a>
    <pre class="raw">
        <?print_r($arEntity['map']);?>
    </pre>
</div>
<?endforeach;?>
</section>
</body>
<foot>
	<script defer="defer">
        
        $('a[href="raw"]').click(function(){
            $(this).next('.raw').slideToggle();
            return false;
        })
        
        $(".entity_link").click(function () {
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
