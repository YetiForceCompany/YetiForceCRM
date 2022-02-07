<?php
/**
 * YetiForce shop YetiForceKanban file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop\Product;

/**
 * YetiForce shop YetiForceKanban class.
 */
class YetiForceKanban extends \App\YetiForce\Shop\AbstractBaseProduct
{
	/** {@inheritdoc} */
	public $label = 'YetiForce Kanban Board';

	/** {@inheritdoc} */
	public $category = 'Addons';

	/** {@inheritdoc} */
	public $website = 'https://yetiforce.com/en/yetiforce-kanban';

	/** {@inheritdoc} */
	public $prices = [
		'Micro' => 5,
		'Small' => 12,
		'Medium' => 25,
		'Large' => 50,
		'Corporation' => 100,
	];

	/** {@inheritdoc} */
	public $featured = true;

	/** {@inheritdoc} */
	public function verify(): array
	{
		$message = $status = true;
		if (\App\YetiForce\Register::getProducts('YetiForceKanban')) {
			[$status, $message] = \App\YetiForce\Shop::checkWithMessage('YetiForceKanban');
		} else {
			$message = 'LBL_PAID_FUNCTIONALITY_ACTIVATED';
			$status = !(new \App\Db\Query())->from('s_#__kanban_boards')->exists();
		}
		return ['status' => $status, 'message' => $message];
	}

	/** {@inheritdoc} */
	public function getAdditionalButtons(): array
	{
		$links = [
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
		];
		if (\App\Security\AdminAccess::isPermitted('Kanban')) {
			$links[] = \Vtiger_Link_Model::getInstanceFromValues([
				'linklabel' => 'LBL_KANBAN',
				'relatedModuleName' => 'Settings:Kanban',
				'linkicon' => 'yfi yfi-kanban',
				'linkhref' => true,
				'linkurl' => 'index.php?parent=Settings&module=Kanban&view=Index',
				'linkclass' => 'btn-primary',
				'showLabel' => 1,
			]);
		}
		return $links;
	}
}
