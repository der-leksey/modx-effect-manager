<?php
class EmanagerUsersCreateProcessor extends modObjectCreateProcessor {
	
    public $classKey = 'modUser';
    
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
        $className = 'EmanagerUsersCreateProcessor';
        $processor = new $className($modx,$properties);
        return $processor;
    }
    
	public function initialize() {
        return parent::initialize();
    }
	

    public function beforeSave() {
    	
    	if (strlen($this->getProperty('password'))<2) {
			$autoPassword = $this->generatePass();
			$this->setProperty('password',$autoPassword);
    		$this->object->set('password',$autoPassword);
    	}
    	$this->profile = $this->modx->newObject('modUserProfile');
        $this->profile->set('email',$this->getProperty('username'));
		$this->profile->set('fullname', $this->getProperty('fullname'));
        $this->profile->set('city', $this->getProperty('city'));
        
        $this->object->addOne($this->profile,'Profile');
    	
		$unique = array('username');
		foreach ($unique as $tmp) {
			if ($this->modx->getCount($this->classKey, array('username' => $this->getProperty($tmp)))) {
				$this->addFieldError($tmp, 'Пользователь существует');
			}
		}
		
		if ($this->hasErrors()) {
			return false;
		}
		
        return parent::beforeSave();
    }
    public function afterSave() {
    	$group = $this->getProperty('primary_group');
    	if ($group && $group > 1) {
    		$membership = $this->modx->newObject('modUserGroupMember');
	        $membership->fromArray(array(
	            'user_group' => $group,
	            'role' => 1,
	            'member' => $this->object->get('id'),
	            'rank' => 0
	        ));
	    	$membership->save();
	    	
	        $this->object->addOne($membership, 'UserGroupMembers');
    	}
    	
        //$this->object->set('primary_group', $this->getProperty('primary_group'));
        $this->object->save();
        
        return parent::afterSave();
    }
    
    public function cleanup() {
    	$message = "Логин: ".$this->getProperty('username')."<br>
    	Пароль: ".$this->getProperty('password')."<br>
    	Адрес админки: http://".$_SERVER['HTTP_HOST']."/backend/\n";
        return $this->success($message,$this->object);
    }
    
}
return "EmanagerUsersCreateProcessor";