<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class VtigerWebserviceObject
{

	private $id;
	private $name;
	private $handlerPath;
	private $handlerClass;

	private function __construct($entityId, $entityName, $handler_path, $handler_class)
	{
		$this->id = $entityId;
		$this->name = $entityName;
		// END
		$this->handlerPath = $handler_path;
		$this->handlerClass = $handler_class;
	}

	// Cache variables to enable result re-use
	private static $_fromNameCache = [];

	static function fromName($adb = false, $entityName)
	{
		$rowData = false;
		// If the information not available in cache?
		if (empty(self::$_fromNameCache)) {
			$rows = (new \App\Db\Query())->from('vtiger_ws_entity')->all();
			foreach ($rows as &$row) {
				self::$_fromNameCache[$row['name']] = $row;
			}
		}
		if (isset(self::$_fromNameCache[$entityName])) {
			$rowData = self::$_fromNameCache[$entityName];
			return new VtigerWebserviceObject($rowData['id'], $rowData['name'], $rowData['handler_path'], $rowData['handler_class']);
		}
		throw new WebServiceException(WebServiceErrorCode::$ACCESSDENIED, 'Permission to perform the operation is denied for name: ' . $entityName);
	}

	// Cache variables to enable result re-use
	private static $_fromIdCache = [];

	static function fromId($adb, $entityId)
	{
		$rowData = false;

		// If the information not available in cache?
		if (!isset(self::$_fromIdCache[$entityId])) {
			$id = explode('x', $entityId);
			$query = (new \App\Db\Query())->from('vtiger_ws_entity')->where(['id' => $id[0]]);
			$result = $query->one();

			if ($result) {
				self::$_fromIdCache[$entityId] = $result;
			}
		}
		$rowData = self::$_fromIdCache[$entityId];

		if ($rowData) {
			return new VtigerWebserviceObject($rowData['id'], $rowData['name'], $rowData['handler_path'], $rowData['handler_class']);
		}

		throw new WebServiceException(WebServiceErrorCode::$ACCESSDENIED, "Permission to perform the operation is denied for id");
	}

	static function fromQuery($adb, $query)
	{
		$moduleRegex = "/[fF][rR][Oo][Mm]\s+([^\s;]+)/";
		$matches = [];
		$found = preg_match($moduleRegex, $query, $matches);
		if ($found === 1) {
			return VtigerWebserviceObject::fromName($adb, trim($matches[1]));
		}
		throw new WebServiceException(WebServiceErrorCode::$ACCESSDENIED, "Permission to perform the operation is denied for query");
	}

	public function getEntityName()
	{
		return $this->name;
	}

	public function getEntityId()
	{
		return $this->id;
	}

	public function getHandlerPath()
	{
		return $this->handlerPath;
	}

	public function getHandlerClass()
	{
		return $this->handlerClass;
	}
}
