<?php

/**
 * Save issue to github
 * @package YetiForce.Github
 * @license licenses/License.html
 * @author Krzysztof Gastołek <krzysztof.gastolek@wars.pl>
 */

class Vtiger_KnowledgeBase_Model extends Vtiger_Base_Model
{
	public static $baseTable = 'u_yf_knowledgebase';
	public static $baseIndex = 'knowledgebaseid';
	protected $recordCache = [];
	protected $recordId;
	protected $viewToPicklistValue = ['Detail' => 'PLL_DETAILVIEW', 'List' => 'PLL_LISTVIEW'];
	
	/**
	 * Function to get the id of the record
	 * @return <Number> - Record Id
	 */
	public function getId()
	{
		return $this->get('knowledgebaseid');
	}
	
	public function getRaw($key)
	{
		return parent::get($key);
	}
	
	public static function getInstanceById($recordId, $moduleName = 'Vtiger')
	{
		$knowledgeBase = Vtiger_Cache::get('KnowledgeBaseModel', $recordId);
		
		if ($knowledgeBase) {
			return $knowledgeBase;
		}
		
		$db = PearDatabase::getInstance();
		$query = 'SELECT * FROM `' . self::$baseTable . '` WHERE `' . self::$baseIndex . '` = ? LIMIT 1;';
		$result = $db->pquery($query, [$recordId]);
		
		if ($result->rowCount() == 0) {
			return false;
		}
		
		$data = $db->fetchByAssoc($result);
		
		if ($moduleName == 'Vtiger' && isset($data['module_name'])) {
			$moduleName = $data['module_name'];
		}

		$handlerClass = Vtiger_Loader::getComponentClassName('Model', 'KnowledgeBase', $moduleName);
		$knowledgeBase = new $handlerClass();
		$knowledgeBase->setData($data);
		Vtiger_Cache::set('KnowledgeBaseModel', $recordId, $knowledgeBase);
		
		return $knowledgeBase;
	}
}
