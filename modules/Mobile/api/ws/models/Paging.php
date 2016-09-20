<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Mobile_WS_PagingModel
{

	var $_start;
	var $_limit;
	var $_page;

	public function __construct()
	{
		$this->_limit = Mobile::config('API_RECORD_FETCH_LIMIT', 20);
	}

	public function start()
	{
		return $this->_start;
	}

	public function limit()
	{
		return $this->_limit;
	}

	public function currentCount()
	{
		return ($this->current() * $this->limit());
	}

	public function current()
	{
		return $this->_page;
	}

	public function next()
	{
		return ($this->current() + 1);
	}

	public function previous()
	{
		return ($this->current() < 1 ? 0 : ($this->current() - 1));
	}

	public function hasNext($countOnPage)
	{
		return ($countOnPage >= $this->limit());
	}

	public function hasPrevious()
	{
		return ($this->start() != 0);
	}

	public function initStart($page)
	{

		if (empty($page))
			$page = 0;
		$this->_page = $page;

		if ($page < 1)
			$this->_start = 0;
		else
			$this->_start = ($page * $this->_limit);
	}

	public function setLimit($limit)
	{
		$this->_limit = $limit;
	}

	static function modelWithPageStart($start)
	{
		$instance = new self();
		$instance->initStart($start);
		return $instance;
	}
}

?>
