<?php

/**
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Assets_ExpiringSoldProducts_Dashboard extends Vtiger_IndexAjax_View
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$widget = Vtiger_Widget_Model::getInstance($request->getInteger('linkid'), $currentUser->getId());
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('RELATED_MODULE', 'Assets');
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DATA', self::getData($request, $widget));
		//Include special script and css needed for this widget
		$viewer->assign('CURRENTUSER', $currentUser);
		if ($request->has('content')) {
			$viewer->view('dashboards/ExpiringSoldProductsContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/ExpiringSoldProducts.tpl', $moduleName);
		}
	}

	public static function getData(App\Request $request, $widget)
	{
		$fields = ['id', 'assetname', 'dateinservice', 'parent_id'];
		$limit = 10;
		if (!empty($widget->get('limit'))) {
			$limit = $widget->get('limit');
		}
		$queryGenerator = new App\QueryGenerator('Assets');
		$queryGenerator->setFields($fields);
		$query = $queryGenerator->createQuery();
		if ('common' === $request->getByType('showtype')) {
			$subQuery = (new \App\Db\Query())->select(['crmid'])->from('u_#__crmentity_showners')->where(['userid' => App\User::getCurrentUserId()])->distinct('crmid');
			$query->andWhere(['in', 'vtiger_crmentity.smownerid', $subQuery]);
		} else {
			$query->andWhere(['vtiger_crmentity.smownerid' => App\User::getCurrentUserId()]);
		}
		$query->orderBy('vtiger_assets.dateinservice');
		$query->limit($limit);

		return $query->all();
	}
}
