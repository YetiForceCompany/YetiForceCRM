<?php
/**
 * Webservice premium container - ModComments record detail file.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace Api\WebservicePremium\ModComments;

/**
 * Webservice premium container - ModComments record detail class.
 */
class Record extends \Api\WebservicePremium\BaseModule\Record
{
	/** {@inheritdoc} */
	public function post(): array
	{
		if ($this->controller->request->has('assigned_user_id') || $this->controller->request->has('customer') || $this->controller->request->has('userid')) {
			throw new \Api\Core\Exception('No permissions for data provided in the request', 403);
		}
		$this->recordModel->set('assigned_user_id', $this->getUserData('user_id'));
		if ($this->getUserCrmId()) {
			$this->recordModel->set('customer', $this->getUserCrmId());
		}
		return parent::post();
	}
}
