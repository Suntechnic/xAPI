<?
namespace Entity {
    class ULTable extends \X\Abstraction\EntityTable
    {
		
        const VERSION = 1;
        
        
        public static function getMap()
        {
            return array(
                new \Bitrix\Main\Entity\IntegerField('ID', array( // id Записи
                    'primary' => true,
                    'autocomplete' => true
                )),
                new \Bitrix\Main\Entity\IntegerField('USER', array( // id Пользователя
                    'required' => true,
					'default_value' => \Model\User::getInstance()->getId()
                )),
				new \Bitrix\Main\Entity\IntegerField('LIST', array( // id Списка
                    'required' => true
                ))
            );
        }
    }

}

