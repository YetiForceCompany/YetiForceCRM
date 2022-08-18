<?php

/**
 * Widget to display RSS.
 *
 * @package Dashboard
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_Rss_Dashboard extends Vtiger_IndexAjax_View
{
	public function process(App\Request $request, $widget = null)
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
		$errors = $items = [];
		foreach ($data['channels'] as $rss) {
			$feed = Rss_Record_Model::getRssClient($rss);
			if ($feed->init()) {
				foreach ($feed->get_items(0, 10) as $announcement) {
					if (!\App\Validator::url((string) $announcement->get_link())) {
						continue;
					}

					$title = App\Purifier::decodeHtml(\App\Purifier::purify(App\Purifier::decodeHtml($announcement->get_title())));
					$items[] = [
						'title' => \App\TextUtils::textTruncate($title, 50),
						'link' => App\Purifier::decodeHtml($announcement->get_link()),
						'date' => \App\Fields\DateTime::formatToViewDate($announcement->get_date('Y-m-d H:i:s')),
						'fullTitle' => $title,
						'source' => $rss,
					];
				}
			} elseif ($error = $feed->error()) {
				$errors[$rss] = $error;
				\App\Log::warning($error, 'RSS');
			}
		}
		$viewer->assign('ERRORS', $errors);
		$viewer->assign('LIST_SUBJECTS', $items);
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		if ($request->has('content')) {
			$viewer->view('dashboards/RssContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/RssHeader.tpl', $moduleName);
		}
	}
}
