<?php

/**
 * Widget to display RSS
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Vtiger_Rss_Dashboard extends Vtiger_IndexAjax_View
{

	public function process(Vtiger_Request $request, $widget = NULL)
	{
		vimport('~libraries/RSSFeeds/Feed.php');
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		if ($widget && !$request->has('widgetid')) {
			$widgetId = $widget->get('id');
		} else {
			$widgetId = $request->get('widgetid');
		}
		$widget = Vtiger_Widget_Model::getInstanceWithWidgetId($widgetId, $currentUser->getId());
		$data = $widget->get('data');
		$data = \App\Json::decode(decode_html($data));
		$listSubjects = [];
		foreach ($data['channels'] as $rss) {
			try {
				$rssContent = Feed::loadRss($rss);
			} catch (FeedException $ex) {
				continue;
			}
			if (!empty($rssContent)) {
				foreach ($rssContent->item as $item) {
					$date = new DateTime($item->pubDate);
					$date = DateTimeField::convertToUserFormat($date->format('Y-m-d H:i:s'));
					$listSubjects[] = [
						'title' => strlen($item->title) > 40 ? substr($item->title, 0, 40) . '...' : $item->title,
						'link' => $item->link,
						'date' => $date,
						'fullTitle' => $item->title,
						'source' => $rss
					];
				}
			}
		}
		$viewer->assign('LIST_SUCJECTS', $listSubjects);
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$content = $request->get('content');
		if (!empty($content)) {
			$viewer->view('dashboards/RssContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/RssHeader.tpl', $moduleName);
		}
	}
}
