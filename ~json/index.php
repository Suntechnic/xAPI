<?
$arResponce = [
        'code' => 0,
        'errors' => [],
        'debug' => [],
        'result' => []
    ];

if ($_GET['model'] && $_GET['method']) {
    
    $_REQUEST['appajax'] = $_REQUEST['appajax']!=''?$_REQUEST['appajax']:'y';
    
    $arAllow = include('allow.php');
    
    if (in_array($_GET['method'],$arAllow[$_GET['model']])) {
        
        require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
        
        $modelRequest = $_GET['model'];
        $methodRequest = $_GET['method'];
        
        $entetyName = '\\Model\\'.$modelRequest;
        if (class_exists($entetyName)) {
            $entety = $entetyName::getInstance();
            
            if (method_exists($entety, $methodRequest)) {
                
                if (is_array($_GET['params'])) {
                    $arResponce['result'] = $entety->$methodRequest(...$_GET['params']);
                } else $arResponce['result'] = $entety->$methodRequest();
                
                
            } else {
                $arResponce['code'] = 2; // ошибка модели или метода
                $arResponce['errors'][] = [
                        'code' => 22, // ошибка метода
                        'text' => 'unknown method "'.$methodRequest.'"'
                    ];
            }
        } else {
            $arResponce['code'] = 2; // ошибка модели или метода
            $arResponce['errors'][] = [
                    'code' => 21, // ошибка модели
                    'text' => 'unknown model'
                ];
        }
        
        $arResponce['debug'] = \XDebug::getLog();
    } else {
        $arResponce['code'] = 2; // ошибка модели или метода
            $arResponce['errors'][] = [
                    'code' => 20, // модель или метод запрещены
                    'text' => 'model or method is not allow'
                ];
    }
} else {
    $arResponce['code'] = 1; // ошбка обращения к интерфейсу
    $arResponce['errors'][] = [
            'code' => 10, // ошибка обращения к интерфейсу
            'text' => 'undefined model or method'
        ];
}


if ($arResponce['debug'] == '') unset($arResponce['debug']); 

header("Content-type: application/json; charset=utf-8");
echo json_encode($arResponce);
die();