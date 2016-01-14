<?php

class GitHubObject
{
	/**
	 * @param array $json
	 */
	public static function fromArray(array $json, $type)
	{
		$array = array();
		foreach($json as $jsonObject)
		{
			$object = new $type($jsonObject);
			if(method_exists($object, 'getId'))
			{
				$array[$object->getId()] = $object;
			}
			else
			{
				$array[] = $object;
			}
		}
			
		return $array;
	}
	
	/**
	 * @param stdClass $json
	 */
	public function __construct(stdClass $json)
	{
		$attributes = $this->getAttributes();
		
		foreach($attributes as $attributeName => $attributeType)
		{
			if(!isset($json->$attributeName))
				continue;
				
			switch ($attributeType)
			{
				case 'string':
					$this->$attributeName = $json->$attributeName;
					break;
					
				case 'int':
					$this->$attributeName = intval($json->$attributeName);
					break;
					
				case 'boolean':
					$this->$attributeName = (bool)$json->$attributeName;
					break;
					
				default:
					$matches = null;
					if(preg_match('/^array<([^>]+)>$/', $attributeType, $matches))
					{
						$attributeType = $matches[1];
						$array = array();
						if(is_array($json->$attributeName))
						{
							foreach($json->$attributeName as $value)
							{
								$array[] = new $attributeType($value);
							}
						}
						$this->$attributeName = $array;
					}
					else
					{
						if(!class_exists($attributeType))
							throw new GitHubClientException("Github type [$attributeType] not found", GitHubClientException::CLASS_NOT_FOUND);
							
						$this->$attributeName = new $attributeType($json->$attributeName);
					}
					break;
			}
		}
	}
	
	/**
	 * @return array
	 */
	protected function getAttributes()
	{
		return array();
	}
}
