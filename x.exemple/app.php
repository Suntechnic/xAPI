<?
class App extends \X\Abstraction\App {
    
    public static function init() {
        $self = App::getInstance();
        $self->addData([
                'time' => [
                        'timestamp' => time(),
                    ],
                'user' => \Model\User::getInstance()->getData()
            ]);
    }

}
