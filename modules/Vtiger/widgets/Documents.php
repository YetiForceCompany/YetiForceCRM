<?php
/**
 * Documents widget class.
 *
 * @package Widget
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_Documents_Widget extends Vtiger_RelatedModule_Widget
{
	/** {@inheritdoc} */
	public function isPermitted(): bool
	{
		return parent::isPermitted() && \App\Relation::getByModule($this->moduleModel->getName(), false, 'Documents');
	}

	/** {@inheritdoc} */
	public function getWidget()
	{
		$this->Config['buttonHeader'] = $this->getHeaderButtons();
		$this->Config['tpl'] = 'Basic.tpl';
		return parent::getWidget();
	}

	/** {@inheritdoc} */
	public function getConfigTplName()
	{
		return 'DocumentsConfig';
	}

	/**
	 * Function to get buttons which visible in header widget.
	 *
	 * @return Vtiger_Link_Model[]
	 */
	public function getHeaderButtons(): array
	{
		$links = [];
		$moduleName = is_numeric($this->Data['relatedmodule']) ? App\Module::getModuleName($this->Data['relatedmodule']) : $this->Data['relatedmodule'];
		if (\App\Privilege::isPermitted($moduleName, 'CreateView')) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues([
				'linklabel' => App\Language::translate('LBL_MASS_ADD', $moduleName),
				'linkdata' => [
					'url' => 'index.php?module=Documents&view=MassAddDocuments&sourceModule=' . $this->Module . '&sourceRecord=' . $this->Record,
					'cb' => 'Documents_MassAddDocuments_Js.register',
					'view' => 'Detail',
				],
				'linkicon' => 'yfi-document-templates',
				'linkclass' => 'btn-light btn-sm js-show-modal',
			]);
		}
		if (!empty($this->Data['email_template']) && \App\Mail::checkInternalMailClient() && \App\Record::isExists($this->Data['email_template'], 'EmailTemplates')
		) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues([
				'linkhint' => App\Language::translate('LBL_SEND_MAIL', $moduleName),
				'linkdata' => [
					'url' => OSSMail_Module_Model::getComposeUrl($this->Module, $this->Record, 'Detail', 'new') . '&template=' . $this->Data['email_template'],
					'module' => $this->Module,
					'record' => $this->Record,
					'popup' => 1
				],
				'linkicon' => 'fas fa-envelope',
				'linkclass' => 'btn-light btn-sm sendMailBtn'
			]);
		}
		return $links;
	}

	/**
	 * Gets relations.
	 *
	 * @param int $moduleId
	 *
	 * @return array
	 */
	public function getRelations(int $moduleId): array
	{
		$relations = [];
		if (empty($this->moduleModel)) {
			$this->moduleModel = Vtiger_Module_Model::getInstance($moduleId);
		}
		if (empty($this->Data['relatedmodule'])) {
			$this->Data['relatedmodule'] = \App\Module::getModuleId('Documents');
		}
		$dataReader = (new \App\Db\Query())->select(['vtiger_relatedlists.*', 'moduleName' => 'vtiger_tab.name', 'relatedField' => 'brl.field_name'])
			->from('vtiger_relatedlists')
			->innerJoin('vtiger_tab', 'vtiger_tab.tabid = vtiger_relatedlists.tabid')
			->leftJoin(['brl' => 'vtiger_relatedlists'], 'vtiger_tab.tabid = brl.tabid')
			->where(['and', ['vtiger_tab.presence' => 0], ['vtiger_relatedlists.related_tabid' => $this->Data['relatedmodule']]])
			->andWhere(['and', ['vtiger_relatedlists.tabid' => new \yii\db\Expression('brl.tabid')], ['brl.related_tabid' => $moduleId], ['brl.name' => 'getDependentsList']])
			->createCommand()->query();
		while ($row = $dataReader->read()) {
			$key = ['relatedField' => $row['relatedField'], 'relationId' => $row['relation_id']];
			$row['translate'] = $this->moduleModel->getFieldByName($row['relatedField'])->getFullLabelTranslation();
			$relations[\App\Json::encode($key)] = $row;
		}
		return $relations;
	}

	/**
	 * Gets custom fields.
	 *
	 * @return array
	 */
	public function getCustomFields(): array
	{
		$fields = [];
		$fromRelations = (array) ($this->Data['fromRelation'] ?? []);
		if ($fromRelations && ($relations = array_intersect_key($this->getRelations($this->moduleModel->getId()), array_flip($fromRelations)))) {
			$params['uitype'] = 16;
			$params['picklistValues'] = [];
			foreach ($relations as $key => $relation) {
				if (\App\Privilege::isPermitted($relation['moduleName'])) {
					$params['picklistValues'][$key] = $relation['translate'];
				}
			}
			$fields[] = \Vtiger_Field_Model::init($this->moduleModel->getName(), $params, 'fromRelation');
		}
		return $fields;
	}
}
