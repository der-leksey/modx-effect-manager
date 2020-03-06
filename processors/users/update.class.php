<?php
class EmanagerUsersUpdateProcessor extends modObjectUpdateProcessor {
	public $classKey = 'modUser';
    public $object;
    public $profile;

    public function generatePass(){
	   $pass='';
	   $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	   $i=0;
	   $lenchars = strlen($chars);
	   while($i<8){
	       $pass.=$chars[random_int(0,$lenchars-1)];
	       $i++;
	   }
	   return $pass;
	}
    
	public static function getInstance(modX &$modx,$className,$properties = array()) {
        $classKey = 'modUser';
        $object = $modx->newObject($classKey);
		$className = 'EmanagerUsersUpdateProcessor';
        $processor = new $className($modx,$properties);
        return $processor;
    }
    
	public function beforeSet() {
        return parent::beforeSet();
    }
    public function beforeSave() {
		if (strlen($this->getProperty('password'))<2) {
			$autoPassword = $this->generatePass();
			$this->setProperty('password',$autoPassword);
    		$this->object->set('password',$autoPassword);
    	}
        $this->setProfile();
        return parent::beforeSave();
    }
    public function setProfile() {
        $this->profile = $this->object->getOne('Profile');
        if (empty($this->profile)) {
            $this->profile = $this->modx->newObject('modUserProfile');
            $this->profile->set('internalKey',$this->object->get('id'));
            $this->profile->save();
            $this->object->addOne($this->profile,'Profile');
        }
        $this->profile->fromArray($this->getProperties());
        return $this->profile;
    }
    
    public function afterSave() {
        return parent::afterSave();
    }
    
	public function cleanup() {
    	$message = "Логин: ".$this->getProperty('username')."<br>
    	Пароль: ".$this->getProperty('password')."<br>
    	Адрес админки: http://".$_SERVER['HTTP_HOST']."/backend/\n";
        return $this->success($message,$this->object);
    }
	
}
return 'EmanagerUsersUpdateProcessor';