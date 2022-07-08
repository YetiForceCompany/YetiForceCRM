<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ********************************************************************************** */

namespace vtlib;

include_once 'vtlib/Vtiger/Utils/StringTemplate.php';

/**
 * Provides API to handle custom links.
 */
class Link
{
	public $tabid;
	public $linkid;
	public $linktype;
	public $linklabel;
	public $linkurl;
	public $linkicon;
	public $icon;
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
	 *
	 * @param array $valuemap
	 */
	public function initialize($valuemap)
	{
		foreach ($valuemap as $key => $value) {
			if (!empty($value) && ('linkurl' == $key || 'linkicon' == $key)) {
				$this->{$key} = \App\Purifier::decodeHtml($value);
			} else {
				$this->{$key} = $value;
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
	public static $__cacheSchemaChanges = [];

	/**
	 * Add link given module.
	 *
	 * @param int         $tabid       Module ID
	 * @param string      $type        Link Type (like DETAIL_VIEW_BASIC). Useful for grouping based on pages
	 * @param string      $label       Label to display
	 * @param string      $url         HREF value or URL to use for the link
	 * @param string      $iconpath    ICON to use on the display
	 * @param int         $sequence    Order or sequence of displaying the link
	 * @param array|null  $handlerInfo
	 * @param string|null $linkParams
	 */
	public static function addLink($tabid, $type, $label, $url, $iconpath = '', $sequence = 0, $handlerInfo = null, $linkParams = null)
	{
		$db = \App\Db::getInstance();
		if (0 != $tabid) {
			$exists = (new \App\Db\Query())->from('vtiger_links')
				->where(['tabid' => $tabid, 'linktype' => $type, 'linkurl' => $url, 'linkicon' => $iconpath, 'linklabel' => $label])
				->exists();
		}
		if (0 == $tabid || !$exists) {
			$params = [
				'tabid' => $tabid,
				'linktype' => $type,
				'linklabel' => $label,
				'linkurl' => $url,
				'linkicon' => $iconpath,
				'sequence' => (int) $sequence,
			];
			if (!empty($handlerInfo)) {
				$params['handler_path'] = $handlerInfo['path'] ?? null;
				$params['handler_class'] = $handlerInfo['class'] ?? null;
				$params['handler'] = $handlerInfo['method'] ?? null;
			}
			if (!empty($linkParams)) {
				$params['params'] = $linkParams;
			}
			$db->createCommand()->insert('vtiger_links', $params)->execute();
			\App\Log::trace("Adding Link ($type - $label) ... DONE");
			\App\Cache::delete('AllLinks', 'ByType');
		}
	}

	/**
	 * Delete link of the module.
	 *
	 * @param int    $tabid Module ID
	 * @param string $type  Link Type (like DETAIL_VIEW_BASIC). Useful for grouping based on pages
	 * @param string $label Display label
	 * @param string $url   URL of link to lookup while deleting
	 */
	public static function deleteLink($tabid, $type, $label, $url = false)
	{
		$db = \App\Db::getInstance();
		if ($url) {
			$db->createCommand()->delete('vtiger_links', [
				'tabid' => $tabid,
				'linktype' => $type,
				'linklabel' => $label,
				'linkurl' => $url,
			])->execute();
			\App\Log::trace("Deleting Link ($type - $label - $url) ... DONE");
		} else {
			$db->createCommand()->delete('vtiger_links', [
				'tabid' => $tabid,
				'linktype' => $type,
				'linklabel' => $label,
			])->execute();
			\App\Log::trace("Deleting Link ($type - $label) ... DONE");
		}
		\App\Cache::delete('AllLinks', 'ByType');
	}

	/**
	 * Delete all links related to module.
	 *
	 * @param int $tabid Module ID
	 */
	public static function deleteAll($tabid)
	{
		\App\Db::getInstance()->createCommand()->delete('vtiger_links', ['tabid' => $tabid])->execute();
		\App\Log::trace('Deleting Links ... DONE');
		\App\Cache::delete('AllLinks', 'ByType');
	}

	/**
	 * Get all the links related to module.
	 *
	 * @param int $tabid Module ID
	 */
	public static function getAll($tabid)
	{
		return self::getAllByType($tabid);
	}

	/**
	 * Get all the link related to module based on type.
	 *
	 * @param int   $tabid      Module ID
	 * @param mixed $type       String or List of types to select
	 * @param array $parameters Map Key-Value pair to use for formating the link url
	 */
	public static function getAllByType($tabid, $type = false, $parameters = false)
	{
		if (\App\Cache::has('AllLinks', 'ByType')) {
			$rows = \App\Cache::get('AllLinks', 'ByType');
		} else {
			$linksFromDb = (new \App\Db\Query())->from('vtiger_links')->all();
			$rows = [];
			foreach ($linksFromDb as $row) {
				$rows[$row['tabid']][$row['linktype']][] = $row;
			}
			\App\Cache::save('AllLinks', 'ByType', $rows);
		}

		$multitype = false;
		$links = [];
		if (false !== $type) {
			if (\is_array($type)) {
				$multitype = true;
				if (self::IGNORE_MODULE === $tabid) {
					$permittedTabIdList = \vtlib\Deprecated::getPermittedModuleIdList();
					if (!empty($permittedTabIdList)) {
						$permittedTabIdList[] = 0;  // Added to support one link for all modules
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
				if (self::IGNORE_MODULE === $tabid) {
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
			foreach ($parameters as $key => $value) {
				$strtemplate->assign($key, $value);
			}
		}

		$instances = [];
		if ($multitype) {
			foreach ($type as $t) {
				$instances[$t] = [];
			}
		}
		foreach ($links as $row) {
			$instance = new self();
			$instance->initialize($row);
			if (!empty($row['handler_path']) && \vtlib\Deprecated::isFileAccessible($row['handler_path'])) {
				\vtlib\Deprecated::checkFileAccessForInclusion($row['handler_path']);
				require_once $row['handler_path'];
				$linkData = new LinkData($instance);
				$ignore = \call_user_func([$row['handler_class'], $row['handler']], $linkData);
				if (!$ignore) {
					\App\Log::trace('Ignoring Link ... ' . var_export($row, true));
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
	 *
	 * @param mixed $tabid
	 */
	public static function getAllForExport($tabid)
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
	 * Link data.
	 *
	 * @param int $linkId
	 *
	 * @return array
	 */
	public static function getLinkData($linkId)
	{
		if (\App\Cache::has('Link', $linkId)) {
			return \App\Cache::get('Link', $linkId);
		}
		$linkData = (new \App\Db\Query())->from('vtiger_links')->where(['linkid' => $linkId])->one();
		\App\Cache::save('Link', $linkId, $linkData);
		return $linkData;
	}
}
