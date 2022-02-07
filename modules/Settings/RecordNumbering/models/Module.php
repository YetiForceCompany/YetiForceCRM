<?php
/**
 * Record numbering basic module file.
 *
 * @package   Settings.Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
/**
 * Record numbering basic module class.
 */
class Settings_RecordNumbering_Module_Model extends Vtiger_Module_Model
{
	/**
	 * Function to get focus of this object.
	 *
	 * @return CRMEntity
	 */
	public function getFocus()
	{
		if (!isset($this->focus)) {
			$this->focus = CRMEntity::getInstance($this->getName());
		}
		return $this->focus;
	}

	/**
	 * Function to get Instance of this module.
	 *
	 * @param string $moduleName
	 * @param mixed  $tabId
	 *
	 * @return self
	 */
	public static function getInstance($moduleName, $tabId = false)
	{
		$moduleModel = new self();
		$moduleModel->name = $moduleName;
		if ($tabId) {
			$moduleModel->id = $tabId;
		}
		return $moduleModel;
	}

	/**
	 * Function to ger Supported modules for Custom record numbering.
	 *
	 * @return array list of supported modules Vtiger_Module_Model
	 */
	public static function getSupportedModules()
	{
		$subQuery = (new \App\Db\Query())->select(['tabid'])->from('vtiger_field')->where(['uitype' => 4])->distinct('tabid');
		$dataReader = (new App\Db\Query())->select(['tabid', 'name'])->from('vtiger_tab')->where(['isentitytype' => 1, 'presence' => 0, 'tabid' => $subQuery])->createCommand()->query();
		while ($row = $dataReader->read()) {
			$modulesModels[$row['tabid']] = self::getInstance($row['name'], $row['tabid']);
		}
		$dataReader->close();

		return $modulesModels;
	}

	/**
	 * Function to set Module sequence.
	 *
	 * @return array result of success
	 */
	public function setModuleSequence()
	{
		$prefix = $this->get('prefix');
		$postfix = $this->get('postfix');
		$tabId = \App\Module::getModuleId($this->getName());
		$instance = \App\Fields\RecordNumber::getInstance($this->getName());
		$instance->set('tabid', $tabId);
		$instance->set('prefix', $prefix);
		$instance->set('cur_id', $this->get('sequenceNumber'));
		$instance->set('postfix', $postfix);
		$instance->set('leading_zeros', $this->get('leading_zeros'));
		$instance->set('reset_sequence', $this->get('reset_sequence'));
		$status = $instance->save();
		$success = ['success' => $status];
		if (!$status) {
			$success['sequenceNumber'] = (new App\Db\Query())->select(['cur_id'])
				->from('vtiger_modentity_num')
				->where(['tabid' => $tabId, 'prefix' => $prefix, 'postfix' => $postfix])
				->scalar();
		}
		return $success;
	}
}
