<?php

class EmanagerCleanImagesProcessor extends modProcessor
{
    const DIRECTORY = 'assets/mgr/';
    const EXTENSIONS = ['jpeg', 'jpg', 'png'];
    const CONFIG_FILES = [MODX_BASE_PATH . 'sbox_cfg.php'];
    const TIME = 604800; //неделя


    public function process()
    {
        $this->files = $this->getFiles();
        $all = $this->files;
        $total = count($this->files);
        $removed = 0;
        $props = $this->getProperties();

        /* 1. Ищем в ресурсах */
        foreach ($this->files as $key => $file) {
    		$query = $this->modx->newQuery('modResource', [
	    		"content LIKE '%$file%'"
	    	]);
			$query->select('id');
			
			$result = $this->modx->getValue($query->prepare());
			if ($result) {
                unset($this->files[$key]);
			}
    	}
        
        /* 2. Ищем в TV */
        foreach ($this->files as $key => $file) {
			$encoded = json_encode($file);
			$encoded = str_replace("\/", "\\\\\\\/", $encoded);
			
			$query = $this->modx->newQuery('modTemplateVarResource', [
	    		"(value LIKE '%$file%') OR (value LIKE '%$encoded%')"
	    	]);
	    	$query->select('id');
	    	
	    	$result = $this->modx->getValue($query->prepare());
	    	if ($result) {
                unset($this->files[$key]);
			}
		}
        
		/* 3. Ищем файлы, у которых дата изменения меньше TIME */
		$now = time();
		foreach ($this->files as $key => $file) {
			$modified = filemtime(MODX_BASE_PATH . self::DIRECTORY .$file);
			if (self::TIME && $now - $modified < self::TIME) {
                unset($this->files[$key]);
			}
		}
        
        /* 3. Ищем в конфиге коробки */
        foreach (self::CONFIG_FILES as $cfg_file) {
            $cfg_file = file_get_contents($cfg_file);
            if (!$cfg_file) continue;

            foreach ($this->files as $key => $file) {
                if (stripos($cfg_file, $file) !== false) {
                    unset($this->files[$key]);
                }
            }
        }

        /* Удаляем */
		foreach ($this->files as $key => $file) {
            $file = MODX_BASE_PATH . self::DIRECTORY . $file;
            if ($props['confirm'] == 'true') unlink($file);
            $removed++;
		}

        /* Чистим кэш */
        if ($props['confirm'] == 'true') {
            self::rm(MODX_ASSETS_PATH . 'web/_cache/thumbs');
            $this->modx->cacheManager->clearCache();
        }
        

        return $this->success([
            'files' => $this->files,
            'all' => $all,
            'removed' => $removed,
            'total' => $total,
            'confirm' => $props['confirm']
        ]);
    }


    /**
     * Ищем все картинки
     */
    private function getFiles()
    {
    	$files = [];
		$directory = MODX_BASE_PATH . self::DIRECTORY;
		
		$it = new RecursiveDirectoryIterator($directory);
		foreach(new RecursiveIteratorIterator($it) as $file) {
		    if (in_array(strtolower(array_pop(explode('.', $file))), self::EXTENSIONS)) {
		    	$files[] = str_replace($directory, '', (string)$file);
		    }
		}
		return $files;
    }


    /**
     * 
     */
    private static function rm($target)
    {
        if(is_dir($target)){
            $files = glob( $target . '*', GLOB_MARK ); //GLOB_MARK adds a slash to directories returned
            foreach( $files as $file ){
                self::rm($file);      
            }
            rmdir( $target );
        } elseif(is_file($target)) {
            unlink( $target );  
        }
    }

}
return "EmanagerCleanImagesProcessor";