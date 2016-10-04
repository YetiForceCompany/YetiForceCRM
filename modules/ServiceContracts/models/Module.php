<?php

/**
 * Service contracts module model Class
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class ServiceContracts_Module_Model extends Vtiger_Module_Model
{

	/**
	 * Function to get list view query for popup window
	 * @param <String> $sourceModule Parent module
	 * @param <String> $field parent fieldname
	 * @param <Integer> $record parent id
	 * @param <String> $listQuery
	 * @return <String> Listview Query
	 */
	public function getQueryByRelatedField(Vtiger_ListView_Model $listviewModel, $listQuery)
	{
		if ($listviewModel->get('src_module') == 'HelpDesk' && !$listviewModel->isEmpty('filterFields')) {
			$filterFields = $listviewModel->get('filterFields');
			if (!empty($filterFields['parent_id'])) {
				return $listQuery . ' && sc_related_to = ' . $filterFields['parent_id'];
			}
		}
	}
}
