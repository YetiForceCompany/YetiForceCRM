<?php
/**
 * Widget model for dashboard - file.
 *
 * @package   Dashboard
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Widget model for dashboard - class.
 */
class Vtiger_ChartFilterModel_Dashboard extends Vtiger_Widget_Model
{
	/** {@inheritdoc} */
	public function getTitle()
	{
		$title = $this->get('title');
		if (empty($title) && !$this->getId()) {
			$title = $this->get('linklabel');
		} else {
			$miniListModel = new Vtiger_ChartFilter_Model();
			$miniListModel->setWidgetModel($this);
			$title = $miniListModel->getTitle();
		}
		return $title;
	}

	/** {@inheritdoc} */
	public function getEditFields(): array
	{
		return ['title' => ['label' => 'LBL_WIDGET_NAME', 'purifyType' => \App\Purifier::TEXT]] + parent::getEditFields();
	}
}
