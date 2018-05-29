<?php
/**
 * Project ListView Model.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

/**
 * ListView Model Class for Project module.
 */
class Project_ListView_Model extends Vtiger_ListView_Model
{
	/**
	 * Function to get the list of listview links.
	 *
	 * @param <Array> $linkParams Parameters to be replaced in the link template
	 *
	 * @return <Array> - an array of Vtiger_Link_Model instances
	 */
	public function getListViewLinks($linkParams)
	{
		$links = parent::getListViewLinks($linkParams);

		$quickLinks = [
			[
				'linktype' => 'LISTVIEWQUICK',
				'linklabel' => 'Tasks List',
				'linkurl' => $this->getModule()->getDefaultUrl(),
				'linkicon' => '',
			],
		];
		foreach ($quickLinks as $quickLink) {
			$links['LISTVIEWQUICK'][] = Vtiger_Link_Model::getInstanceFromValues($quickLink);
		}

		return $links;
	}
}
