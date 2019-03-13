<?php

/**
 * Widget to display RSS.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */
class Vtiger_Rss_Dashboard extends Vtiger_IndexAjax_View
{
	public function process(\App\Request $request, $widget = null)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		if ($widget && !$request->has('widgetid')) {
			$widgetId = $widget->get('id');
		} else {
			$widgetId = $request->getInteger('widgetid');
		}
		$widget = Vtiger_Widget_Model::getInstanceWithWidgetId($widgetId, $currentUser->getId());
		$data = $widget->get('data');
		$data = \App\Json::decode(App\Purifier::decodeHtml($data));
		$listSubjects = [];
		foreach ($data['channels'] as $rss) {
			try {
				$rssContent = Feed::loadRss($rss);
			} catch (FeedException $ex) {
				continue;
			}
			if (!empty($rssContent)) {
				foreach ($rssContent->item as $item) {
					if (!\App\Validator::url((string) $item->link)) {
						continue;
					}
					$date = new DateTime($item->pubDate);
					$date = DateTimeField::convertToUserFormat($date->format('Y-m-d H:i:s'));
					$title = \App\Purifier::purifyByType((string) $item->title, 'Text');
					$listSubjects[] = [
						'title' => strlen($title) > 40 ? substr($title, 0, 40) . '...' : $title,
						'link' => (string) $item->link,
						'date' => $date,
						'fullTitle' => $title,
						'source' => $rss,
					];
				}
			}
		}
		$viewer->assign('LIST_SUCJECTS', $listSubjects);
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		if ($request->has('content')) {
			$viewer->view('dashboards/RssContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/RssHeader.tpl', $moduleName);
		}
	}
}
