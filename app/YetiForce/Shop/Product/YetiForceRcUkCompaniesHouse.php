<?php
/**
 * YetiForce shop YetiForceRcUkCompaniesHouse file.
 *
 * @see App\RecordCollectors\UkCompaniesHouse
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop\Product;

/**
 * YetiForce shop YetiForceRcUkCompaniesHouse class.
 */
class YetiForceRcUkCompaniesHouse extends \App\YetiForce\Shop\AbstractBaseProduct
{
	/** {@inheritdoc} */
	public function getAdditionalButtons(): array
	{
		$return = [];
		if (\App\Security\AdminAccess::isPermitted('RecordCollector')) {
			$return[] = \Vtiger_Link_Model::getInstanceFromValues([
				'linklabel' => 'RecordCollector',
				'relatedModuleName' => 'Settings:RecordCollector',
				'linkicon' => 'yfi-record-collectors mr-2',
				'linkhref' => true,
				'linkurl' => 'index.php?parent=Settings&module=RecordCollector&view=List',
				'linkclass' => 'btn-primary',
				'showLabel' => 1,
			]);
		}
		return array_merge([
			\Vtiger_Link_Model::getInstanceFromValues([
				'linklabel' => 'Website',
				'relatedModuleName' => '_Base',
				'linkicon' => 'fas fa-globe mr-2',
				'linkhref' => true,
				'linkExternal' => true,
				'linktarget' => '_blank',
				'linkurl' => $this->website,
				'linkclass' => 'btn-info',
				'showLabel' => 1,
			]),
			\Vtiger_Link_Model::getInstanceFromValues([
				'linklabel' => 'company-information.service.gov.uk',
				'relatedModuleName' => 'Settings:_Base',
				'linkicon' => 'fa-solid fa-link',
				'linkhref' => true,
				'linkExternal' => true,
				'linktarget' => '_blank',
				'linkurl' => 'https://developer.company-information.service.gov.uk/',
				'linkclass' => 'btn-secondary',
				'showLabel' => 1,
			]),
		], $return);
	}
}
