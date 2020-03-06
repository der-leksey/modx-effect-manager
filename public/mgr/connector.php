<?php
ini_set('display_errors', 1);

$base_path = $_SERVER['DOCUMENT_ROOT'];

// Подключаем MODX
require_once $base_path . '/config.core.php';
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CONNECTORS_PATH . 'index.php';

// Указываем путь к папке с процессорами и заставляем MODX работать
$modx->request->handleRequest(array(
	'processors_path' => MODX_CORE_PATH . 'components/emanager/processors/',
	'location' => '',
));