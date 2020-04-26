<?
require_once('.prolog.php');

$arResults = array();

if ($_REQUEST['cacheclear'] == 'iblock') {
    
    global $CACHE_MANAGER;
    $CACHE_MANAGER->ClearByTag('x_iblock');
    $arResults[] = 'Кэш инфоблоков очищен';
}


foreach ($arResults as $result):?><p><?=$result?></p><?endforeach?>

<a href="?cacheclear=iblock">сбросить кэш инфоблоков</a><br>
<a href="iblocks_structure.php">инфоблоки</a>

<?require_once('.epilog.php');