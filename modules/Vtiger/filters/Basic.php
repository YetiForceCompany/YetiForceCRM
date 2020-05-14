<?php

class Vtiger_Basic_Filter
{
	public $viewname = 'Basic';
	protected $columnList = [];
	protected $cvadvFilterAnd = [];
	protected $cvadvFilterOr = [];

	public function getViewName()
	{
		return $this->viewname;
	}

	public function getColumnList()
	{
		return $this->columnList;
	}

	public function getAdvftCriteria(CustomView $cv)
	{
		$columnindex = 0;
		$advft_criteria = [];
		$i = 1;
		$j = 0;

		if ($this->cvadvFilterAnd) {
			foreach ($this->cvadvFilterAnd as $cvadv) {
				$cvadv['columnindex'] = $columnindex;
				$criteria = $cv->getAdvftCriteria($cvadv);
				$advft_criteria[$i]['columns'][$j] = $criteria;
				$advft_criteria[$i]['condition'] = 'and';
				++$j;
				++$columnindex;
			}
			++$i;
		}

		if ($this->cvadvFilterOr) {
			foreach ($this->cvadvFilterOr as $cvadv) {
				$cvadv['columnindex'] = $columnindex;
				$criteria = $cv->getAdvftCriteria($cvadv);
				$advft_criteria[$i]['columns'][$j] = $criteria;
				$advft_criteria[$i]['condition'] = null;
				++$j;
				++$columnindex;
			}
			++$i;
		}
		return [$i, $j, $advft_criteria];
	}
}
