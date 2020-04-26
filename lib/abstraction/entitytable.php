<?
namespace X\Abstraction {
    abstract class EntityTable extends \Bitrix\Main\Entity\DataManager {
        
        
        /*
         * Проверка таблицы
        */
        public static function verifeTable ()
        {
            $entity = static::getEntity();
            $tableName = $entity->getDBTableName();
            //\Bitrix\Main\Config\Option::set('x','entity_version_table_'.$tableName,0);
            if (static::VERSION) {
                $curVersion = \Bitrix\Main\Config\Option::get('x','entity_version_table_'.$tableName,0);
                
                if (static::VERSION == $curVersion) {
                    return true; // все ок - продолжаем
                } else if (static::VERSION > $curVersion) {
                    
                    if (static::updateTable()) {
                        \Bitrix\Main\Config\Option::set('x','entity_version_table_'.$tableName,static::VERSION);
                        return true; // удалось проапдейтить таблицу - продолжаем
                    }
                }
                
                throw new \Bitrix\Main\DB\Exception('Conflict versions of table '.$tableName);
            } else {
                return false; // неудача
            }
        }
        
        /*
         * Создание таблицы сущности
        */
        public static function createTable ()
        {
            $entity = static::getEntity();
            $tableName = $entity->getDBTableName();
            
            $connection = \Bitrix\Main\Application::getInstance()->getConnection();
            if (!$connection->isTableExists($tableName)) {
                $r = $entity->createDbTable();
                return true;
            } else {
                return false;
            }
        }
        
        /*
         * 
         * Обновляет таблицу сущности
         * 
        */
        #DEV:
        public static function updateTable ()
        {
            $entity = static::getEntity();
            $tableName = $entity->getDBTableName();

            $connection = \Bitrix\Main\Application::getInstance()->getConnection();
            if ($connection->isTableExists($tableName)) {

                try {
                    
                    $tmp_tableName = 'x_tmptable_'.$tableName;
                    
                    $r = $connection->query('RENAME TABLE '.$tableName.' TO '.$tmp_tableName);
                    $r = $entity->createDbTable();
                    
                    $r = $fields = implode(', ',array_keys($connection->getTableFields($tmp_tableName)));
                    
                    $r = $connection->query('INSERT INTO '.$tableName.' ('.$fields.') SELECT '.$fields.' FROM '.$tmp_tableName.';');
                    $r = $connection->query('DROP TABLE '.$tmp_tableName);
                    
                    return true;
                    
                } catch (\Exception $e) {
                    
                    if ($connection->isTableExists($tmp_tableName)) {
                        if ($connection->isTableExists($tableName)) {
                            $connection->query('DROP TABLE '.$tableName);
                        }
                        $connection->query('RENAME TABLE '.$tmp_tableName.' TO '.$tableName);
                    }
                    
                }
                
                if (static::ALLOWED_RECREATE === true) {
                    static::dropTable();
                    return static::createTable();
                } else {
                    throw new \Bitrix\Main\DB\Exception('Unable to update table version for '.$tableName);
                    return false;
                }
                
                //$fields = $connection->getTableFields($tableName);
                //foreach ($entity->compileDbTableStructureDump() as $sqlQuery)
                //{
                //    \XDebug::step($sqlQuery); // die('<!-- xdebug --<pre>'.print_r($arResult,true).'</pre>-->');
                //}
                // return true;
            } else { // если таблицы нет - ее просто достаточно создать
                return static::createTable();
            }
        }
    
        /*
         * Удаление таблицы сущности
        */
        public static function dropTable ()
        {
            $entity = static::getEntity();
            $tableName = $entity->getDBTableName();
            
            $connection = \Bitrix\Main\Application::getInstance()->getConnection();
            if ($connection->isTableExists($tableName)) $connection->dropTable($tableName);
            return true;
        }
        
    }
}

