<?php

/**
 * ActivityState view Class.
 *
 * @package   View
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class Calendar_ActivityState_View extends Calendar_ActivityStateModal_View
{
	/**
	 * {@inheritdoc}
	 */
	protected function getTpl()
	{
		return 'Extended/ActivityState.tpl';
	}
}
