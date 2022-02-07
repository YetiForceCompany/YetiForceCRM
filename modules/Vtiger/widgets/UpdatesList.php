<?php
/**
 * Updates list widget model file.
 *
 * @package Widget
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Updates list widget model class.
 */
class Vtiger_UpdatesList_Widget extends Vtiger_Basic_Widget
{
	/** {@inheritdoc} */
	public function getWidget()
	{
		$fieldName = $this->Config['data']['field_name'];
		if (\is_array($fieldName)) {
			$fieldName = current($fieldName);
		}
		$this->Config['tpl'] = 'UpdatesList.tpl';
		$this->Config['url'] = 'module=' . $this->Module . '&view=Detail&record=' . $this->Record . '&mode=showModTrackerByField&field=' . $fieldName;
		return $this->Config;
	}

	/** {@inheritdoc} */
	public function getConfigTplName()
	{
		return 'UpdatesListConfig';
	}
}
