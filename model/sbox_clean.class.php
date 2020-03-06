<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

class sbox_clean {
	
	const categories = [65]; //Категории, из которых удаляем
	
	public $modx, $config, $confirm, $Cfg;
	function __construct(modX &$modx, array $config = array()) {
		$this->modx =& $modx;
		$this->Cfg = $modx->getService('sbox_config','sbox_config',MODX_CORE_PATH.'components/sbox/model/');
		$this->config = $this->Cfg->getConfig();
		
		//$this->confirm = 'no'; //Режим
	}
	
	private function configToFile($file) {
		$contents = var_export($this->config, true);
		file_put_contents(MODX_BASE_PATH.$file, "<?php\n return {$contents};\n");
		return "Файл конфигурации создан";
	}
	
	private function remove($objects,$name,$text_name) {
		$out = [];
		foreach($objects as $obj) {
			$out[] = $obj->get($name);
			if($this->confirm=="true") {
				$obj->remove();
			}
		}
		return "$text_name: ".implode('; ',$out);
	}
	
	private function removeResources($ids) {
		$resources = $this->modx->getCollection('modResource', array(
			'id:IN' => $ids
		));
		return $this->remove($resources,'pagetitle','Ресурсы');
	}
	
	private function removeSnippets() {
		$resources = $this->modx->getCollection('modSnippet', array(
			'category:IN' => self::categories,
		));
		return $this->remove($resources,'name','Сниппеты');
	}
	
	private function removeCategories() {
		$Cfg = $this->Cfg;
		
		$categories = $this->modx->getCollection('modCategory', array(
			'id:IN' => $Cfg::tv_categories,
			'OR:parent:IN' => $Cfg::tv_categories,
			'OR:id:IN' => self::categories,
		));
		return $this->remove($categories,'category','Категории');
	}
	
	private function removeTemplates() {
		$q1 = $this->modx->newQuery('modResource');
		$q1->select('GROUP_CONCAT(DISTINCT(modResource.template) SEPARATOR "||") as templates');
		$q1->prepare();
		$q1->stmt->execute();
		$q1 = $q1->stmt->fetchAll(PDO::FETCH_ASSOC);
		
		$notIn = explode("||",$q1[0]['templates']);
		$notIn[] = 15; //Каталог, наследуется
		
		$tmpls = $this->modx->getCollection('modTemplate',$where = array(
			'id:NOT IN' => $notIn
		));
		return $this->remove($tmpls,'templatename','Категории ');
	}
	
	private function removeTVs() {
		$Cfg = $this->Cfg;

		$q2 = $this->modx->newQuery('modTemplateVarResource');
		$q2->select('GROUP_CONCAT(DISTINCT(modTemplateVarResource.tmplvarid) SEPARATOR "||") as ids');
		$q2->prepare();
		$q2->stmt->execute();
		$q2 = $q2->stmt->fetchAll(PDO::FETCH_ASSOC);
		$tvs = $this->modx->getCollection('modTemplateVar',$where = array(
			'id:NOT IN' => explode("||",$q2[0]['ids']."||46"), //46-page_text_position
			'name:NOT LIKE'=>'migx.%',
			'OR:category:IN' => $Cfg::tv_categories,
		));
		return $this->remove($tvs,'name','TV ');
	}
	
	private function removeChunks() {
		$chunks_list = array_column($this->config['blocks'],'block-select');
		$chunks = $this->modx->getCollection('modChunk', array(
			'id:NOT IN' => $chunks_list,
			'name:LIKE'=>'block-%',
			'name:NOT LIKE'=>'block-%-1', //наследуются
			'OR:category:IN' => self::categories, //box panel
		));
		return $this->remove($chunks,'name','Чанки');
	}
	
	private function removeMenu($namespace) {
		$menu = $this->modx->getCollection('modMenu', array(
			'namespace' => $namespace
		));
		return $this->remove($menu,'text','Пункт меню');
	}
	
	
	private function removePackage($p, $text) {
		
		if($this->confirm=="true") {
			$q3 = $this->modx->newQuery('transport.modTransportPackage');
			$q3->select('package_name,signature');
			$q3->where(array('package_name'=>$p));
			$q3->prepare(); $q3->stmt->execute();
			$q3 = $q3->stmt->fetchAll(PDO::FETCH_ASSOC);
			$shk_remove = $this->modx->runProcessor('workspace/packages/remove', ['signature' => $q3[0]['signature']]);
			return !$shk_remove->isError() ? "$text удалён" : "$text не удалён (ошибка)";
		} else {
			return "$text будет удалён";
		}
	}
	
	
	private function removeDir($dir) {
		//Удаляет папку
	    if ($objs = glob($dir."/*")) {
	       foreach($objs as $obj) {
	         is_dir($obj) ? $this->removeDir($obj) : unlink($obj);
	       }
	    }
	    rmdir($dir);
	}
	
	/*===================*/
	public function start($confirm) {
		$this->confirm = $confirm;
		
		if(is_file(MODX_BASE_PATH.'sbox_cfg.php')&&$this->confirm=="true") {
			echo "<h3>Коробка уже очищена</h3>";
			return false;
		}
		
		if($this->confirm=="true") {
			echo "<h3>Очистка коробки. Боевой режим</h3>";
			
			$out[] = '1. '.$this->configToFile('sbox_cfg.php');
		} else {
			echo "<h3>Очистка коробки. Тестовый режим (без удаления)</h3>";
			echo "<h4>Для боевого режима добавьте &confirm=true в адресную строку</h4>";
			$out[] = '1. '.$this->configToFile('sbox_cfg_testmode.php');
		}
		
		if($this->config['main']['site_mode']!=3) {
			$out[] = '1.1. '.$this->removePackage('shopkeeper3','Shopkeeper');
			//$out[] = '1.2. '.$this->removePackage('HybridAuth','HybridAuth');
			$out[] = '1.3. '.$this->removePackage('Login','Login');
			$out[] = '1.4. '.$this->removeResources([174]); //магазин
			$out[] = '1.5. '.$this->removeMenu('shopkeeper3');
		}
		$out[] = '2. '.$this->removeResources([35]); //настр. коробки
		$out[] = '3. '.$this->removeChunks();
		$out[] = '4. '.$this->removeCategories();
		$out[] = '5. '.$this->removeTVs();
		$out[] = '6. '.$this->removeTemplates();
		$out[] = '7. '.$this->removeSnippets();
		$out[] = '8. '.$this->removeMenu('sbox');
		
		
		
		if($this->confirm=="true") {
			$this->removeDir(MODX_BASE_PATH.'/assets/components/sbox/');
			$this->removeDir(MODX_CORE_PATH.'/components/sbox/');
			unlink(MODX_BASE_PATH.'sbox_cfg_testmode.php');
			$out[] = '9. Удалены папки компонента sbox';
		}
		
		return implode('<hr>',$out);
	}
	
};