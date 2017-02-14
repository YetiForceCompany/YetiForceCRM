<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

/**
 * Vtiger Link Model Class
 */
class Vtiger_Link_Model extends vtlib\Link
{

	// Class variable to store the child links
	protected $childlinks = [];

	/**
	 * Function to get the value of a given property
	 * @param string $propertyName
	 * @return <Object>
	 * @throws Exception
	 */
	public function get($propertyName)
	{
		if (property_exists($this, $propertyName)) {
			return $this->$propertyName;
		}
	}

	/**
	 * Function to set the value of a given property
	 * @param string $propertyName
	 * @param <Object> $propertyValue
	 * @return Vtiger_Link_Model instance
	 */
	public function set($propertyName, $propertyValue)
	{
		$this->$propertyName = $propertyValue;
		return $this;
	}

	/**
	 * Function to check whether link is active
	 * @return boolean
	 */
	public function isActive()
	{
		return isset($this->active) ? $this->active : true;
	}

	/**
	 * Function to get the link url
	 * @return string
	 */
	public function getUrl()
	{
		return $this->convertToNativeLink();
	}

	/**
	 * Function to get the link label
	 * @return string
	 */
	public function getLabel()
	{
		return $this->linklabel;
	}

	/**
	 * Function to get the link type
	 * @return string
	 */
	public function getType()
	{
		return $this->linktype;
	}

	/**
	 * Function to get the link icon name
	 * @return string
	 */
	public function getIcon()
	{
		return $this->linkicon;
	}

	/**
	 * Function to get the link glyphicon name
	 * @return string
	 */
	public function getGlyphiconIcon()
	{
		return $this->glyphicon;
	}

	/**
	 * Function to check whether link has icon or not
	 * @return boolean true/false
	 */
	public function isIconExists()
	{
		$linkIcon = $this->getIcon();
		if (empty($linkIcon)) {
			return false;
		}
		return true;
	}

	/**
	 * Function to retrieve the icon path for the link icon
	 * @return <String/Boolean> - returns image path if icon exits
	 *                              else returns false;
	 */
	public function getIconPath()
	{
		if (!$this->isIconExists()) {
			return false;
		}
		return Vtiger_Theme::getImagePath($this->getIcon());
	}

	/**
	 * Function to get the Class Name
	 * @return <class name>
	 */
	public function getClassName()
	{
		return $this->get('linkclass');
	}

	/**
	 * Function to get the grup Class Name
	 * @return <class name>
	 */
	public function getGrupClassName()
	{
		return $this->get('linkgrupclass');
	}

	/**
	 * Function to get the link id
	 * @return <Number>
	 */
	public function getId()
	{
		return $this->linkid;
	}

	/**
	 * Function to Add link to the child link list
	 * @param Vtiger_Link_Model $link - link model
	 * @result Vtiger_Link_Model - current Instance;
	 */
	public function addChildLink(Vtiger_Link_Model $link)
	{
		$this->childlinks[] = $link;
		return $this;
	}

	public function setChildLink($links)
	{
		$this->childlinks = $links;
	}

	/**
	 * Function to get all the child links
	 * @result <array> - list of Vtiger_Link_Model instances
	 */
	public function getChildLinks()
	{
		//See if indexing is need depending only user selection
		return $this->childlinks;
	}

	/**
	 * Function to check whether the link model has any child links
	 * @return boolean true/false
	 */
	public function hasChild()
	{
		(count($this->childlinks) > 0) ? true : false;
	}

	public function isPageLoadLink()
	{
		$url = $this->get('linkurl');
		if (strpos($url, 'index') === 0) {
			return true;
		}
		return false;
	}

	/**
	 * Convert to native link
	 * @return string
	 */
	public function convertToNativeLink()
	{
		$url = $this->get('linkurl');
		if (empty($url)) {
			return $url;
		}
		//Check if the link is not javascript
		if (!$this->isPageLoadLink()) {
			//To convert single quotes and double quotes
			$url = Vtiger_Util_Helper::toSafeHTML($url);
			return $url;
		}
		$module = $parent = false;
		$sourceModule = false;
		$sourceRecord = false;
		$parametersParts = explode('&', $url);
		foreach ($parametersParts as $index => $keyValue) {
			if (strpos($keyValue, '=') === false) {
				continue;
			}
			$urlParts = explode('=', $keyValue);
			$key = $urlParts[0];
			$value = $urlParts[1];

			if (strcmp($key, 'module') === 0 || strcmp($key, 'index.php?module') === 0) {
				$module = $value;
			}

			if (strcmp($key, 'action') === 0) {
				if (strpos($value, 'View')) {
					$value = str_replace('View', '', $value);
					$key = 'view';
				}
			}
			if (strcmp($key, 'return_module') === 0) {
				$key = 'sourceModule';
				//Indicating that it is an relation operation
				$parametersParts[] = 'relationOperation=true';
			}
			if (strcmp($key, 'return_id') === 0) {
				$key = 'sourceRecord';
			}

			if (strcmp($key, 'sourceRecord') === 0) {
				$sourceRecord = $value;
			}

			if (strcmp($key, 'sourceModule') === 0) {
				$sourceModule = $value;
			}
			if (strcmp($key, 'parent') === 0) {
				$parent = $value;
			}
			$newUrlParts = [];
			array_push($newUrlParts, $key);
			if (!empty($value)) {
				array_push($newUrlParts, $value);
			}
			$parametersParts[$index] = implode('=', $newUrlParts);
		}

		//to append the reference field in one to many relation
		if (!empty($module) && !empty($sourceModule) && !empty($sourceRecord) && empty($parent)) {
			$sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
			$relatedModuleModel = Vtiger_Module_Model::getInstance($module);
			$relationModel = Vtiger_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);
			if ($relationModel && $relationModel->isDirectRelation()) {
				$fieldList = $relatedModuleModel->getFields();
				foreach ($fieldList as $fieldName => $fieldModel) {
					if ($fieldModel->isReferenceField()) {
						$referenceList = $fieldModel->getReferenceList();
						if (in_array($sourceModuleModel->get('name'), $referenceList)) {
							$parametersParts[] = $fieldModel->get('name') . '=' . $sourceRecord;
						}
					}
				}
			}
		}

		if (!empty($module)) {
			$this->relatedModuleName = $parent ? "$parent:$module" : $module;
		}

		$url = implode('&', $parametersParts);
		//To convert single quotes and double quotes
		$url = Vtiger_Util_Helper::toSafeHTML($url);
		return $url;
	}

	/**
	 * Function to get the instance of Vtiger Link Model from the given array of key-value mapping
	 * @param array $valueMap
	 * @return Vtiger_Link_Model instance
	 */
	public static function getInstanceFromValues($valueMap)
	{
		$linkModel = new self();
		$linkModel->initialize($valueMap);

		// To set other properties for Link Model
		foreach ($valueMap as $property => $value) {
			if (!isset($linkModel->$property)) {
				$linkModel->$property = $value;
			}
		}

		return $linkModel;
	}

	/**
	 * Function to get the instance of Vtiger Link Model from a given vtlib\Link object
	 * @param vtlib\Link $linkObj
	 * @return Vtiger_Link_Model instance
	 */
	public static function getInstanceFromLinkObject(vtlib\Link $linkObj)
	{
		$objectProperties = get_object_vars($linkObj);
		$linkModel = new self();

		if (!empty($objectProperties['params'])) {
			$params = \App\Json::decode($objectProperties['params']);
			if (!empty($params)) {
				foreach ($params as $properName => $propertyValue) {
					$linkModel->$properName = $propertyValue;
				}
			}
			unset($objectProperties['params']);
		}
		foreach ($objectProperties as $properName => $propertyValue) {
			$linkModel->$properName = $propertyValue;
		}
		// added support for multilayout
		if (strpos($linkModel->linkurl, '_layoutName_') !== false) {
			$filePath1 = str_replace('_layoutName_', Yeti_Layout::getActiveLayout(), $linkModel->linkurl);
			$filePath2 = str_replace('_layoutName_', Yeti_Layout::getActiveLayout(), $linkModel->linkurl);
			if (is_file(ROOT_DIRECTORY . DIRECTORY_SEPARATOR . $filePath1)) {
				$linkModel->linkurl = $filePath1;
			} else if (is_file(ROOT_DIRECTORY . DIRECTORY_SEPARATOR . $filePath2)) {
				$linkModel->linkurl = $filePath2;
			}
		}
		return $linkModel;
	}

	/**
	 * Function to get all the Vtiger Link Models for a module of the given list of link types
	 * @param <Number> $tabid
	 * @param <Array> $type
	 * @param <Array> $parameters
	 * @return <Array> - List of Vtiger_Link_Model instances
	 */
	public static function getAllByType($tabid, $type = false, $parameters = false)
	{
		$links = Vtiger_Cache::get('links-' . $tabid, $type);
		if (!$links) {
			$links = parent::getAllByType($tabid, $type, $parameters);
			Vtiger_Cache::set('links-' . $tabid, $type, $links);
		}

		$linkModels = [];
		foreach ($links as $linkType => $linkObjects) {
			foreach ($linkObjects as $linkObject) {
				$queryParams = vtlib\Functions::getQueryParams($linkObject->linkurl);
				if (!(isset($queryParams['module']) && !Users_Privileges_Model::isPermitted($queryParams['module']))) {
					$linkModels[$linkType][] = self::getInstanceFromLinkObject($linkObject);
				}
			}
		}

		if (!is_array($type)) {
			$type = array($type);
		}

		$diffTypes = array_diff($type, array_keys($linkModels));
		foreach ($diffTypes as $linkType) {
			$linkModels[$linkType] = [];
		}

		return $linkModels;
	}

	/**
	 * Function to get the relatedModuleName
	 * @return string
	 */
	public function getRelatedModuleName($defaultModuleName = false)
	{
		$relatedModuleName = $defaultModuleName;
		if (empty($this->relatedModuleName)) {
			$queryParams = vtlib\Functions::getQueryParams($this->get('linkurl'));
			if (isset($queryParams['module'])) {
				$this->relatedModuleName = $relatedModuleName = $queryParams['parent'] ? $queryParams['parent'] . ':' . $queryParams['module'] : $queryParams['module'];
			}
		} else {
			$relatedModuleName = $this->relatedModuleName;
		}
		return $relatedModuleName;
	}
}
