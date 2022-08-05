<?php
/**
 * Widget model for dashboard - file.
 *
 * @package   Dashboard
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Widget model for dashboard - class.
 */
class Vtiger_MiniListModel_Dashboard extends Vtiger_Widget_Model
{
	/** {@inheritdoc} */
	public function getTitle()
	{
		$title = $this->get('title');
		if (empty($title) && !$this->getId()) {
			$title = $this->get('linklabel');
		} else {
			$miniListModel = new Vtiger_MiniList_Model();
			$miniListModel->setWidgetModel($this);
			$title = $miniListModel->getTitle();
		}
		return $title;
	}

	/** {@inheritdoc} */
	public $customFields = [
		'owners_all' => ['label' => 'LBL_FILTERS_AVAILABLE', 'purifyType' => \App\Purifier::STANDARD]
	];

	/** {@inheritdoc} */
	public function getEditFields(): array
	{
		$fields = [];
		$fields['default_owner'] = ['label' => 'LBL_DEFAULT_FILTER', 'purifyType' => \App\Purifier::STANDARD];

		return ['title' => ['label' => 'LBL_WIDGET_NAME', 'purifyType' => \App\Purifier::TEXT]] +
			$this->editFields + $fields + $this->customFields +
			['limit' => ['label' => 'LBL_NUMBER_OF_RECORDS_DISPLAYED', 'purifyType' => App\Purifier::INTEGER]];
	}

	/** {@inheritdoc} */
	public function getFieldInstanceByName($name)
	{
		if (!isset($this->customFields[$name])) {
			return parent::getFieldInstanceByName($name);
		}

		$moduleName = 'Settings:WidgetsManagement';
		$params = [
			'name' => $name,
			'label' => $this->getEditFields()[$name]['label'],
			'tooltip' => $this->getEditFields()[$name]['tooltip'] ?? ''
		];
		switch ($name) {
			case 'owners_all':
				$params['uitype'] = 33;
				$params['typeofdata'] = 'V~M';
				$picklistValue = [
					'mine' => 'LBL_MINE',
					'all' => 'LBL_ALL',
					'users' => 'LBL_USERS',
					'groups' => 'LBL_GROUPS',
					'groupUsers' => 'LBL_GROUP_USERS',
					'roleUsers' => 'LBL_ROLE_USERS',
					'rsUsers' => 'LBL_ROLE_AND_SUBORDINATES_USERS'
				];
				foreach ($picklistValue as $key => $label) {
					$params['picklistValues'][$key] = \App\Language::translate($label, $moduleName);
				}
				$owners = $this->get('owners') ? \App\Json::decode($this->get('owners')) : [];
				$value = $owners['available'] ?? [];
				$params['fieldvalue'] = implode(' |##| ', $value);
				break;
			default: break;
		}
		return \Vtiger_Field_Model::init($moduleName, $params, $name);
	}

	/** {@inheritdoc} */
	public function setDataFromRequest(App\Request $request)
	{
		parent::setDataFromRequest($request);
		foreach ($this->customFields as $fieldName => $fieldInfo) {
			if ($request->has($fieldName)) {
				$value = $request->getByType($fieldName, $fieldInfo['purifyType']);
				$fieldModel = $this->getFieldInstanceByName($fieldName)->getUITypeModel();
				$fieldModel->validate($value, true);
				$value = $fieldModel->getDBValue($value);

				switch ($fieldName) {
					case 'owners_all':
						$value = $value ? explode(' |##| ', $value) : [];
						$owners = $this->get('owners') ? \App\Json::decode($this->get('owners')) : [];
						$owners['available'] = $value;
						$this->set('owners', \App\Json::encode($owners));
						break;
					default: break;
				}
			}
		}
	}

	/** {@inheritdoc} */
	public function getSettingsLinks()
	{
		$links = [];
		if ($this->getId() && \App\User::getCurrentUserModel()->isAdmin()) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues([
				'linklabel' => 'LBL_SHOW_COMPLETE_DETAILS',
				'linkclass' => 'btn btn-default btn-xs js-show-modal',
				'linkicon' => 'fas fa-th-list',
				'linkdata' => [
					'url' => "index.php?module=Home&view=MiniListWizard&step=step1&linkId={$this->get('linkid')}&templateId={$this->getId()}",
					'module' => \App\Module::getModuleName($this->get('tabid')),
					'modalId' => \App\Layout::getUniqueId('MiniList')
				]
			]);
		}
		return array_merge($links, parent::getSettingsLinks());
	}

	/**
	 * Get value by field for edit view.
	 *
	 * @param string $name
	 */
	public function getValueForEditView(string $name)
	{
		$value = '';
		switch ($name) {
			case 'title':
				$value = $this->get('title') ?: '';
				break;
			case 'module':
			case 'fieldHref':
			case 'filterFields':
				$value = $this->getDataValue($name) ?: '';
				break;
			case 'filterid':
				$value = $this->get($name) ?: 0;
				break;
			case 'fields':
				$value = $this->getDataValue($name) ?: [];
				break;
			default:
				break;
		}
		return $value;
	}

	/** {@inheritdoc} */
	public function isDeletable(): bool
	{
		return parent::isDeletable() && Users_Privileges_Model::getCurrentUserPrivilegesModel()->hasModuleActionPermission($this->get('tabid'), 'CreateDashboardFilter');
	}

	/** {@inheritdoc} */
	public function isViewable(): bool
	{
		$userPrivModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$moduleName = \App\Json::decode($this->get('data'))['module'];

		return $userPrivModel->hasModulePermission($moduleName) && \App\CustomView::isPermitted((int) $this->get('filterid'));
	}

	/** {@inheritdoc} */
	public function isCreatable(): bool
	{
		$userPrivModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		return $this->isViewable() && $userPrivModel->hasModuleActionPermission($this->get('module') ?: $this->get('tabid'), 'CreateDashboardFilter');
	}
}
