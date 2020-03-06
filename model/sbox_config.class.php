<?php
class sbox_config {
	
	public $modx;
	private $array;
	function __construct(modX &$modx, array $config = array()) {
		$this->modx =& $modx;
	}
	
	const res_tmpls = [3,4,17]; //Шаблоны ресурсов, из которых берём параметры
	const tv_categories = [74,29,31,61]; //категории, из которых берём tv
	const block_classes_tvs = ['border','bgr_options','header','slider']; //tv для html-классов блока
	
	
	/*Создание конфига*/
	public function getConfig() {
		
		$start = microtime(true);
		
		$q = $this->modx->newQuery('modResource');
		$q->select(array(
			'modResource.id as id',
			'modResource.template as template',
			'modResource.pagetitle as title',
			'modResource.longtitle as longtitle',
			'modResource.menutitle as type',
			'IFNULL(mtvr.value, mtv.default_text) as value',
			'mtv.name as name',
			'coll.menuindex as menuindex',
			'coll.collection as collection'
		));
		
		$q->leftJoin('modTemplateVar', 'mtv', 'mtv.category IN ('.implode(',',self::tv_categories).')');
		$q->leftJoin('modTemplateVarResource', 'mtvr', 'mtvr.contentid = modResource.id AND mtvr.tmplvarid = mtv.id');
		$q->leftJoin('CollectionSelection', 'coll', 'coll.resource = modResource.id');
		
		$q->where(array(
		    'modResource.template:IN'=>self::res_tmpls,
		    'modResource.deleted'=>0,
		));
		$q->prepare();
		$q->stmt->execute();
		$q_params = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
		unset($q);
		
		/*разделим блоки и шаблоны, приведем массив в нужный вид*/
		$array = [];
		foreach ($q_params as $i) {
			//Удаляем префикс _block у параметров
			$i['name'] = str_replace('block_','',$i['name']);
			
			switch ((int)$i['template']) {
				case 4:
					if(isset($i['value'])&&strlen($i['value'])>0) {
						$array['main'][$i['name']] = $i['value'];
					}
					break;
				case 3: case 17:
					$array['blocks'][$i['id']]['name'] = $i['title'];
					$array['blocks'][$i['id']]['type'] = str_replace('block-','',$i['type']);
					if($i['longtitle']) {
						$array['blocks'][$i['id']]['title'] = $i['longtitle'];
					}
					
					if(isset($i['value'])&&strlen($i['value'])>0) {
						$array['blocks'][$i['id']][$i['name']] = $i['value'];
					}
					if($i['collection']) {
						$array['containers'][$i['collection']][$i['id']] = $i['menuindex'];
					}
					break;
			}
		}
		
		//порядок блоков
		foreach($array['containers'] as $key=>$i) {
			asort($i);
			$array['containers'][$key] = array_keys($i);
		}
		
		$this->array = $array;
		$this->array['lexicon'] = $this->lexicon();
		$this->array['dict'] = $this->createDict();
		$this->processConfig();
		
		
		//$this->modx->log(1, 'Время выполнения скрипта: '.round(microtime(true) - $start, 4).' сек.');
		
		return $this->array;
	}//getConfig





	//Создание справочника
	private function createDict() {
		$ids = [
			'forms'=>71,
			'popup_forms'=>133,
			'colors'=>48,
			'fonts1'=>145,
			'fonts2'=>146,
		];
		
		$q = $this->modx->newQuery('modResource');
		$q->select(array(
			'modResource.id as id',
			'modResource.content as content',
			'modResource.parent as parent',
			'mtvr.value as tv',
			'mtvr.tmplvarid as tvid',
		));
		$q->leftJoin('modTemplateVarResource', 'mtvr', 'mtvr.contentid = modResource.id');
		$q->where(array(
		    'modResource.parent:IN'=>array_values($ids),
		));
		$q->prepare();
		$q->stmt->execute();
		$q_res = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
		$dict = [];
		
		foreach ($q_res as $i) {
			if ($i['parent']==$ids['popup_forms']) {
				$dict['popup_forms'][] = $i['id'];
			}
			//цветосхемы
			if (!empty($i['content'])&&$i['parent']==$ids['colors']) {
				$dict[$i['id']] = json_decode($i['content'],true);
				$dict[$i['id']]['color-txt'] = $dict[$i['id']]['color-txt'] ?: '1f1f1f';
				$dict[$i['id']]['color-bgr'] = $dict[$i['id']]['color-bgr'] ?: 'ffffff';
				$dict[$i['id']]['color-3'] = $dict[$i['id']]['color-3'] ?: 'ffffff';
				$dict[$i['id']]['color-4'] = $dict[$i['id']]['color-4'] ?: '1f1f1f';
			}
			//таблицы
			if (!empty($i['tv'])&&$i['tvid']==42) {
				$table = json_decode($i['tv'],true);
				$table = array_column($table,'value','key');
				
				if(in_array($i['parent'], [$ids['forms'],$ids['popup_forms']])) {
					$table = $this->processForm($table);
				}
				
				$dict[$i['id']] = $table;
			}
		}
		
		return $dict;
	}
	
	

	
	//Обработка конфига
	private function processConfig() {

		foreach ($this->array['blocks'] as $bid=>&$b) {
			
			//Выбранный дизайн - на чанк и модификатор
			if ($b['block-select']) {
				$sel = explode('-',$b['block-select']);
				$b['chunk'] = (string)$sel[0];
				if ($sel[1]) {
					$b['mod'] = $sel[1];
				}
			}
			
			//Кнопки
			$b['btns'] = json_decode($b['btns'],true) ?: array();
			$b['btns'] = $this->processButtons($b['btns']);
			if (!$b['btns']) unset($b['btns']);
			
			//специфические настройки блока в массив
			$options = json_decode($b['options'],true);
			if ($options) {
				$b['options'] = array_column($options,'value','key');
			}
			
			//Объединяем html-классы блока
			$tmp_classes = [];
			foreach (self::block_classes_tvs as $tv) {
				if ($b[$tv]) {
					$tmp_classes = array_merge(explode('||', $b[$tv]), $tmp_classes);
				}
			}
			
			//колонки
			if (!empty($b['width'])) $tmp_classes[] = "has-grid-{$b['width']}";
			
			
			if ($tmp_classes) {
				$b['classes'] = implode(' ', $tmp_classes);
			}

			
		}

		unset($b);
		return true;
		
	}//processConfig
	
	
	/*Обработка кнопок*/
	private function processButtons($btns) {
		$out = [];
		foreach($btns as $btn_index=>$btn) {
			
			if($btn['on']!=1) continue;
			$key = $btn['key']?:$btn_index;
			$out[$key]['name'] = $this->translate($btn['name']);
			$out[$key]['icon'] = $btn['icon'];
			
			$btnClasses = [
				0=>str_replace('||',' ',$this->array['main']['btns-settings']),
				1=>$btn['color'],
				2=>$btn['bordered']?:$this->array['main']['btns-bordered'],
			];
			$out[$key]['classes'] = implode(' ',$btnClasses);
			
			switch(true) {
				case is_numeric($btn['action']):
					$out[$key]['action'] = 'href="'.$this->modx->makeUrl((int)$btn['action']).'"';
					break;
				case strpos($btn['action'],'#')===0:
					$out[$key]['action'] = 'data-anchor href="'.str_replace('#','#b',$btn['action']).'"';
					break;
				case strpos($btn['action'],'@')===0:
					$out[$key]['action'] = 'data-fancybox href="#popup-'.str_replace('@','',$btn['action']).'"';
					break;
				case empty($btn['action']):
					$out[$key]['action'] = '';
					break;
				default:
					$out[$key]['action'] = 'target="_blank" href="'.$btn['action'].'"';
					break;
			}
		}
		return $out;
	}
	
	
	
	/*Обработка форм из справочника*/
	private function processForm($form) {
		$fields = []; $required = []; $fi_required = [];
			foreach(explode('||',$form['fields']) as $i) {
			$fields[] = trim($i);
		}
		foreach(explode('||',$form['required']) as $i) {
			$required[] = trim($i);
			$fi_required[] = trim($i).':required';
		}
		$form['fields'] = $fields;
		$form['required'] = $required;
		$form['fi_required'] = implode(',',$fi_required);
		
		foreach(['title','btn_text','textarea_label','success_text','subject'] as $i) {
			$form[$i] = $this->translate($form[$i]);
		}
		
		$form['custom']=array();
		foreach($form as $k=>$v){
			if(strpos($k,'custom_')===0){
				$form['custom'][substr($k,7)]=$v;
			}
		}
		
		return $form;
	}
	
	
	
	/*Получение словаря*/
	private function lexicon() {
		
		$q = $this->modx->newQuery('modResource');
		$q->select(array(
			'mtvr.value as val',
			'modResource.context_key as ctx',
		));
		$q->leftJoin('modTemplateVarResource', 'mtvr', 'mtvr.contentid = modResource.id AND mtvr.tmplvarid = 42');
		$q->where(array(
		    'modResource.alias'=>'lexicon',
		));
		$q->prepare();
		$q->stmt->execute();
		$q_res = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
		$q_res = array_column($q_res,'val','ctx');
		
		$lex = [];
		foreach($q_res as $ctx=>$table) {
			$lex[$ctx] = json_decode($table,true);
			$lex[$ctx] = array_column($lex[$ctx],'value','key');
		}
		return $lex;
	}
	
	
	/*значение - в массив с переводами по контекстам*/
	private function translate($input) {
		$out = [];
		foreach($this->array['lexicon'] as $ctx=>$lex) {
			$out[$ctx] = $lex[$input]?:$input;
		}
		return $out;
	}
	
};