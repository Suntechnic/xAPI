<?
namespace X\Abstraction {
    abstract class IBModel extends Model {
        
        const MODEL = 'iblock';
        
        public static function getInstance($ID=false) {
            if ($ID) {
                $ID = $ID;
            } else {
                $ID = static::IDIB;
            }
            
            return parent::getInstance($ID);
        }
        
        protected function __construct($ID) {
            if (!$ID) die('Invalid IBlock Id: '.$ID);
            $this->ID = $ID;
            
            return parent::__construct($ID);
        }
        
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
        
        /* сбрасывает тегированный кэш
         */
        public function cacheReset ($arSub=false) { // сбрасывает тегированный кэш
            $arInvalida = [];
            global $CACHE_MANAGER;
            if (!$arSub) { // если теги не переданые - скидываем все
                $arInvalida[] = 'x_iblock_id_'.$this->ID;
                $CACHE_MANAGER->ClearByTag('x_iblock_id_'.$this->ID);
            } else {
                foreach ($arSub as $sub) {
                    $arInvalida[] = 'x_iblock_id_'.$this->ID.'_'.$sub;
                    $CACHE_MANAGER->ClearByTag('x_iblock_id_'.$this->ID.'_'.$sub);
                }
            }
        }
        
        // возвращает ID инфоблока
        public function getId() {return $this->ID;}
        
        // возвращает Раздел
        public function getSection ($arFilter,$arSelect=false,$arOrder=false,$count=false) {
            $arSections = $this->getSections($arFilter,$arSelect,$arOrder,$count);
            return array_shift($arSections);
        }
        
        // возвращает словарь разделов
        public function getSections ($arFilter=array(),$arSelect=false,$arOrder=false,$count=false,$params=array()) {
            # TODO: прикрутить кэш
            //\CModule::includeModule("iblock");
            
            if (!is_array($params)) $params = array('caller'=>$params); $params['method'] = 'getSections';
            # http://dev.1c-bitrix.ru/api_help/iblock/classes/ciblocksection/getlist.php
            if (!$arSelect) {
                $arSelect = Array(
                        'ID',
                        'NAME',
                        'IBLOCK_ID'
                    );
            } else {
                if (in_array('ID',$arSelect)) $arSelect[] = 'ID';
            }
            
            if (!$arOrder) $arOrder = Array(
                    'SORT'=>'ASC'
                );
            $arFilter['IBLOCK_ID'] = $this->ID;
            
            \XDebug::log(
                    array(
                            'filter'=>$arFilter,
                            'select'=>$arSelect,
                            'order'=>$arOrder,
                        ),
                    'call getSections for '.$this->ID
                );
            $db_res = \CIBlockSection::GetList(
                    $arOrder,
                    $arFilter,
                    $count,
                    $arSelect
                );
            
            if (in_array('SECTION_PAGE_URL',$arSelect)) {
                while($arSection = $db_res->GetNext()) {
                    $arSections[$arSection['ID']] = $this->sectionPreprocessing($arSection,$params);
                }
            } else {
                while($arSection = $db_res->Fetch()) {
                    
                    $arSections[$arSection['ID']] = $this->sectionPreprocessing($arSection,$params);
                }
            }
            
            return $arSections;
        }
        
        
        // предобработка элемента
        function sectionPreprocessing ($arSection,$params) {return $arSection;}
        
        // возвращает элемент по фильтру
        public function getElement ($params=array(),$force=false) {
            
            if (!is_array($params)) $params = array('caller'=>$params); $params['method'] = 'getElement';
            
            $arFilter = $this->__getFilter();
            $arSelect = $this->__getSelect();
            $arOrder = $this->__getOrder(); // ордер нужен для форсированного получения верхнего элемента
            if (!in_array('ID',$arSelect)) $arSelect[] = 'ID';
            
            $cacheKey = 'IBModel_getElement_'.$this->cacheKey(array('filter'=>$arFilter,'select'=>$arSelect, 'order'=>$arOrder, 'params'=>$params));
            $cacheTime = $this->cacheTime('getElement');
            $obCache = new \CPHPCache();
            $cacheSubDir = 'elements';
            if (BX_COMP_MANAGED_CACHE && $obCache->InitCache(
                    $cacheTime,
                    $cacheKey,
                    $this->cDir.'/'.$cacheSubDir.'/'.$cacheKey
                )) {
                $result = $obCache->GetVars();
            } elseif ($obCache->StartDataCache() ) {
                ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                // начало тегирования
                global $CACHE_MANAGER;
                $CACHE_MANAGER->StartTagCache($this->cDir.'/'.$cacheSubDir.'/'.$cacheKey); { // Теги для групп элементов
                    ////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    ////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    \CModule::includeModule('iblock');
                    $db_res = \CIBlockElement::GetList(
                            $arOrder,
                            $arFilter,
                            false, false,
                            $arSelect
                        );
                    $arElms = array();
                    $CNT = $db_res->SelectedRowsCount();
                    $result = false;
                    
                    if ($CNT != 1) $result = $CNT;
                    if ($CNT == 1 || $force) {
                        if (in_array('DETAIL_PAGE_URL',$arSelect)) {
                            if($arElm = $db_res->GetNext()) $result = $this->elementPreprocessing($arElm,$params);
                        } else {
                            if($arElm = $db_res->Fetch()) $result = $this->elementPreprocessing($arElm,$params);
                        }
                    }
                    
                    if ($result) {
                        $arElms = [$result];
                        $arElms = $this->listPreprocessing($arElms,$params);
                        $result = $arElms[0];
                    }
                    ////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    ////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    $CACHE_MANAGER->RegisterTag('x_iblock_id_'.$this->ID.'_element_'.$result['ID']);
                    //$CACHE_MANAGER->StartTagCache($this->cDir.'/'.$cacheSubDir); { // Теги для групп элементов
                    //    $CACHE_MANAGER->RegisterTag('x_iblock_id_'.$this->ID.'_'.$cacheSubDir);
                    //    $CACHE_MANAGER->StartTagCache($this->cDir); { // общие теги для ИБ
                    //        $CACHE_MANAGER->RegisterTag('x_iblock');
                    //        $CACHE_MANAGER->RegisterTag('x_iblock_id_'.$this->ID);
                    //    } $CACHE_MANAGER->EndTagCache();
                    //} $CACHE_MANAGER->EndTagCache();
                } $CACHE_MANAGER->EndTagCache();
                $obCache->EndDataCache($result);
                // конец тегирования
                ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                if (is_array($result)) {} else $obCache->AbortDataCache();
                $cacheKey = '';//'--'.$cacheKey;
            }
            
            \XDebug::log(
                    array(
                            'filter'=>$arFilter,
                            'select'=>$arSelect,
                            'params'=> $params,
                            'cachetime' => $cacheTime,
                            'cachekey' => $cacheKey,
                            'result' => $result
                        ),
                    'call getElement for '.$this->ID.($cacheKey?' (from cache)':'')
                );
            
            return $result;
        }
        
        // возвращает количество элементов
        public function getCnt ($params=array()) {
            if (!is_array($params)) $params = array('caller'=>$params); $params['method'] = 'getCnt';
            
            \CModule::includeModule("iblock");
            $arFilter = $this->__getFilter();
            
            $cacheKey = 'IBModel_getCnt_'.$this->cacheKey(array(
                    'filter'=>$arFilter,
                    'params'=>$params
                ));
            $cacheTime = $this->cacheTime('getCnt');
            $obCache = new \CPHPCache();
            $cacheSubDir = 'elements';
            if (BX_COMP_MANAGED_CACHE && $obCache->InitCache(
                    $cacheTime,
                    $cacheKey,
                    $this->cDir.'/'.$cacheSubDir.'/'.$cacheKey
                )) {
                $CNT = $obCache->GetVars();
            } elseif ($obCache->StartDataCache() ) {
                ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
                // началь тегирования
                global $CACHE_MANAGER;
                $CACHE_MANAGER->StartTagCache($this->cDir.'/'.$cacheSubDir.'/'.$cacheKey); { // Теги для групп элементов
                    ////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    ////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    $db_res = \CIBlockElement::GetList(
                            array(),
                            $arFilter,
                            false, false,
                            array('ID')
                        );
                    $CNT = $db_res->SelectedRowsCount();
                } $CACHE_MANAGER->EndTagCache();
                // конец тегирования
                ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                $obCache->EndDataCache($CNT);
                $cacheKey = '';//'--'.$cacheKey;
            }
            \XDebug::log(
                    array(
                            'filter'=>$arFilter,
                            'params'=>$params,
                            'cachetime' => $cacheTime,
                            'cachekey' => $cacheKey
                        ),
                    'call getCnt for '.$this->ID.($cacheKey?' (from cache)':'')
                );
            return $CNT;
        }
        #
        
        // возвращает словарь элементов
        public function getDict ($key='ID', $arSelect=false, $params=array()) {
            if (!is_array($params)) $params = array('caller'=>$params); $params['method'] = 'getDict';
            
            \CModule::includeModule("iblock");
            # http://dev.1c-bitrix.ru/api_help/iblock/classes/ciblockelement/getlist.php
            if ($arSelect === false) {
                $arSelect = $this->__getSelect();
            } else $null = $this->__getSelect(); // чтобы сбросить одноразосвый селект

            if (!in_array($key,$arSelect)) $arSelect[] = $key;
            $arFilter = $this->__getFilter();
            $arOrder = $this->__getOrder();
            
            
            $cacheKey = 'IBModel_getDict_'.$this->cacheKey(array(
                    'filter'=>$arFilter,
                    'select'=>$arSelect,
                    'order'=>$arOrder,
                    'params'=>$params
                ));
            $cacheTime = $this->cacheTime('getDict');
            $obCache = new \CPHPCache();
            $cacheSubDir = 'elements';
            if (BX_COMP_MANAGED_CACHE && $obCache->InitCache(
                    $cacheTime,
                    $cacheKey,
                    $this->cDir.'/'.$cacheSubDir.'/'.$cacheKey
                )) {
                $arElms = $obCache->GetVars();
            } elseif ($obCache->StartDataCache() ) {
                ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
                // началь тегирования
                global $CACHE_MANAGER;
                $CACHE_MANAGER->StartTagCache($this->cDir.'/'.$cacheSubDir.'/'.$cacheKey); { // Теги для групп элементов
                    ////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    ////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    $db_res = \CIBlockElement::GetList(
                            $arOrder,
                            $arFilter,
                            false, false,
                            $arSelect
                        );
                    $arElms = array();
                    if(strpos($key,'PROPERTY_')===0)$key.='_VALUE';
                    
                    if (in_array('DETAIL_PAGE_URL',$arSelect)) {
                        while($arElm = $db_res->GetNext()) {
                            $arElms[$arElm[$key]] = $this->elementPreprocessing($arElm,$params);
                        }
                    } else {
                        while($arElm = $db_res->Fetch()) {
                            $arElms[$arElm[$key]] = $this->elementPreprocessing($arElm,$params);
                        }
                    }
                    
                    if (count($arElms)) $arElms = $this->listPreprocessing($arElms,$params);
                    
                    
                    foreach ($arElms as $arElm) {
                        //print ('<!-- regtag:'.$this->cDir.'/'.$cacheSubDir.'/'.$cacheKey.' --<pre>'.print_r('x_iblock_id_'.$this->ID.'_element_'.$arElm['ID'],true).'</pre>-->');
                        $CACHE_MANAGER->RegisterTag('x_iblock_id_'.$this->ID.'_element_'.$arElm['ID']);
                    }
                    ////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    ////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    //$CACHE_MANAGER->StartTagCache($this->cDir.'/'.$cacheSubDir); { // Теги для групп элементов
                    //    $CACHE_MANAGER->RegisterTag('x_iblock_id_'.$this->ID.'_'.$cacheSubDir);
                    //    $CACHE_MANAGER->StartTagCache($this->cDir); { // общие теги для ИБ
                    //        $CACHE_MANAGER->RegisterTag('x_iblock');
                    //        $CACHE_MANAGER->RegisterTag('x_iblock_id_'.$this->ID);
                    //    } $CACHE_MANAGER->EndTagCache();
                    //} $CACHE_MANAGER->EndTagCache();
                } $CACHE_MANAGER->EndTagCache();
                // конец тегирования
                ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                $obCache->EndDataCache($arElms);
                $cacheKey = '';//'--'.$cacheKey;
            }
            \XDebug::log(
                    array(
                            'filter'=>$arFilter,
                            'select'=>$arSelect,
                            'order'=>$arOrder,
                            'params'=>$params,
                            'cachetime' => $cacheTime,
                            'cachekey' => $cacheKey
                        ),
                    'call getDict for '.$this->ID.($cacheKey?' (from cache)':'')
                );
            return $arElms;
        }
        #
        
        
        // возвращает словарь элементов
        public function getPage ($pageNum = 1, $pageSize = false, $params=array()) {
            if (!is_array($params)) $params = array('caller'=>$params); $params['method'] = 'getPage';
            if (!$pageSize) {
                $pageSize = 12;
                if ($this->pageSize > 0) $pageSize = $this->pageSize;
            }
            
            # TODO: прикрутить кэш
            \CModule::includeModule("iblock");
            # 
            $arSelect = $this->__getSelect();
            $arFilter = $this->__getFilter();
            $arOrder = $this->__getOrder();
            $arNav = array(
                    'nTopCount' => false,
                    'iNumPage' => $pageNum,
                    'nPageSize' => $pageSize,
                    'checkOutOfRange' => true
                );
            
            \XDebug::log(
                    array(
                            'filter'=>$arFilter,
                            'select'=>$arSelect,
                            'order'=>$arOrder,
                            'nav' => $arNav
                        ),
                    'call getPage for '.$this->ID
                );
            
            $db_res = \CIBlockElement::GetList(
                    $arOrder,
                    $arFilter,
                    false, 
                    $arNav,
                    $arSelect
                );
            $arElms = array();
            
            if (in_array('DETAIL_PAGE_URL',$arSelect)) {
                while($arElm = $db_res->GetNext()) {
                    $arElms[] = $this->elementPreprocessing($arElm,$params);
                }
            } else {
                while($arElm = $db_res->Fetch()) {
                    $arElms[] = $this->elementPreprocessing($arElm,$params);
                }
            }
            
            if (count($arElms)) $arElms = $this->listPreprocessing($arElms,$params);
            
            $arPage = array(
                    'ITEMS' => $arElms,
                    'NAVIGATION' => array(
                        'NavRecordCount' => $db_res->NavRecordCount,
                        'NavPageCount' => $db_res->NavPageCount,
                        'NavPageNomer' => $db_res->NavPageNomer,
                        'NavPageSize' => $db_res->NavPageSize,
                        'nStartPage' => $db_res->nStartPage,
                        'nEndPage' => $db_res->nEndPage,
                    )
                );
            
            return $arPage;
        }
        #
        
        // предобработка элемента
        function elementPreprocessing ($arElement,$params) {return $arElement;}
        function listPreprocessing ($arElements,$params) {return $arElements;}
        
        
        // возвращает справочник элементов
        public function getReference ($key='ID',$val='NAME') {
            
            \CModule::includeModule('iblock');
            $arFilter = $this->__getFilter();
            $arOrder = $this->__getOrder();
            $arSelect = $this->__getSelect();
            $db_res = \CIBlockElement::GetList(
                    $arOrder,
                    $arFilter,
                    false, false,
                    array($key,$val)
                );
            $arElms = array();
            if(strpos($key,'PROPERTY_')===0)$key.='_VALUE';
            if(strpos($val,'PROPERTY_')===0)$val.='_VALUE';
            
            if (in_array('DETAIL_PAGE_URL',$this->getSelect)) {
                while($arElm = $db_res->GetNext()) {
                    $arElms[$arElm[$key]] = $arElm[$val];
                }
            } else {
                while($arElm = $db_res->Fetch()) {
                    $arElms[$arElm[$key]] = $arElm[$val];
                }
            }
            
            
            \XDebug::log(
                    array(
                            'filter'=>$arFilter,
                            'order'=>$arOrder,
                            'result'=>$arElms
                        ),
                    'call getReference for '.$this->ID
                );
            
            
            return $arElms;
        }
        
        
        /*
         * Исполтьзуется во внутренних методах - обертка над getFilter
         * добавляет id инфоблока, чтобы гарантировать инфоблок
         * и разворачивает подзапросы
        */
        private function __getFilter () {

            
            $arFilter = parent::__getFilter();
            //$this->lastFilter = $arFilter;
            
            $arFilter['IBLOCK_ID'] = $this->GetID();
            
            // развертывание подзапровосов
            
            if ($arFilter['X_SUBQUERIES']) { // фильтр содержит подзапросы
                foreach ($arFilter['X_SUBQUERIES'] as $arSubQuery) {
                    //$arSubQuery = [
                    //        'model' => 'Catalog',
                    //        'property' => 'OBJECT', // свойство элемента модели model
                    //                                  к которому привязан ID элемента этой модели
                    //        'filter' => [
                    //                'ID' => [92,55]
                    //            ],
                    //    ];
                    // подзапросы развертываются через модели, чтобы обеспечить поддержку
                    // проектозависимых модификаций
                    $modelName = '\Model\\'.$arSubQuery['model'];
                    $model = $modelName::getInstance();
                    $arFilter[] = ['ID' => $model->SubQuery($arSubQuery['property'],$arSubQuery['filter'])];
                }
                unset($arFilter['X_SUBQUERIES']);
                
            }
            
            if ($arFilter['X_BACKSUBQUERIES']) { // фильтр содержит обратные подзапросы
                foreach ($arFilter['X_BACKSUBQUERIES'] as $arSubQuery) {
                    //$arSubQuery = [
                    //        'model' => 'Catalog',
                    //        'property' => 'OBJECT', // свойство этой модели, к которому привязан ID элемента модели model
                    //        'filter' => [
                    //                'ID' => [92,55]
                    //            ],
                    //    ];
                    // подзапросы развертываются через модели, чтобы обеспечить поддержку
                    // проектозависимых модификаций
                    $modelName = '\Model\\'.$arSubQuery['model'];
                    $model = $modelName::getInstance();
                    
                    $arRef = $model->setFilter($arSubQuery['filter'])->getReference('ID','ID');
                    if (count($arRef)) {
                        $arFilter[] = [
                                'PROPERTY_'.$arSubQuery['property'] => $arRef
                            ];
                    } else {
                        $arFilter[] = [
                                'PROPERTY_'.$arSubQuery['property'] => 0
                            ];
                    }
                    
                }
                unset($arFilter['X_BACKSUBQUERIES']);
            }
            
            return $arFilter;
        }
        
        public function SubQuery ($property,$arFilter) {
            $this->setParams(['filter'=>$arFilter]);
            return \CIBlockElement::SubQuery('PROPERTY_'.$property,$this->__getFilter());
        }
        
        
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// добавление
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// и апдейт элементов
        
        
        // удаляет элемент
        public function delete ($ELEMENT_ID) {
            if (is_numeric($ELEMENT_ID) && $ELEMENT_ID > 0) {
                $this->setParams(['filter'=>['ID'=>$ELEMENT_ID]]);
            } elseif (is_array($ELEMENT_ID)) {
                $this->setParams(['filter'=>$ELEMENT_ID]);
            }
            
            $arElms = $this->getReference('ID','ID');

            
            
            foreach ($arElms as $idk=>$idv) {
                $r = \CIBlockElement::Delete($idv);
                $arElms[$idk] = $r?$r:false;
            }
            
            return $arElms;
        }
        #
        
        
        // добавляет элемент или обновляет элемент
        public function apd (
                $arFields,$arProps=array(), // элемент
                $arPrice=array() // только для каталога
            ) {
            
            
            $r = array(
                    'ID' => $ID,
                    'rs' => ($ID>0),
                    'el' => $el,
                    'op' => 'none'
                );
            if ($arFields['ID'] > 0) {
                $r = $this->update($arFields,$arProps,$arPrice);
                $r['op'] = 'update';
            } else {
                $r = $this->add($arFields,$arProps,$arPrice);
                $r['op'] = 'add';
            }
            
            return $r;
        }
        #
        
        // добавляет элемент
        public function add (
                $arFields,$arProps=array(), // элемент
                $arPrice=array(), // только для каталога
                $bWorkFlow=false,$bUpdateSearch=false,$bResizePictures=true
            ) {
            
            \CModule::includeModule('iblock');
            
            $el = new \CIBlockElement;

            //$arFields = array_filter(
            //        $arPropFields,
            //        function($k){return ('PROPERTY_'!=substr($k,0,9));},
            //        ARRAY_FILTER_USE_KEY
            //    );
            //$arProps = array_filter(
            //        $arPropFields,
            //        function($k){return ('PROPERTY_'==substr($k,0,9));},
            //        ARRAY_FILTER_USE_KEY
            //    );
            if (array_key_exists($arFields['ID'])) unset($arFields['ID']);
            $arFields['IBLOCK_ID'] = $this->ID;
            if (
                    is_array($arProps) &&
                    count($arProps)
                ) $arFields['PROPERTY_VALUES'] = $arProps;
            
            $ID = $el->Add($arFields,$bWorkFlow,$bUpdateSearch,$bResizePictures);
            
            // если есть цена
            if ($arPrice['PRICE'] > 0 && $ID > 0) {
                \CCatalogProduct::Add(['ID'=>$ID]); // превращаем в товар
                $arPrice['PRODUCT_ID'] = $ID;
                \CPrice::Add($arPrice); // добваляем цену
            }
            
            if ($ID>0) $this->cacheReset(['element_']); // сбрасываем кэш неопределенных элементов
            return array(
                    'ID' => $ID,
                    'rs' => ($ID>0),
                    'el' => $el
                );
        }
        #
        
        
        // обновляет элемент
        public function update (
                $arFields,$arProps=array(),
                $arPrice=array() // только для каталога
            ) {
            
            \CModule::includeModule('iblock');
            
            $el = new \CIBlockElement;
            
            $ID = intval($arFields['ID']);
            if ($arFields['ID'] > 0) {
                unset($arFields['ID']);
                $arFields['IBLOCK_ID'] = $this->ID;
                if (
                        is_array($arProps) &&
                        count($arProps)
                    ) $arFields['PROPERTY_VALUES'] = $arProps;
            }
            $rs = $el->Update($ID,$arFields);

            if ($rs) {
                $this->cacheReset(['element_','element_'.$ID]); // сбрасываем кэш неопределенных элементов и обновленного
                #TODO: если есть цена - Обновляем ее
                //if ($arPrice['PRICE'] > 0 && $ID > 0) {
                //    \CCatalogProduct::Add(['ID'=>$ID]); // превращаем в товар
                //    $arPrice['PRODUCT_ID'] = $ID;
                //    \CPrice::Add($arPrice);
                //}
            }
            
            return array(
                    'ID' => $ID,
                    'rs' => $rs,
                    'el' => $el
                );
        }
        #
        
        // обновляет свойство для группы элементов
        // $arValues - массив вида ID элемента => array(КодСвойства=>ЗначениеСвойства)
        // обертка над https://dev.1c-bitrix.ru/api_help/iblock/classes/ciblockelement/setpropertyvaluesex.php
        public function updatesProperty ($arValues, $type_casting=true) {
            
            \CModule::includeModule('iblock');
            //global $DB; $DB->StartTransaction();

            $IBLOCK_ID = $this->GetID();
            
            if ($type_casting) {
                $arPropsRef = $this->getPropsDict([],'CODE');
            }
            //
            $arCacheTags = ['element_'];
            foreach ($arValues as $ID=>$arProps) {
                if ($ID) {
                    if ($type_casting) {
                        foreach ($arProps as $code=>$value) {
                            if ($arPropsRef[$code]['PROPERTY_TYPE'] == 'N') {
                                $arProps[$code] = str_replace([',',' '],['.',''],$value);
                            }
                        }
                    }
                    \CIBlockElement::SetPropertyValuesEx($ID, $IBLOCK_ID, $arProps); // Обновляем массив свойств типа файл
                    $arCacheTags[] = 'element_'.$ID;
                }
            }
            //$DB->Commit();
            
            $this->cacheReset($arCacheTags);
            return $results;
        }
        #
        
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// работа
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// со свойствами
        // возвращает словарь свойств
        public function getPropsDict ($arFilter=array(),$key='ID') {
            $arFilter['IBLOCK_ID'] = $this->ID;
            
            $cacheKey = 'IBModel_getPropsDict_'.$this->cacheKey(array('filter'=>$arFilter,'key'=>$key));
            $obCache = new \CPHPCache();
            
            
            $cacheSubDir = 'props';
            if (BX_COMP_MANAGED_CACHE && $obCache->InitCache(
                    $this->cacheTime('getPropsDict'),
                    $cacheKey,
                    $this->cDir.'/'.$cacheSubDir
                )) {
                $result = $obCache->GetVars();
                
            } elseif ($obCache->StartDataCache() ) {
                
                $cacheKey = '';
                $properties = \CIBlockProperty::GetList(Array('sort'=>'asc', 'name'=>'asc'), $arFilter);
                $result = array();
                
                while ($prop_fields = $properties->Fetch()) {
                    
                    if ($prop_fields['PROPERTY_TYPE'] == 'L') { // списки
                        if ($prop_fields['LIST_TYPE'] == 'L' || $prop_fields['LIST_TYPE'] == 'C') {
                            $prop_fields['ENUM'] = array();
                            $db_enum_list = \CIBlockProperty::GetPropertyEnum(
                                    $prop_fields['ID'],
                                    Array('sort'=>'asc', 'value'=>'asc'),
                                    Array('IBLOCK_ID'=>$this->ID)
                                );
                            while ($arEnum = $db_enum_list->Fetch()) {
                                $arEnum['LABEL'] = $arEnum['VALUE'];
                                $prop_fields['ENUM'][$arEnum["ID"]] = $arEnum;
                            }
                        }
                    } elseif ($prop_fields['PROPERTY_TYPE'] == 'S') { // строки
                        if ($prop_fields['USER_TYPE'] == 'directory'
                                && $prop_fields['USER_TYPE_SETTINGS']['TABLE_NAME'] != '') { // справочник
                            $prop_fields['ENUM'] =
                                    \X\Helpers\HLReference::getReference($prop_fields['USER_TYPE_SETTINGS']['TABLE_NAME'])
                                    ->data('UF_XML_ID');
                            $prop_fields['ENUM'] = array_map(function($r){
                                    $r['LABEL'] = $r['UF_NAME'];
                                    return $r;
                                },$prop_fields['ENUM']);
                        }
                    }
                    
                    $result[$prop_fields[$key]] = $prop_fields;
                }
                
                if (count($result)) {
                    global $CACHE_MANAGER;
                    $CACHE_MANAGER->StartTagCache($this->cDir.'/'.$cacheSubDir); {
                        $CACHE_MANAGER->RegisterTag('x_iblock_id_'.$this->ID.'_props');
                    } $CACHE_MANAGER->EndTagCache();
                    $obCache->EndDataCache($result);
                } else $obCache->AbortDataCache();
            }
            
            return $result;
        }
        #
        
        // возвращает справочник значений свойств типа список
        // ->getPropsEnumRef(array(),'CODE','VALUE','ID'); справочник ID по значениям
        public function getPropsEnumRef (
                $arFilter=array(), // фильтр свойств
                $keyProp='ID', // ключ свойств
                $keyEnum='ID',$keyVal='VALUE' // ключ и значение справочника
            ) {
            $arFilter['PROPERTY_TYPE'] = 'L';
            $arProps = $this->getPropsDict($arFilter,$keyProp);
            
            $arRefS = array();
            foreach ($arProps as $key=>$arProp) {
                $arRef = array();
                foreach ($arProp['ENUM'] as $arEnum) {
                    $arRef[$arEnum[$keyEnum]] = $arEnum[$keyVal];
                }
                $arRefS[$key] = $arRef;
            }
            
            return $arRefS;
        }
        #
        
        // возвращает справочник значений свойств типа строка
        // ->getPropsStingRef(array(),'CODE','VALUE','ID');
        public function getPropsStringRef (
                $arFilter=array(), // фильтр свойств
                $keyProp='ID' // ключ свойств
            ) {
            
            
            $arFilter['PROPERTY_TYPE'] = 'S';
            $arProps = $this->getPropsDict($arFilter,$keyProp);
            
            // теперь нужно собрать значения
            // Получим карту id в ключ
            $arPropsMapID = array_column($arProps, $keyProp, 'ID');
            
            $arPropsIDs = array_map(function ($priop) {return 'PROPERTY_'.$priop;},array_keys($arPropsMapID));
            
            $arDict = $this->setParams(['select'=>$arPropsIDs, 'filter'=>['ACTIVE'=>'Y']])->getDict();
            
            // преобразуем id к ключам в которых хранятся значения свойств
            $arPropsMapValueKey = [];
            foreach ($arPropsMapID as $idProp=>$keyRef) {
                $arPropsMapValueKey['PROPERTY_'.$idProp.'_VALUE'] = $keyRef;
            }
            unset($arPropsMapID,$arPropsIDs);
            
            
            // создадим блоки ENUM
            foreach ($arProps as $key=>$arProp) $arProps[$key]['ENUM'] = [];
            
            // соберем значения
            foreach ($arDict as $arElement) {
                foreach ($arPropsMapValueKey as $key=>$keyRef) {
                    if ($arElement[$key]) {
                        if (is_array($arElement[$key])) {
                            $arProps[$keyRef]['ENUM'] = array_merge($arProps[$keyRef]['ENUM'],$arElement[$key]);
                        } else {
                            $arProps[$keyRef]['ENUM'][] = $arElement[$key];
                        }
                    }
                }
            }
            
            // уникализируем их и оставляем тлько справочники
            $arRefS = [];
            foreach ($arProps as $key=>$arProp) {
                $arRefS[$key] = array_unique($arProps[$key]['ENUM']);
                sort($arRefS[$key]);
            }
            
            
            return $arRefS;
        }
        #
    }
}

