<?
// Source Dump Manager
if (!defined('S_P_SOURCESDUMP')) return; // свалка ресрсов не определена

$path = $_SERVER['REQUEST_URI'];
if (
        strrpos($path, P_CSS) === 0
        || strrpos($path, P_JS) === 0
        || strrpos($path, P_IMAGES) === 0
        || strrpos($path, P_PICTURES) === 0
    ) {
   
   
    $file = basename($path);
    $pathinfo = pathinfo($path);
    
    $dirname = S_.$pathinfo['dirname'];
    
    // проверяем что файл есть в куче
    if (!file_exists(S_P_SOURCESDUMP.'/'.$file)) return; //... и выходим если его нет
    
    // создаем путь рекурсивно
    if (!is_dir($dirname)) mkdir($dirname,0775,true); // создадим директорию если нет
    if (!is_dir($dirname)) return; // выйдем если не удалось
    
    // скопируем файл из кучи
    if(copy(S_P_SOURCESDUMP.'/'.$file,$dirname.'/'.$file)) {
        $imgstr = 'image/png;base64,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';
        $new_data=explode(";",$imgstr);
        $type=$new_data[0];
        $data=explode(",",$new_data[1]);
        header("Content-type:".$type);
        
        echo base64_decode($data[1]);
        die();
    }
}

