<?php
class EmanagerUsersGetProcessor extends modObjectGetListProcessor {
    public $classKey = 'modUser';
    public $defaultSortField = 'id';
    public function prepareQueryBeforeCount(xPDOQuery $c) {
    	$c->where(array('modUser.primary_group:IN'=>[2,3]));
		$c->leftJoin('modUserProfile', 'modUserProfile', 'modUser.id = modUserProfile.internalKey');
		$c->select($this->modx->getSelectColumns($this->classKey, $this->classKey));
		$c->select('modUserProfile.fullname, modUserProfile.city');
		return $c;
    }
}
return "EmanagerUsersGetProcessor";