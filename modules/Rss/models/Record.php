<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */
vimport('~libraries/RSSFeeds/Feed.php');

// for rss caching
Feed::$cacheDir = 'cache/rss_cache';

class Rss_Record_Model extends Vtiger_Record_Model
{

	/**
	 * Function to get the id of the Record
	 * @return <Number> - Report Id
	 */
	public function getId()
	{
		return $this->get('rssid');
	}

	/**
	 * Function to set the id of the Record
	 * @param <type> $value - id value
	 * @return <Object> - current instance
	 */
	public function setId($value)
	{
		return $this->set('rssid', $value);
	}

	/**
	 * Fuction to get the Name of the Record
	 * @return string
	 */
	public function getName()
	{
		return $this->get('rsstitle');
	}

	/**
	 * Function to get Rss fetched object
	 * @return <object> - Rss Object
	 */
	public function getRssObject()
	{
		return $this->get('rss');
	}

	/**
	 * Function to set Rss Object
	 * @param <object> $rss - rss fetched object
	 */
	public function setRssObject($rss)
	{
		return $this->set('rss', $rss->item);
	}

	/**
	 * Function to set Rss values
	 * @param <object> $rss - Rss fetched object
	 */
	public function setRssValues($rss)
	{
		$this->set('rsstitle', $rss->title);
		$this->set('url', $rss->link);
	}

	/**
	 * Function to save the record
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
	 * Function to delete a record
	 */
	public function delete()
	{
		$db = PearDatabase::getInstance();
		$recordId = $this->getId();

		$sql = 'DELETE FROM vtiger_rss where rssid = ?';
		$db->pquery($sql, array($recordId));
	}

	/**
	 * Function to make a record default for an rss record
	 */
	public function makeDefault()
	{
		$db = PearDatabase::getInstance();
		$recordId = $this->getId();

		$sql = 'UPDATE vtiger_rss set starred = 0';
		$db->pquery($sql, array());

		$sql = 'UPDATE vtiger_rss set starred = 1 where rssid = ?';
		$db->pquery($sql, array($recordId));
	}

	/**
	 * Function to get record instance by using id and moduleName
	 * @param integer $recordId
	 * @param string $qualifiedModuleName
	 * @return Rss_Record_Model RecordModel
	 */
	static public function getInstanceById($recordId, $qualifiedModuleName = null)
	{
		$rowData = (new \App\Db\Query)->from('vtiger_rss')->where(['rssid' => $recordId])->one();

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
	 * Function to set the sender address to the record
	 * @param <array> $rssItems
	 * @return <array> $items
	 */
	public function setSenderInfo(&$rssItems)
	{
		foreach ($rssItems as $item) {
			$item->sender = $this->getName();
		}
	}

	/**
	 * Function to get clean record instance by using moduleName
	 * @param string $qualifiedModuleName
	 * @return <Settings_SMSNotifier_Record_Model>
	 */
	static public function getCleanInstance($qualifiedModuleName)
	{
		$recordModel = new self();
		return $recordModel->setModule($qualifiedModuleName);
	}

	/**
	 * Function to validate the rss url
	 * @param string $url
	 * @return <boolean> 
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
	 * Function to get the default rss
	 */
	public function getDefaultRss()
	{
		$db = PearDatabase::getInstance();

		$result = $db->pquery('SELECT rssid FROM vtiger_rss where starred = 1', array());
		$recordId = $db->query_result($result, '0', 'rssid');
		if ($recordId) {
			$this->setId($recordId);
		} else {
			$result = $db->pquery('SELECT rssid FROM vtiger_rss', array());
			$recordId = $db->query_result($result, '0', 'rssid');
			$this->setId($recordId);
		}
	}

}
