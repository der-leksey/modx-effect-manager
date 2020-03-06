<?php
class EmanagerUsersRemoveProcessor extends modObjectRemoveProcessor {
	public $classKey = 'modUser';
	public $objectType = 'object';
	
	public function beforeRemove() {
	    if ($this->modx->user->id == $this->getProperty('id')) {
	    	return 'Вы не можете удалить сами себя!';
	    }
	    return true;
	}
	public function cleanup() {
        return $this->success('Пользователь удалён.',$this->object);
    }

}

return 'EmanagerUsersRemoveProcessor';