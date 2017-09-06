<?php

class Users_ExportData_Action extends Vtiger_ExportData_Action
{

	public function checkPermission(\App\Request $request)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if (!$currentUserModel->isAdminUser()) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	/**
	 * Function exports the data based on the mode
	 * @param \App\Request $request
	 */
	public function exportData(\App\Request $request)
	{
		$moduleName = $request->getByType('source_module', 1);

		$this->moduleInstance = Vtiger_Module_Model::getInstance($moduleName);
		$this->moduleFieldInstances = $this->moduleInstance->getFields();
		$this->focus = $this->moduleInstance->getEntityInstance();
		$query = $this->getExportQuery($request);
		$entries = $query->all();

		$headers = ['User Name', 'Title', 'First Name', 'Last Name', 'Email', 'Other Email', 'Secondary Email', 'Office Phone', 'Mobile', 'Fax', 'Street', 'City', 'State', 'Country', 'Postal Code'];
		foreach ($headers as &$header) {
			$translatedHeaders[] = \App\Language::translate(html_entity_decode($header, ENT_QUOTES), $moduleName);
		}
		$this->output($request, $translatedHeaders, $entries);
	}

	/**
	 * Function that generates Export Query based on the mode
	 * @param \App\Request $request
	 * @return string export query
	 */
	public function getExportQuery(\App\Request $request)
	{
		$cvId = $request->get('viewname');
		$queryGenerator = new \App\QueryGenerator($request->getByType('source_module', 1));
		if (!empty($cvId)) {
			$queryGenerator->initForCustomViewById($cvId);
		}
		$acceptedFields = ['user_name', 'title', 'first_name', 'last_name', 'email1', 'email2', 'secondaryemail', 'phone_work', 'phone_mobile', 'phone_fax', 'address_street', 'address_city', 'address_state', 'address_country', 'address_postalcode'];
		$queryGenerator->setFields($acceptedFields);
		return $queryGenerator->createQuery();
	}
}
