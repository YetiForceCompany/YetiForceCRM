<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

class Rss_Record_Model extends Vtiger_Record_Model
{
	/**
	 * Function to get the id of the Record.
	 *
	 * @return int - Report Id
	 */
	public function getId()
	{
		return $this->get('rssid');
	}

	/**
	 * Function to set the id of the Record.
	 *
	 * @param int $value - id value
	 *
	 * @return Rss_Record_Model - current instance
	 */
	public function setId($value)
	{
		return $this->set('rssid', $value);
	}

	/**
	 * Function to get the Name of the record.
	 *
	 * @return string - Entity Name of the record
	 */
	public function getName(): string
	{
		return $this->get('rsstitle');
	}

	/**
	 * Function to get Rss fetched object.
	 *
	 * @return array
	 */
	public function getRssItems(): array
	{
		return $this->get('rss') ?: [];
	}

	/**
	 * Function to set Rss Object.
	 *
	 * @param SimplePie $rss - Rss fetched object
	 *
	 * @return void
	 */
	public function setRssItems(SimplePie $rss): void
	{
		$items = [];
		foreach ($rss->get_items() as $announcement) {
			if (!\App\Validator::url((string) $announcement->get_link())) {
				continue;
			}
			$title = App\Purifier::decodeHtml(\App\Purifier::purify(App\Purifier::decodeHtml($announcement->get_title())));
			$items[] = [
				'title' => \App\TextUtils::textTruncate($title, 100),
				'link' => App\Purifier::decodeHtml($announcement->get_link()),
				'date' => \App\Fields\DateTime::formatToViewDate($announcement->get_date('Y-m-d H:i:s')),
				'fullTitle' => $title
			];
		}
		$this->set('rss', $items);
	}

	/**
	 * Function to set Rss values.
	 *
	 * @param SimplePie $rss - Rss fetched object
	 *
	 * @return void
	 */
	public function setRssChannel(SimplePie $rss): void
	{
		$this->set('rsstitle', App\Purifier::decodeHtml(\App\Purifier::purify(App\Purifier::decodeHtml($rss->get_title()))));
		if (\App\Validator::url($rss->get_link())) {
			$this->set('url', \App\Purifier::purifyByType($rss->get_link(), \App\Purifier::URL));
		}
	}

	/**
	 * Function to save the record.
	 *
	 * @param string $url
	 */
	public function saveRecord($url)
	{
		$title = $this->getName();
		if ('' === $title) {
			$title = $url;
		}
		$db = \App\Db::getInstance();
		$insert = $db->createCommand()->insert('vtiger_rss', ['rssurl' => $url, 'rsstitle' => $title])->execute();

		if ($insert) {
			$id = $db->getLastInsertID('vtiger_rss_rssid_seq');
			$this->setId($id);

			return $id;
		}
		return false;
	}

	/**
	 * Function to delete a record.
	 */
	public function delete()
	{
		\App\Db::getInstance()->createCommand()->delete('vtiger_rss', ['rssid' => $this->getId()])->execute();
	}

	/**
	 * Function to make a record default for an rss record.
	 */
	public function makeDefault()
	{
		$recordId = $this->getId();
		$dbCommand = \App\Db::getInstance()->createCommand();
		$dbCommand->update('vtiger_rss', ['starred' => 0])->execute();
		$dbCommand->update('vtiger_rss', ['starred' => 1], ['rssid' => $recordId])->execute();
	}

	/**
	 * Function to get record instance by using id and moduleName.
	 *
	 * @param int    $recordId
	 * @param string $qualifiedModuleName
	 *
	 * @return Rss_Record_Model RecordModel
	 */
	public static function getInstanceById($recordId, $qualifiedModuleName = null)
	{
		$rowData = (new \App\Db\Query())->from('vtiger_rss')->where(['rssid' => $recordId])->one();
		if ($rowData) {
			$recordModel = new self();
			$recordModel->setData($rowData);
			$recordModel->setModule($qualifiedModuleName);

			$feed = self::getRssClient($recordModel->get('rssurl'));
			if ($feed->init()) {
				$recordModel->setRssChannel($feed);
				$recordModel->setRssItems($feed);
			} elseif ($error = $feed->error()) {
				$recordModel->set('error', $error);
				\App\Log::warning($error, 'RSS');
			}
			return $recordModel;
		}
		return false;
	}

	/**
	 * Function to get clean record instance by using moduleName.
	 *
	 * @param string $qualifiedModuleName
	 *
	 * @return Rss_Record_Model
	 */
	public static function getCleanInstance($qualifiedModuleName)
	{
		$recordModel = new self();

		return $recordModel->setModule($qualifiedModuleName);
	}

	/**
	 * Function to validate the rss url.
	 *
	 * @param string $url
	 *
	 * @return bool
	 */
	public function validateRssUrl($url)
	{
		$feed = new SimplePie();
		$feed->set_cache_location(ROOT_DIRECTORY . '/cache/rss_cache');
		$feed->set_feed_url($url);
		if ($feed->init()) {
			$this->setRssChannel($feed);
			return true;
		}
		if ($error = $feed->error()) {
			\App\Log::warning($error, 'RSS');
			return false;
		}
	}

	/**
	 * Function to get the default rss.
	 */
	public function getDefaultRss()
	{
		$recordId = (new \App\Db\Query())->select(['rssid'])->from('vtiger_rss')->where(['starred' => 1])->scalar();
		if ($recordId) {
			$this->setId($recordId);
		} else {
			$recordId = (new \App\Db\Query())->select(['rssid'])->from('vtiger_rss')->scalar();
			$this->setId($recordId);
		}
	}

	/**
	 * Get rss client.
	 *
	 * @param string $url
	 *
	 * @return SimplePie
	 */
	public static function getRssClient(string $url): SimplePie
	{
		$feed = new SimplePie();
		if (!empty(\Config\Security::$proxyConnection)) {
			$proxy = [];
			$proxy[CURLOPT_PROXY] = \Config\Security::$proxyHost;
			if (!empty(\Config\Security::$proxyPort)) {
				$proxy[CURLOPT_PROXYPORT] = \Config\Security::$proxyPort;
			}
			if (!empty(\Config\Security::$proxyLogin) || !empty(\Config\Security::$proxyPassword)) {
				$login = \Config\Security::$proxyLogin ?? '';
				if (!empty(\Config\Security::$proxyPassword)) {
					$login .= ':' . \Config\Security::$proxyPassword;
				}
				$proxy[CURLOPT_PROXYUSERPWD] = $login;
			}
			$feed->set_curl_options($proxy);
		}
		$feed->set_cache_location(ROOT_DIRECTORY . '/cache/rss_cache');
		$feed->set_feed_url($url);
		return $feed;
	}
}
