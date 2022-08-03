<?php
/**
 * Widget model for dashboard - file.
 *
 * @package   Dashboard
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */

/**
 * Widget model for dashboard - class.
 */
class OSSTimeControl_TimeCounterModel_Dashboard extends Vtiger_Widget_Model
{
	/** {@inheritdoc} */
	public function getTitle()
	{
		return \App\Language::translate($this->get('linklabel'), 'Dashboard');
	}
}
