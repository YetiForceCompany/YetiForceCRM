<?php
/**
 * Class to read and save configuration for integration with magento.
 *
 * @package Integration
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */

namespace App\Integrations\Magento;

use App\Db\Query;

/**
 * Class Config.
 */
class Config extends \App\Base
{
	/**
	 * Instance class.
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Function to get object to read configuration.
	 *
	 * @return void
	 */
	public static function getInstance()
	{
		if (empty(static::$instance)) {
			static::$instance = new static();
			$data = (new Query())->select(['name', 'value'])->from('i_#__magento_config')->createCommand()->queryAllByGroup();
			static::$instance->setData($data);
		}
	}
}
