<?php
/**
 * The modification time update handler file for product variants.
 *
 * @package Handler
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * The modification time update handler class for the product variants.
 */
class Products_UpdateModifiedTime_Handler
{
	/**
	 * EntityAfterSave function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterSave(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		if ('PLL_TYPE_VARIATION' === $recordModel->get('product_type') && $recordModel->get('parent_id') && $recordModel->getPreviousValue()) {
			$recordModel = \Vtiger_Record_Model::getInstanceById($recordModel->get('parent_id'), 'Products');
			$recordModel->set('modifiedtime', date('Y-m-d H:i:s'));
			$recordModel->save();
		}
	}
}
