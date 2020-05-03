<?
// $user = \Model\User::getInstance();
namespace Model {
    class User extends \X\Abstraction\CurrentUser {
        
        protected $SelectFields=array(
                'ID',
                'LOGIN',
                'EMAIL',
            );
        
        
        protected function __construct() {
            
            $uid = 0;
            global $USER;
            if (is_a($USER,'CUser')) {
                $uid = intval($USER->GetID());
            }
            $this->id = $uid;
        }
        
        public function register ($arFields=[]) {
            if ($this->GetID() > 0) return $this->GetID();
            
            if (!isset($arFields['LOGIN'])) $arFields['LOGIN'] = randString(8);
            if (!isset($arFields['PASSWORD'])) {
                $autopass = randString(16);
                $arFields['PASSWORD'] = $autopass;
                $arFields['PASSWORD_CONFIRM'] = $autopass;
            }
            
            $userbx = new \CUser;
            $result = new \X\Result();
            
            $newUserID = intval($userbx->Add($arFields));
            if ($newUserID > 0) {
                $result->setId($newUserID);
                $userbx->Authorize($newUserID);
            } else {
                $lstErrors = array_filter(explode('<br>',$userbx->LAST_ERROR), function ($s) {return trim($s) != '';});
                $result->addErrors($lstErrors);
            }
            
            return $result;
        }
    }
}