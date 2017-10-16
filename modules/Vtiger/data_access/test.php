<?php
/*
  Return Description
  ------------------------
  Info type: error, info, success
  Info title: optional
  Info text: mandatory
  Type: 0 - notify
  Type: 1 - show quick create mondal
 */

Class DataAccess_test
{

	public $config = true;

	public function process($ModuleName, $ID, $record_form, $config)
	{
		return ['save_record' => false, 'type' => 0, 'info' => ['title' => '111', 'text' => '22', 'type' => 'info']];
	}

	public function getConfig($id, $module, $baseModule)
	{
		return ['tpl' => 'test', 'type' => 0];
	}
}
