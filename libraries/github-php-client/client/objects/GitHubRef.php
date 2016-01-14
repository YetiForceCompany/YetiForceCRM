<?php

require_once(__DIR__ . '/../GitHubObject.php');
require_once(__DIR__ . '/GitHubRefObject.php');
	

class GitHubRef extends GitHubObject
{
	/* (non-PHPdoc)
	 * @see GitHubObject::getAttributes()
	 */
	protected function getAttributes()
	{
		return array_merge(parent::getAttributes(), array(
			'ref' => 'string',
			'url' => 'string',
			'object' => 'GitHubRefObject',
		));
	}
	
	/**
	 * @var string
	 */
	protected $ref;

	/**
	 * @var string
	 */
	protected $url;

	/**
	 * @var GitHubRefObject
	 */
	protected $object;

	/**
	 * @return string
	 */
	public function getRef()
	{
		return $this->ref;
	}

	/**
	 * @return string
	 */
	public function getUrl()
	{
		return $this->url;
	}

	/**
	 * @return GitHubRefObject
	 */
	public function getObject()
	{
		return $this->object;
	}

}

