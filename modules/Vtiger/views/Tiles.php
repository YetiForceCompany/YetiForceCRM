<?php
/**
 * Tiles view file.
 *
 * @package View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 */
/**
 * Tiles view class.
 */
class Vtiger_Tiles_View extends Vtiger_List_View
{
	/** @var array Mapping size of tiles to number of columns */
	const TILES_SIZES = ['very_small' => 2, 'small' => 3, 'medium' => 4, 'big' => 6];
	/** {@inheritdoc} */
	protected $pageTitle = 'LBL_TILES_VIEW';

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		if (!Users_Privileges_Model::getCurrentUserPrivilegesModel()->hasModuleActionPermission($request->getModule(), 'TilesView')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * Function to initialize the required data in smarty to display the Tiles view contents.
	 *
	 * @param App\Request   $request
	 * @param Vtiger_Viewer $viewer
	 *
	 * @return void
	 */
	public function initializeListViewContents(App\Request $request, Vtiger_Viewer $viewer): void
	{
		parent::initializeListViewContents($request, $viewer);
		$tileSize = $request->isEmpty('tile_size') ? App\Config::layout('tileDefaultSize', 'very_small') : $request->getByType('tile_size');
		$viewer->assign('TILE_SIZE', $tileSize);
		$viewer->assign('TILE_COLUMN_SIZE', $this->getTileColumnNumbers($tileSize));
	}

	/**
	 * Get column numbers based on size of view.
	 *
	 * @param string $tileSize
	 *
	 * @return int
	 */
	public function getTileColumnNumbers(string $tileSize): int
	{
		return self::TILES_SIZES[$tileSize] ?? 4;
	}

	/** {@inheritdoc} */
	public function getProcessTemplate(): string
	{
		return 'TilesContents.tpl';
	}
}
