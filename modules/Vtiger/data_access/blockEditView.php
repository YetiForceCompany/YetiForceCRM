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

Class DataAccess_blockEditView
{

	public $config = false;

	public function process($ModuleName, $ID, $record_form, $config)
	{

		return true;
	}

	public function getConfig($id, $module, $baseModule)
	{
		return false;
	}
}
