<?php
ini_set('display_errors', 1);
class EmanagerIndexManagerController extends modExtraManagerController {
	
	public function isManager() {
		$user = $this->modx->user->toArray();
		return in_array($user['primary_group'],[1,2]) ? true : false;
    }

    public function getPageTitle() {
    	return 'Effect Manager';
    }
    
    public function loadCustomCssJs() {
    	if($this->isManager()) {
    		
    		$q = $this->modx->newQuery('modUserGroup');
			$q->select(['name', 'id']);
			$q->where([
				'id:NOT IN' => [1]
			]);
			$q->sortby('id');
			$q->prepare();
			$q->stmt->execute();
			$usergroups = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
    		$usergroups = json_encode($usergroups);
    		
    		
			$assets = $this->modx->getOption('assets_url');
		    $this->addJavascript($assets.'components/emanager/mgr/js/sboxMgr.js?time='.time());
			$this->addJavascript($assets.'components/emanager/mgr/js/sboxMgrTabUsers.js?time='.time());
			$this->addJavascript($assets.'components/emanager/mgr/js/sboxMgrTabImages.js?time='.time());
		    $this->addHtml('
		    <style>
		    	.x-window-dlg .x-window-body {
		    		max-height: 80vh !important;
		    		overflow: auto !important;
		    	}
		    </style>
		    <script>
		    	Sbox.usergroups = ' . $usergroups . ' ;
		        Ext.onReady(() => {
		        	Sbox.config.connector_url = "'.$assets.'components/emanager/mgr/connector.php";
		            MODx.add({
		                xtype: "sboxPanelHome"
		            });
		        });
		    </script>');
    	} else {
    		$this->addHtml("<script>
           	Ext.onReady(() => {
			    MODx.add({ 
			        xtype: 'panel', cls: 'container',
			        items: [{html: '<h3>Доступ запрещён</h3>',}]
			    });
			});
	        </script>");
    	}
	}
	
}