<?

namespace Entity {
    class ItemTable extends \X\Abstraction\EntityTable
    {
		
        const VERSION = 2;
        
        
        public static function getMap()
        {
            return array(
                new \Bitrix\Main\Entity\IntegerField('ID', array( // id пункта
                    'primary' => true,
                    'autocomplete' => true
                )),
                new \Bitrix\Main\Entity\IntegerField('LIST', array( // id списка
                    'required' => true
                )),
                new \Bitrix\Main\Entity\StringField('NAME', [ // id Пользователей имеющих доступ к списку
					'required' => true,
                ]),
                ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                new \Bitrix\Main\Entity\DatetimeField('LCTIMESTAMP', [ // время последнего изменения
                    'required' => true,
                    'default_value' => new \Bitrix\Main\Type\Datetime
                ]),
                new \Bitrix\Main\Entity\IntegerField('LCUSER', [ // пользователь внёсший последнее изменение
                    'required' => true,
                    'default_value' => \Model\User::getInstance()->getId()
                ]),
                new \Bitrix\Main\Entity\BooleanField('WASTED', [ // потрачено
                    'required' => true,
					'values' => ['N', 'Y'],
                    'default_value' => 'N'
				]),
                ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                //new \Bitrix\Main\Entity\DatetimeField('BIRTHELINE', [ // время последнего изменения
                //    
                //]),
                //new \Bitrix\Main\Entity\DatetimeField('DEADLINE', [ // время последнего изменения
                //    
                //]),
            );
        }
    }

}

