<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */
namespace vtlib;

include_once('vtlib/Vtiger/Utils/StringTemplate.php');

/**
 * Provides API to handle custom links
 * @package vtlib
 */
class Link
{

	public $tabid;
	public $linkid;
	public $linktype;
	public $linklabel;
	public $linkurl;
	public $linkicon;
	public $glyphicon;
	public $sequence;
	public $status = false;
	public $handler_path;
	public $handler_class;
	public $handler;
	public $params;

	// Ignore module while selection
	const IGNORE_MODULE = -1;

	/**
	 * Initialize this instance.
	 */
	public function initialize($valuemap)
	{
		foreach ($valuemap as $key => $value) {
			if ($key == 'linkurl' || $key == 'linkicon') {
				$this->$key = decode_html($value);
			} else {
				$this->$key = $value;
			}
		}
	}

	/**
	 * Get module name.
	 */
	public function module()
	{
		if (!empty($this->tabid)) {
			return \App\Module::getModuleName($this->tabid);
		}
		return false;
	}

	/** Cache (Record) the schema changes to improve performance */
	static $__cacheSchemaChanges = [];

	/**
	 * Add link given module
	 * @param Integer Module ID
	 * @param String Link Type (like DETAILVIEW). Useful for grouping based on pages.
	 * @param String Label to display
	 * @param String HREF value or URL to use for the link
	 * @param String ICON to use on the display
	 * @param Integer Order or sequence of displaying the link
	 */
	static function addLink($tabid, $type, $label, $url, $iconpath = '', $sequence = 0, $handlerInfo = null, $linkParams = null)
	{
		$db = \App\Db::getInstance();
		if ($tabid != 0) {
			$checkres = (new \App\Db\Query())->from('vtiger_links')
				->where(['tabid' => $tabid, 'linktype' => $type, 'linkurl' => $url, 'linkicon' => $iconpath, 'linklabel' => $label])
				->exists();
		}
		if ($tabid == 0 || !$checkres) {
			$params = [
				'tabid' => $tabid,
				'linktype' => $type,
				'linklabel' => $label,
				'linkurl' => $url,
				'linkicon' => $iconpath,
				'sequence' => intval($sequence),
			];
			if (!empty($handlerInfo)) {
				$params['handler_path'] = $handlerInfo['path'];
				$params['handler_class'] = $handlerInfo['class'];
				$params['handler'] = $handlerInfo['method'];
			}
			if (!empty($linkParams)) {
				$params['params'] = $linkParams;
			}
			$db->createCommand()->insert('vtiger_links', $params)->execute();
			self::log("Adding Link ($type - $label) ... DONE");
		}
	}

	/**
	 * Delete link of the module
	 * @param Integer Module ID
	 * @param String Link Type (like DETAILVIEW). Useful for grouping based on pages.
	 * @param String Display label
	 * @param String URL of link to lookup while deleting
	 */
	static function deleteLink($tabid, $type, $label, $url = false)
	{
		$db = \App\Db::getInstance();
		if ($url) {
			$db->createCommand()->delete('vtiger_links', [
				'tabid' => $tabid,
				'linktype' => $type,
				'linklabel' => $label,
				'linkurl' => $url
			])->execute();
			self::log("Deleting Link ($type - $label - $url) ... DONE");
		} else {
			$db->createCommand()->delete('vtiger_links', [
				'tabid' => $tabid,
				'linktype' => $type,
				'linklabel' => $label,
			])->execute();
			self::log("Deleting Link ($type - $label) ... DONE");
		}
	}

	/**
	 * Delete all links related to module
	 * @param Integer Module ID.
	 */
	static function deleteAll($tabid)
	{
		\App\Db::getInstance()->createCommand()->delete('vtiger_links', ['tabid' => $tabid])->execute();
		self::log("Deleting Links ... DONE");
	}

	/**
	 * Get all the links related to module
	 * @param Integer Module ID.
	 */
	static function getAll($tabid)
	{
		return self::getAllByType($tabid);
	}

	/**
	 * Get all the link related to module based on type
	 * @param Integer Module ID
	 * @param mixed String or List of types to select 
	 * @param Map Key-Value pair to use for formating the link url
	 */
	static function getAllByType($tabid, $type = false, $parameters = false)
	{
		$currentUser = \Users_Record_Model::getCurrentUserModel();
		if (\App\Cache::has('AllLinks', 'ByType')) {
			$rows = \App\Cache::get('AllLinks', 'ByType');
		} else {
			$linksFromDb = (new \App\Db\Query())->from('vtiger_links')->all();
			$rows = [];
			foreach ($linksFromDb as $row) {
				$rows [$row['tabid']] [$row['linktype']] [] = $row;
			}
			\App\Cache::save('AllLinks', 'ByType', $rows);
		}

		$multitype = false;
		$links = [];
		if ($type !== false) {
			if (is_array($type)) {
				$multitype = true;
				if ($tabid === self::IGNORE_MODULE) {
					$permittedTabIdList = getPermittedModuleIdList();
					if (!empty($permittedTabIdList)) {
						$permittedTabIdList [] = 0;  // Added to support one link for all modules
						foreach ($permittedTabIdList as $moduleId) {
							foreach ($type as $typ) {
								if (isset($rows[$moduleId][$typ])) {
									foreach ($rows[$moduleId][$typ] as $data) {
										$links[] = $data;
									}
								}
							}
						}
					}
				} else {
					foreach ($type as $typeLink) {
						if (isset($rows[0][$typeLink])) {
							foreach ($rows[0][$typeLink] as $data) {
								$links[] = $data;
							}
						}
						if (isset($rows[$tabid][$typeLink])) {
							foreach ($rows[$tabid][$typeLink] as $data) {
								$links[] = $data;
							}
						}
					}
				}
			} else {
				if ($tabid === self::IGNORE_MODULE) {
					foreach ($rows as $row) {
						if (isset($row[$type])) {
							foreach ($row[$type] as $data) {
								$links[] = $data;
							}
						}
					}
				} else {
					if (isset($rows[0][$type])) {
						foreach ($rows[0][$type] as $data) {
							$links[] = $data;
						}
					}
					if (isset($rows[$tabid][$type])) {
						foreach ($rows[$tabid][$type] as $data) {
							$links[] = $data;
						}
					}
				}
			}
		} else {
			foreach ($rows[$tabid] as $linkType) {
				foreach ($linkType as $data) {
					$links[] = $data;
				}
			}
		}

		$strtemplate = new \Vtiger_StringTemplate();
		if ($parameters) {
			foreach ($parameters as $key => $value)
				$strtemplate->assign($key, $value);
		}

		$instances = [];
		if ($multitype) {
			foreach ($type as $t)
				$instances[$t] = [];
		}
		foreach ($links as $row) {
			$instance = new self();
			$instance->initialize($row);
			if (!empty($row['handler_path']) && \vtlib\Deprecated::isFileAccessible($row['handler_path'])) {
				\vtlib\Deprecated::checkFileAccessForInclusion($row['handler_path']);
				require_once $row['handler_path'];
				$linkData = new LinkData($instance, vglobal('current_user'));
				$ignore = call_user_func(array($row['handler_class'], $row['handler']), $linkData);
				if (!$ignore) {
					self::log('Ignoring Link ... ' . var_export($row, true));
					continue;
				}
			}
			if ($parameters) {
				$instance->linkurl = $strtemplate->merge($instance->linkurl);
				$instance->linkicon = $strtemplate->merge($instance->linkicon);
			}
			if ($multitype) {
				$instances[$instance->linktype][] = $instance;
			} else {
				$instances[$instance->linktype] = $instance;
			}
		}
		return $instances;
	}

	/**
	 * Extract the links of module for export.
	 */
	static function getAllForExport($tabid)
	{
		$dataReader = (new \App\Db\Query())->from('vtiger_links')
				->where(['tabid' => $tabid])
				->createCommand()->query();
		$links = [];
		while ($row = $dataReader->read()) {
			$instance = new self();
			$instance->initialize($row);
			$links[] = $instance;
		}
		return $links;
	}

	/**
	 * Helper function to log messages
	 * @param String Message to log
	 * @param Boolean true appends linebreak, false to avoid it
	 * @access private
	 */
	static function log($message, $delimit = true)
	{
		Utils::Log($message, $delimit);
	}

	/**
	 * Checks whether the user is admin or not
	 * @param vtlib\LinkData $linkData
	 * @return Boolean
	 */
	static function isAdmin($linkData)
	{
		$user = $linkData->getUser();
		return $user->is_admin == 'on' || $user->column_fields['is_admin'] == 'on';
	}
}
