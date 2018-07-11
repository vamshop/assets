<?php

use Cake\Core\Configure;
use Cake\Core\Plugin;
use Vamshop\Core\Vamshop;
use Assets\Utility\StorageManager;

if (!Configure::read('Assets.installed')) {
	return;
}

spl_autoload_register(function($class) {
	$defaultPath = Plugin::path('Assets') . 'Vendor' . DS . 'Gaufrette' . DS . 'src' . DS;
	$base = Configure::read('Assets.GaufretteLib');
	if (empty($base)) {
		$base = $defaultPath;
	}
	$class = str_replace('\\', DS, $class);
	if (file_exists($base . $class . '.php')) {
		include ($base . $class . '.php');
	}
});

Configure::write('Wysiwyg.attachmentBrowseUrl', array(
	'prefix' => 'admin',
	'plugin' => 'Assets',
	'controller' => 'Attachments',
	'action' => 'browse',
));

Configure::write('Wysiwyg.uploadsPath', '');

Vamshop::mergeConfig('Wysiwyg.actions', array(
	'Admin/Attachments/browse',
));

StorageManager::config('LocalAttachment', array(
	'description' => 'Local Attachment',
	'adapterOptions' => array(WWW_ROOT . 'assets', true),
	'adapterClass' => '\Gaufrette\Adapter\Local',
	'class' => '\Gaufrette\Filesystem',
));
StorageManager::config('LegacyLocalAttachment', array(
	'description' => 'Local Attachment (Legacy)',
	'adapterOptions' => array(WWW_ROOT . 'uploads', true),
	'adapterClass' => '\Gaufrette\Adapter\Local',
	'class' => '\Gaufrette\Filesystem',
));

// TODO: make this configurable via backend
$actions = [
	'Admin/Blocks/edit',
	'Admin/Contacts/edit',
	'Admin/Nodes/edit',
	'Admin/Types/edit',
];
$tabTitle = __d('assets', 'Assets');
foreach ($actions as $action):
	list($controller, ) = explode('/', $action);
	Vamshop::hookAdminTab($action, $tabTitle, 'Assets.admin/asset_list');
	Vamshop::hookHelper($controller, 'Assets.AssetsAdmin');
endforeach;

// TODO: make this configurable via backend
$models = [
	'Vamshop/Blocks.Blocks',
	'Vamshop/Contacts.Contacts',
	'Vamshop/Nodes.Nodes',
	'Vamshop/Taxonomy.Types',
];
foreach ($models as $model) {
	Vamshop::hookBehavior($model, 'Assets.LinkedAssets', ['priority' => 9]);
}

Vamshop::hookHelper('*', 'Assets.AssetsFilter');
