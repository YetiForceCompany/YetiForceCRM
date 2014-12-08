<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
require_once('vtlib/thirdparty/parser/feed/simplepie.inc');

/**
 * Extends SimplePie (feed parser library for Rss, Atom, etc)
 * @package vtlib
 */
class Vtiger_Feed_Parser extends SimplePie {
	var $vt_cachelocation = 'test/vtlib/feedcache';
	var $vt_fetchdone = false;

	/**
	 * Parse the feed url.
	 * @param String Feed url (RSS, ATOM etc)
	 * @param Integer Timeout value (to try connecting to url)
	 */
	function vt_dofetch($url, $timeout=10) {
		$this->set_timeout($timeout);
		$this->set_feed_url($url);
		$this->enable_order_by_date(false);
		$this->enable_cache(false);
		$this->init();
		$this->vt_fetchdone = true;
	}

	/**
	 * Parse the content as feed.
	 * @param String Feed content
	 */
	function vt_doparse($content) {
		$this->set_raw_data($content);
		$this->init();
		$this->vt_fetchdone = true;
	}
}
?>
