<?php
/**
 * Edit view.
 *
 * @package   View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */

/**
 * Products_Edit_View class.
 */
class Products_Edit_View extends Vtiger_Edit_View
{
	/** {@inheritdoc} */
	public function getDuplicate()
	{
		$this->record->set('qtyinstock', null);
		parent::getDuplicate();
	}
}
