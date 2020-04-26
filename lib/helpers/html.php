<?
// \X\Helpers\Html::
namespace X\Helpers
{
    class Html
    {
        
        private static $_memoizing = [];
        
        
        # Возвращает грамотически верное существительное
        # $number - число
        # $titles - массив, например такой
        # \X\Helpers\Html::inclineNum($num,['','','']);
        /* array(
                'цифра', # 1
                'цифры', # 2
                'цифр'   # 10
            ) */
        public static function inclineNum ($number, $titles, $full=false)
        {  
            $cases = array (2, 0, 1, 1, 1, 2);
            $result = $titles[ ($number%100 > 4 && $number%100 < 20) ? 2 : $cases[min($number%10, 5)] ];
            return $full?$number.' '.$result:$result;  
        }
        
        /*
         * форматирует телефон
        */
        function phone ($phone = '', $convert = true)
        {
            $phoneCodes=Array(
                '375'=>Array(
                        'name'=>'Belarus',
                        'cityCodeLength'=>4,
                        'zeroHack'=>false,
                        'exceptions'=>Array(17,163,162,232,222),
                        'exceptions_max'=>3,
                        'exceptions_min'=>2
                    ),
                '380'=>Array(
                        'name'=>'Ukraine',
                        'cityCodeLength'=>4,
                        'zeroHack'=>true,
                        'exceptions'=>Array(44,432,1762,562,622,412,522,564,53615,642,322,448,629,512,482,532,3355,1821,403,222,1852,356,3371,267,3443,1694,1965,3058,1627,3385,3356,2718,3370,3260,3231,2785,309,2857,2957,2911,294,1705,3,295,3250,3387,2523,3246,2674,1854,3433,1711,251,2958,2477,2984,307,542,352,572,552,382,472,462,654),
                        'exceptions_max'=>5,
                        'exceptions_min'=>1
                    ),
                '8'=>Array(
                        'name'=>'Russia',
                        'cityCodeLength'=>5,
                        'zeroHack'=>false,
                        'exceptions'=>Array(4162,416332,8512,851111,4722,4725,391379,8442,4732,4152,4154451,4154459,4154455,41544513,8142,8332,8612,8622,3525,812,8342,8152,3812,4862,3422,342633,8112,9142,8452,3432,3434,3435,4812,3919,8432,8439,3822,4872,3412,3511,3512,3022,4112,4852,4855,3852,3854,8182,818,90,3472,4741,4764,4832,4922,8172,8202,8722,4932,493,3952,3951,3953,411533,4842,3842,3843,8212,4942,3912,4712,4742,8362,495,499,4966,4964,4967,498,8312,8313,3832,383612,3532,8412,4232,423370,423630,8632,8642,8482,4242,8672,8652,4752,4822,482502,4826300,3452,8422,4212,3466,3462,8712,8352,997,901,902,903,904,905,906,908,909,910,911,912,913,914,915,916,917,918,919,920,921,922,923,924,925,926,927,928,929,930,931,932,933,934,936,937,938,950,951,952,953,960,961,962,963,964,965,967,968,980,981,982,983,984,985,987,988,989),
                        'exceptions_max'=>8,
                        'exceptions_min'=>2
                    ),
                '7'=>Array(
                        'name'=>'Russia',
                        'cityCodeLength'=>5,
                        'zeroHack'=>false,
                        'exceptions'=>Array(4162,416332,8512,851111,4722,4725,391379,8442,4732,4152,4154451,4154459,4154455,41544513,8142,8332,8612,8622,3525,812,8342,8152,3812,4862,3422,342633,8112,9142,8452,3432,3434,3435,4812,3919,8432,8439,3822,4872,3412,3511,3512,3022,4112,4852,4855,3852,3854,8182,818,90,3472,4741,4764,4832,4922,8172,8202,8722,4932,493,3952,3951,3953,411533,4842,3842,3843,8212,4942,3912,4712,4742,8362,495,499,4966,4964,4967,498,8312,8313,3832,383612,3532,8412,4232,423370,423630,8632,8642,8482,4242,8672,8652,4752,4822,482502,4826300,3452,8422,4212,3466,3462,8712,8352,997,901,902,903,904,905,906,908,909,910,911,912,913,914,915,916,917,918,919,920,921,922,923,924,925,926,927,928,929,930,931,932,933,934,936,937,938,950,951,952,953,960,961,962,963,964,965,967,968,980,981,982,983,984,985,987,988,989),
                        'exceptions_max'=>8,
                        'exceptions_min'=>2
                    ),
                '1'=>Array(
                        'name'=>'USA',
                        'cityCodeLength'=>3,
                        'zeroHack'=>false,
                        'exceptions'=>Array(),
                        'exceptions_max'=>0,
                        'exceptions_min'=>0
                    )
                );
            
            if (empty($phone)) return '';
            
            $phoneBlocks = function ($number){
                $add='';
                if (strlen($number)%2)
                {
                    $add = $number[0];
                    $number = substr($number, 1, strlen($number)-1);
                }
                return $add.implode("-", str_split($number, 2));
            };
            
            // очистка от лишнего мусора с сохранением информации о "плюсе" в начале номера
            $phone=trim($phone);
            $plus = ($phone[0] == '+');
            $phone = preg_replace("/[^0-9A-Za-z]/", "", $phone);
            $OriginalPhone = $phone;
            // конвертируем буквенный номер в цифровой
            if ($convert == true && !is_numeric($phone)) {
                $replace = array('2'=>array('a','b','c'),
                '3'=>array('d','e','f'),
                '4'=>array('g','h','i'),
                '5'=>array('j','k','l'),
                '6'=>array('m','n','o'),
                '7'=>array('p','q','r','s'),
                '8'=>array('t','u','v'),
                '9'=>array('w','x','y','z'));
                foreach($replace as $digit=>$letters) {
                    $phone = str_ireplace($letters, $digit, $phone);
                }
            }
            // заменяем 00 в начале номера на +
            if (substr($phone, 0, 2)=="00")
            {
                $phone = substr($phone, 2, strlen($phone)-2);
                $plus=true;
            }
            // если телефон длиннее 7 символов, начинаем поиск страны
            if (strlen($phone)>7)
            foreach ($phoneCodes as $countryCode=>$data)
            {
                $codeLen = strlen($countryCode);
                if (substr($phone, 0, $codeLen)==$countryCode)
                {
                    // как только страна обнаружена, урезаем телефон до уровня кода города
                    $phone = substr($phone, $codeLen, strlen($phone)-$codeLen);
                    $zero=false;
                    // проверяем на наличие нулей в коде города
                    if ($data['zeroHack'] && $phone[0]=='0')
                    {
                        $zero=true;
                        $phone = substr($phone, 1, strlen($phone)-1);
                    }
                    $cityCode=NULL;
                    // сначала сравниваем с городами-исключениями
                    if ($data['exceptions_max']!=0)
                    for ($cityCodeLen=$data['exceptions_max']; $cityCodeLen>=$data['exceptions_min']; $cityCodeLen--)
                    if (in_array(intval(substr($phone, 0, $cityCodeLen)), $data['exceptions']))
                    {
                        $cityCode = ($zero ? "0" : "").substr($phone, 0, $cityCodeLen);
                        $phone = substr($phone, $cityCodeLen, strlen($phone)-$cityCodeLen);
                        break;
                    }
                    // в случае неудачи с исключениями вырезаем код города в соответствии с длиной по умолчанию
                    if (is_null($cityCode))
                    {
                        $cityCode = substr($phone, 0, $data['cityCodeLength']);
                        $phone = substr($phone, $data['cityCodeLength'], strlen($phone)-$data['cityCodeLength']);
                    }
                    // возвращаем результат
                    return ($plus ? "+" : "").$countryCode.' ('.$cityCode.') '.$phoneBlocks($phone);
                }
            }
            // возвращаем результат без кода страны и города
            return ($plus ? "+" : "").$phoneBlocks($phone);
        }
        #
        
        /* нормализует телефон для использования
        # \X\Helpers\Html::phoneNormal('8 800 123-45-46');
        */
        public static function phoneNormal (
                $phone,
                $len=0, // Проверять длину
                $coder = ['8'=>'7'] // Заменять код => код (Только при прохождении длины)
            )
        {
            $phone = preg_replace('![^0-9]+!', '', $phone);
            if ($len) {
                if (strlen($phone)!=$len) return false;
                foreach ($coder as $c=>$r) {
                    if (strpos($phone,$c)===0) { // если телефон начинаеся с кода...
                        $phone = $r.substr($phone,strlen($c)); // заменяем его
                    }
                }
            }
            
            return $phone;
        }
        #
        
        /* генерирует уникальные id
        $htmlID = \X\Helpers\Html::newID();
        */
        public static function newID ($name='xid',$rndlen=8)
        {
            $id = $name.'_'.randString($rndlen);
            while (self::$_memoizing['getID'][$id]) {
                $id = $name.'_'.randString($rndlen);
            }
            self::$_memoizing['lastID'] = $id;
            self::$_memoizing['getID'][$id] = true;
            return $id;
        }
        #
        
        /* возвращает последний сгенерированный id
        \X\Helpers\Html::lastID();
        */
        public static function lastID ()
        {
            return self::$_memoizing['lastID'];
        }
        #
        
        
        /* выводит данные в страницу \X\Helpers\Html::showData($data, ['js-map-data'=>false]) */
        public static function showData ($array, $arAttr=[])
        {
            echo self::data($array, $arAttr);
        }
        #
        
        /* возвращает данные для инлайн вставки =\X\Helpers\Html::data($data, ['js-map-data'=>false]) */
        public static function data ($array, $arAttr=[])
        {
            return '<script type="application/json" '.self::attrs($arAttr).'>'.json_encode($array).'</script>';
        }
        #
        
        /* показывает включаемый svg по имени */
        public static function showSvg ($name, $classPrefix='')
        {
            echo self::svg($name, $classPrefix);
        }
        #
        
        /* возвращает svg по имени */
        public static function svg ($name, $classPrefix='')
        {
            $file = S_P_SVG.'/'.$name.'.svg';
            if (file_exists($file)) {
                $svgStr = file_get_contents($file);
                if ($classPrefix) $svgStr = str_replace('<svg ','<svg class="'.$classPrefix.'-'.$name.'" ',$svgStr);
                return $svgStr;
            }
            return '';
        }
        #
        
        /* возвращает массив атрибутов в виде строки */
        public static function attrs ($arAttrs)
        {
            if (count($arAttrs) == 0) return '';
            $attrs = array();
            foreach ($arAttrs as $name=>$val) {
                if ($val !== false) {
                    $attrs[] = $name.'="'.$val.'"';
                } else $attrs[] = $name;
            }
            return implode(' ',$attrs);
        }
        #
        
        /* рендерит микрошаблон */
        public static function render ($tmpl,$data=array())
        {
            include(S_P_TMPL.'/'.$tmpl.'.php');
        }
        #
        
        
        
        /*
         * Делает текст безопасным
         * \X\Helpers\Html::html2text($text,true,250)
        */
        public static function html2text (
                $html,
                $strict=false, 
                $len=0
            )
        {
            $text = trim(strip_tags($html));
            if ($strict) $text = htmlspecialchars($text);
            if ($len > 0) $text = substr($text,0,$len);
            return $text;
        }
        #
        
        // legacy
        // depricated
        public static function DeclOfNum ($number, $titles)
        {
            \XDebug::log(
                    'Use depricated function DeclOfNum. Use inclineNum!',
                    'WARNING!'
                );
            return self::inclineNum($number, $titles);
        }
        
        
    }
}

