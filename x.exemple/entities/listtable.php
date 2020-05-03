<?

namespace Entity {
    class ListTable extends \X\Abstraction\EntityTable
    {
		
        const VERSION = 1;
        
        
        public static function getMap()
        {
            return array(
                new \Bitrix\Main\Entity\IntegerField('ID', array( // id списка
                    'primary' => true,
                    'autocomplete' => true
                )),
                new \Bitrix\Main\Entity\StringField('KEY', array( // ключ-ссылка для шаринга
                    'required' => true,
                    'default_value' => 'key',
                    'save_data_modification' => function () {
						return array(
							function ($value) {
                                if ($value == 'key' || !$value) return randString(32);
								return $value;
							}
						);
					},
                )),
                new \Bitrix\Main\Entity\StringField('NAME', [ // id Пользователей имеющих доступ к списку
					'required' => true,
                ])
            );
        }
    }

}