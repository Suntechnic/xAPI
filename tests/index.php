<?
// вместо хедера
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
?>
<style>
	.mes-info {
		color: navy;
	}
	.mes-ok {
		color: green;
	}
	.mes-error {
		color: red;
	}
	.mes-fail {
		color: red;
		font-weight: 800;
	}
</style>
<div>
<h2>Тест модели инфоблоков</h2>
<?
$arResult = include($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/x.api/tests/iblocks.php');
foreach ($arResult as $arMes) {
	?><span class="mes-<?=$arMes['type']?>"><?=$arMes['text']?></span><br><?
	if ($arMes['context']):?>
	<pre>
		<?print_r($arMes['context'])?>
	</pre>
	<?endif;
}
?>
</div>
<?
//
//$actions = \Model\Actions::getInstance();
//$actions->setOrder(['UUID'=>'DESC']);
//print('<-- xdebug --<pre>'.print_r($actions->setFilter(['UUID'=>'8a473854-d45f-ed7c-0173-378d53cc7b62'])->ref(),true).'</pre>-->');