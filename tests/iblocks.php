<?
// тестирования модели инфоблоков
\Bitrix\Main\Loader::includeModule('iblock');

$arMesssages = [];

// создание 
$arFields = Array(
    'ID'=>'x_tests',
    'SECTIONS'=>'Y',
    'IN_RSS'=>'N',
    'SORT'=>100,
    'LANG' => Array(
			'ru'=>Array(
					'NAME'=>'Тестовые инфоблоки X.API',
					'SECTION_NAME' => 'Разделы',
					'ELEMENT_NAME' => 'Элементы'
				)
        )
    );

$obBlocktype = new CIBlockType;
$DB->StartTransaction();
$res = $obBlocktype->Add($arFields);
if (!$res) {
	$DB->Rollback();
	$arMesssages[] = [
			'text' => 'Error: '.$obBlocktype->LAST_ERROR,
			'type' => 'fail'
		];
	return $arMesssages;
} else {
   $DB->Commit();
}


$ib = new CIBlock;

$arFields = Array(
	"ACTIVE" => 'Y',
	"NAME" => 'Тестовый инфоблок 1',
	"CODE" => 'x_test_1',
	"IBLOCK_TYPE_ID" => 'x_tests',
	"SITE_ID" => Array('s1'),
	"SORT" => 100
);

$ID_IB1 = $ib->Add($arFields);
if (!$ID_IB1) {
	$arMesssages[] = [
			'text' => 'Error: '.$ib->LAST_ERROR,
			'type' => 'fail'
		];
   return $arMesssages;
}


$arFields = Array(
	"ACTIVE" => 'Y',
	"NAME" => 'Тестовый инфоблок 2',
	"CODE" => 'x_test_2',
	"IBLOCK_TYPE_ID" => 'x_tests',
	"SITE_ID" => Array('s1'),
	"SORT" => 100
);

$ID_IB2 = $ib->Add($arFields);
if (!$ID_IB2) {
	$arMesssages[] = [
			'text' => 'Error: '.$ib->LAST_ERROR,
			'type' => 'fail'
		];
	return $arMesssages;
}

Bitrix\Main\Loader::registerAutoLoadClasses('x.api',  array(
		// тестовые классы
		'\X\Tests\IBlock1' => 'tests/model/iblock1.php',
		'\X\Tests\IBlock2' => 'tests/model/iblock2.php'
	));

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// данные созадны

try {
	$ib1 = \X\Tests\IBlock1::getInstance($ID_IB1);
	$ib2 = \X\Tests\IBlock2::getInstance($ID_IB2);
	$ib1_2 = \X\Tests\IBlock1::getInstance($ID_IB1);
	
	$arMesssages[] = [
			'text' => 'Модели инстанцированы',
			'type' => 'ok'
		];
	if ($ib1->getId() == $ID_IB1
			&& $ib1_2->getId() == $ID_IB1
			&& $ib2->getId() == $ID_IB2
		) {
		
		$arMesssages[] = [
				'text' => 'Изоляция объектов в норме',
				'type' => 'ok'
			];
		// установка времени кэширования
		$defCaсруTime = $ib1->cacheTime();
		
		$arMesssages[] = [
				'text' => 'Время кэширования по умолчанию: '.$defCaсруTime,
				'type' => 'info'
			];
		$arMesssages[] = [
				'text' => 'Время длительного кэширования по умолчанию: '.$ib1->cacheTime('long'),
				'type' => 'info'
			];
		
		$ib1->cacheTime('getElement', 100500);
		if ($ib1->cacheTime('getElement') == 100500
				&& $ib1_2->cacheTime('getElement') == 100500
				&& $defCaсруTime == $ib1->cacheTime()
				
			) {
			$arMesssages[] = [
					'text' => 'Установка времини кэширования работает штатно',
					'type' => 'ok'
				];
		} else {
			$arMesssages[] = [
					'text' => 'Ошибка установки времени кэширования',
					'type' => 'error'
				];
		}
		
		if ($ib1->cacheTime() != $ib2->cacheTime()) {
			$arMesssages[] = [
					'text' => 'Относительный сдвиг кэша работает',
					'type' => 'ok'
				];
		} else {
			$arMesssages[] = [
					'text' => 'Относительный сдвиг кэша не работает или нарушена изоляция',
					'type' => 'error'
				];
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////////////
		// параметры - селект и фильтр
		$selfSelect = $ib1->getSelect();
		ksort($selfSelect);
		
		// Select
		$ib1->setSelect(['CODE','ID']);
		$ib1->add2Select(['NAME']);
		$referenceSetSelect = ['CODE','ID','NAME'];
		ksort($referenceSetSelect);
		
		$setSelect = $ib1->getSelect();
		ksort($setfSelect);
		
		if (serialize($setSelect) == serialize($referenceSetSelect)) {
			$arMesssages[] = [
					'text' => 'Установка Select работает',
					'type' => 'ok'
				];
		} else {
			$arMesssages[] = [
					'text' => 'Ошибка установки Select',
					'type' => 'error'
				];
		}
		
		// фидльтр
		$ib1->setFilter(['CODE'=>'CODE','ID'=>1]);
		$ib1->add2Filter(['NAME'=>'name']);
		$referenceSetFilter = ['CODE'=>'CODE','ID'=>1,'NAME'=>'name'];
		ksort($referenceSetFilter);
		
		$setFilter = $ib1->getFilter();
		ksort($setfFilter);
		
		if (serialize($setFilter) == serialize($referenceSetFilter)) {
			$arMesssages[] = [
					'text' => 'Установка Filter работает',
					'type' => 'ok'
				];
		} else {
			$arMesssages[] = [
					'text' => 'Ошибка установки Filter',
					'type' => 'error'
				];
		}
		
		// одно разовые параметры
		$paramSelect = ['ID','CODE'];
		$paramFilter = ['ID' => 1];
		ksort($paramSelect);
		ksort($paramFilter);
		$ib1->setParams([
				'select' => $paramSelect,
				'filter' => $paramFilter
			]);
		$paramGetSelect = $ib1->getSelect();
		$paramGetFilter = $ib1->getFilter();
		ksort($paramGetSelect);
		ksort($paramGetFilter);
		if (serialize($paramSelect) == serialize($paramGetSelect)
				&& serialize($paramFilter) == serialize($paramGetFilter)
			) {
			$arMesssages[] = [
					'text' => 'Установка Params работает',
					'type' => 'ok'
				];
		} else {
			$arMesssages[] = [
					'text' => 'Ошибка установки Params',
					'context' => [
							'setting' => [
								'select' => $paramSelect,
								'filter' => $paramFilter
							],
							'getting' => [
								'select' => $paramGetSelect,
								'filter' => $paramGetFilter
							]
						],
					'type' => 'error'
				];
		}
		
		// сброс парамс
		$setSelect = $ib1->getSelect();
		ksort($setfSelect);
		$setFilter = $ib1->getFilter();
		ksort($setfFilter);
		if (serialize($setSelect) == serialize($referenceSetSelect)
				&& serialize($setFilter) == serialize($referenceSetFilter)
			) {
			$arMesssages[] = [
					'text' => 'Возврат после считывания Params работает',
					'type' => 'ok'
				];
		} else {
			$arMesssages[] = [
					'text' => 'Ошибка возврата после Params',
					'type' => 'error'
				];
		}
		
		// сброс селект
		$ib1->resetSelect();
		$self2Select = $ib1->getSelect();
		ksort($self2Select);
		if (serialize($selfSelect) == serialize($self2Select)) {
			$arMesssages[] = [
					'text' => 'Сброс Select работает',
					'type' => 'ok'
				];
		} else {
			$arMesssages[] = [
					'text' => 'Ошибка сброса Select',
					'type' => 'error'
				];
		}
		
	} else {
		$arMesssages[] = [
				'text' => 'Нарушена изоляция',
				'type' => 'error'
			];
	}
} catch (Exception $e) {
	$arMesssages[] = [
			'text' => 'Выброшено исключение: ',  $e->getMessage(),
			'type' => 'fail'
		];
}


// удаление данных
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



CIBlockType::Delete('x_tests');

return $arMesssages;