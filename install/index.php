<?
global $MESS;
$strPath2Lang = str_replace("\\", "/", __FILE__);
$strPath2Lang = substr($strPath2Lang, 0, strlen($strPath2Lang)-strlen("/install/index.php"));
include(GetLangFileName($strPath2Lang."/lang/", "/install/index.php"));

class x_api extends CModule {
	
	var $MODULE_SPACE = "x";
	var $MODULE_UID = "api";
	var $MODULE_TYPE = "M";
	var $MODULE_CODE = "API";
	var $MODULE_EVENTS = array();
	
	var $MODULE_DIR;
	var $MODULE_DIR_ABS;
    var $MODULE_ID;
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;
	
    function __construct() {
		
		$this->MODULE_DIR = $this->MODULE_SPACE.'.'.$this->MODULE_UID;
		$this->MODULE_DIR_ABS = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_DIR;
		$this->MODULE_ID = $this->MODULE_SPACE.'.'.$this->MODULE_UID;
		
        $arModuleVersion = array();
        
        $path = str_replace("\\", "/", __FILE__);
        $path = substr($path, 0, strlen($path) - strlen("/index.php"));
        include($path."/version.php");
		
        if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        }
		
		$this->MODULE_SP = "X_".$this->MODULE_TYPE."_".$this->MODULE_CODE."_";
        
        $this->MODULE_NAME = GetMessage($this->MODULE_SP."INSTALL_NAME");
		$this->MODULE_DESCRIPTION = GetMessage($this->MODULE_SP."INSTALL_DESCRIPTION");
		$this->PARTNER_NAME = GetMessage($this->MODULE_SP."PARTNER");
		$this->PARTNER_URI = GetMessage($this->MODULE_SP."PARTNER_URI");
    }
	
	
	function _getComponents() {
		return array_map(
				function ($p) {return str_replace($this->MODULE_DIR_ABS.'/install/components/'.$this->MODULE_SPACE.'/','',$p);}, 
				glob($this->MODULE_DIR_ABS.'/install/components/'.$this->MODULE_SPACE.'/[abcdefghijklmnopqrstuvwxyz\.]*')
			);
    }
    
    function InstallFiles() {
		// компоненты
		$arComponents = $this->_getComponents();
		if (count($arComponents)) {
			foreach ($arComponents as $compName) {
				CopyDirFiles(
						$this->MODULE_DIR_ABS.'/install/components/'.$this->MODULE_SPACE.'/'.$compName,
						$_SERVER['DOCUMENT_ROOT'].'/bitrix/components/'.$this->MODULE_SPACE.'/'.$compName,
						true,
						true
					);
			}
			
		}
        
        return true;
    }
    
    function UnInstallFiles() {
		// компоненты
		$arComponents = $this->_getComponents();
		if (count($arComponents)) {
			foreach ($arComponents as $compName) {
				DeleteDirFilesEx('/bitrix/components/'.$this->MODULE_SPACE.'/'.$comp_name);
			}
		}
		
		// файлы в upload если модуль насоздавал
		DeleteDirFilesEx('/upload/'.$this->MODULE_ID);
        return true;
    }
	
	function _getEntities() {
		$arFilesEntity = glob($this->MODULE_DIR_ABS.'/lib/[abcdefghijklmnopqrstuvwxyz]*_table.php');
		if (count($arFilesEntity)) {
			foreach ($arFilesEntity as $file) {
				$entityName = '\\'.ucfirst($this->MODULE_SPACE)
						.'\\'.ucfirst($this->MODULE_UID)
						.'\\'.ucfirst(str_replace('_table.php','',basename($file))).'Table';
				$arAutoload[$entityName] = str_replace($_SERVER["DOCUMENT_ROOT"],'',$file);
			}
			
			\Bitrix\Main\Loader::registerAutoLoadClasses(null, $arAutoload);
			return $arAutoload;
		}
		
        return [];
    }
	
	
	function InstallTables() {
		$arEntities = $this->_getEntities();
		if (count($arEntities)) {
			$connection = \Bitrix\Main\Application::getInstance()->getConnection();
			
			foreach ($arEntities as $entityName=>$file) {
				$entity = $entityName::getEntity();
				$tableName = $entity->getDBTableName();
				
				if (!$connection->isTableExists($tableName)) {
					$entity->createDbTable();
				}
			}
		}
		
        return true;
    }
    
    function UnInstallTables() {
		$arEntities = $this->_getEntities();
		if (count($arEntities)) {
			$connection = \Bitrix\Main\Application::getInstance()->getConnection();
			
			foreach ($arEntities as $entityName=>$file) {
				$entity = $entityName::getEntity();
				$tableName = $entity->getDBTableName();
				
				if ($connection->isTableExists($tableName)) $connection->dropTable($tableName);
			}
		}
        return true;
    }
	
	function InstallEvents() {
		foreach ($this->MODULE_EVENTS as $arEvent) {
			RegisterModuleDependences(
					$arEvent['module'],
					$arEvent['event'],
					$this->MODULE_ID,
					$arEvent['class'], 
					$arEvent['method']
				);
		}
		return true;
	}

	function UnInstallEvents() {
		foreach ($this->MODULE_EVENTS as $arEvent) {
			UnRegisterModuleDependences(
					$arEvent['module'],
					$arEvent['event'],
					$this->MODULE_ID,
					$arEvent['class'], 
					$arEvent['method']
				);
		}
		return true;
	}
    
    function DoInstall() {
		global $DB, $APPLICATION, $step;
        $this->InstallFiles();
		$this->InstallTables();
        RegisterModule($this->MODULE_ID);
		$this->InstallEvents();
		$APPLICATION->IncludeAdminFile(
				GetMessage($this->MODULE_SP."INSTALL_TITLE"),
				$this->MODULE_DIR_ABS."/install/step.php"
			);
    }
    
    function DoUninstall() {
		global $DB, $APPLICATION, $step;
		$this->UnInstallEvents();
		$this->UnInstallTables();
        $this->UnInstallFiles();
        UnRegisterModule($this->MODULE_ID);
		$APPLICATION->IncludeAdminFile(
				GetMessage($this->MODULE_SP."INSTALL_TITLE"), 
                $this->MODULE_DIR_ABS."/install/unstep.php"
			);
    }
}
?>