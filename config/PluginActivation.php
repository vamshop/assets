<?php

namespace Assets\Config;

use Cake\ORM\TableRegistry;
use Cake\Cache\Cache;
use Vamshop\Core\Plugin;

/**
 * Assets Activation
 *
 * Activation class for Assets plugin.
 *
 * @author   Rachman Chavik <contact@xintesa.com>
 * @license  http://www.opensource.org/licenses/mit-license.php The MIT License
 */
class PluginActivation {

/**
 * onActivate will be called if this returns true
 *
 * @param  object $controller Controller
 * @return boolean
 */
	public function beforeActivation(&$controller) {
		/*
		if (!Plugin::loaded('Imagine')) {
			$plugin = new Plugin();
			$plugin->addBootstrap('Imagine');
			Plugin::load('Imagine');
			Log::info('Imagine plugin added to bootstrap');
		}
		*/
		return true;
	}

/**
 * Creates the necessary settings
 *
 * @param object $controller Controller
 * @return void
 */
	public function onActivation(&$controller) {
		$VamshopPlugin = new Plugin();
		$result = $VamshopPlugin->migrate('Assets');
		if ($result) {
			$Settings = TableRegistry::get('Vamshop/Settings.Settings');
			$Settings->write('Assets.installed', 1);
			Cache::clearGroup('menus');
		}
		return $result;
	}

/**
 * onDeactivate will be called if this returns true
 *
 * @param  object $controller Controller
 * @return boolean
 */
	public function beforeDeactivation(&$controller) {
		return true;
	}

/**
 * onDeactivation
 *
 * @param object $controller Controller
 * @return void
 */
	public function onDeactivation(&$controller) {
		$Settings = TableRegistry::get('Vamshop/Settings.Settings');
		$Settings->deleteKey('Assets.installed');
	}

}
