<?php

class sbox_frontend {
	public $modx;

	function __construct(modX &$modx, array $config = array()) {
		$this->modx =& $modx;
	}


	public function getContent($a,$uri) {
		$post = [
		    'sbox_fe' => 1,
		    'sbox_cfg' => json_encode($a),
		];
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://'.$_SERVER['HTTP_HOST'].($uri=='/'?'':'/'.$uri));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		$response = curl_exec($ch);
		curl_close($ch);
		return  $response;
	}


	public function cfgSave($cfg,$name) {
		$content = json_encode($cfg);
		
		$doc = $this->modx->newObject('modDocument');
		$doc->set('parent',197);
		$doc->set('pagetitle',$name);
		$doc->set('template',0);
		$doc->set('richtext',0);
		$doc->setContent($content);
		$doc->cleanAlias($name);
		$doc->save();

		return  'Конфигурафия сохранена :)';
	}
	
	
	public function cfgImport($cfg) {
		$main = $this->modx->getObject('modResource',35);
		$out = [];
		
		foreach($cfg['main'] as $k=>$i) {
			if($main->getTVValue($k)!=$i) {
				$main->setTVValue($k,$i);
				$out[$k] = $i;
			}
		}
		

		
		foreach($cfg['blocks'] as $bid=>$block) {
			
			if($b_res = $this->modx->getObject('modResource',$bid)) {
				
				$allowed = preg_grep('/color|opacity|font|block\-select/', array_keys($block));
				
				foreach($block as $k=>$i) {
					if(!in_array($k,$allowed)) continue;
					if($b_res->getTVValue($k)!=$i) {;
						$b_res->setTVValue($k,$i);
						$out[$bid][$k] = $i;
					}
				}
			}
		}
		
		
		$this->modx->cacheManager->refresh();
		if(count($out)==0) {
			return  'Нет изменений, сохранять нечего';
		} else {
			return  'Сохранено :) '.json_encode($out,JSON_UNESCAPED_UNICODE);
		}
		
	}
	
	
	
}
