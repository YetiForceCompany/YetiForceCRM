<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************************************************************** */

class Assets_ExpiringSoldProducts_Dashboard extends Vtiger_IndexAjax_View
{

	public function process(Vtiger_Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$widget = Vtiger_Widget_Model::getInstance($request->get('linkid'), $currentUser->getId());
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('RELATED_MODULE', 'Assets');
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DATA', self::getData($request, $widget));
		//Include special script and css needed for this widget
		$viewer->assign('CURRENTUSER', $currentUser);
		if (!$request->isEmpty('content')) {
			$viewer->view('dashboards/ExpiringSoldProductsContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/ExpiringSoldProducts.tpl', $moduleName);
		}
	}

	public static function getData(Vtiger_Request $request, $widget)
	{
		$fields = ['id', 'assetname', 'dateinservice', 'parent_id'];
		$limit = 10;
		if (!empty($widget->get('limit'))) {
			$limit = $widget->get('limit');
		}
		$queryGenerator = new App\QueryGenerator('Assets');
		$queryGenerator->setFields($fields);
		$query = $queryGenerator->createQuery();
		$showtype = $request->get('showtype');
		if ($showtype === 'common') {
			$subQuery = (new \App\Db\Query())->select('crmid')->from('u_#__crmentity_showners')->where(['userid' => App\User::getCurrentUserId()])->distinct('crmid');
			$query->andWhere(['in', 'vtiger_crmentity.smownerid', $subQuery]);
		} else {
			$query->andWhere(['vtiger_crmentity.smownerid' => App\User::getCurrentUserId()]);
		}
		$query->orderBy('vtiger_assets.dateinservice');
		$query->limit($limit);
		return $query->all();
	}
}
