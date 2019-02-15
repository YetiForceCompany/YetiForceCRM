<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

// for rss caching
Feed::$cacheDir = 'cache/rss_cache';

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
	 * Fuction to get the Name of the Record.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->get('rsstitle');
	}

	/**
	 * Function to get Rss fetched object.
	 *
	 * @return <object> - Rss Object
	 */
	public function getRssObject()
	{
		return $this->get('rss');
	}

	/**
	 * Function to set Rss Object.
	 *
	 * @param <object> $rss - rss fetched object
	 */
	public function setRssObject($rss)
	{
		return $this->set('rss', $rss->item);
	}

	/**
	 * Function to set Rss values.
	 *
	 * @param <object> $rss - Rss fetched object
	 */
	public function setRssValues($rss)
	{
		$this->set('rsstitle', \App\Purifier::purifyByType((string) $rss->title, 'Text'));
		$this->set('url', $rss->link);
	}

	/**
	 * Function to save the record.
	 *
	 * @param string $url
	 */
	public function saveRecord($url)
	{
		$title = $this->getName();
		if ($title === '') {
			$title = $url;
		}
		$db = \App\Db::getInstance();
		$insert = $db->createCommand()->insert('vtiger_rss', ['rssurl' => $url, 'rsstitle' => $title])->execute();

		if ($insert) {
			$id = $db->getLastInsertID('vtiger_rss_rssid_seq');
			$this->setId($id);

			return $id;
		} else {
			return false;
		}
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
			$rss = Feed::loadRss($recordModel->get('rssurl'));
			$recordModel->setSenderInfo($rss->item);
			$recordModel->setRssValues($rss);
			$recordModel->setRssObject($rss);

			return $recordModel;
		}
		return false;
	}

	/**
	 * Function to set the sender address to the record.
	 *
	 * @param array $rssItems
	 *
	 * @return array $items
	 */
	public function setSenderInfo(&$rssItems)
	{
		foreach ($rssItems as $item) {
			$item->sender = $this->getName();
		}
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
		try {
			$rss = Feed::loadRss($url);
			if ($rss) {
				$this->setRssValues($rss);

				return true;
			} else {
				return false;
			}
		} catch (FeedException $ex) {
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
}
