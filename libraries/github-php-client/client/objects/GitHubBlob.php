<?php

require_once(__DIR__ . '/../GitHubObject.php');

	

class GitHubBlob extends GitHubObject
{
	/* (non-PHPdoc)
	 * @see GitHubObject::getAttributes()
	 */
	protected function getAttributes()
	{
		return array_merge(parent::getAttributes(), array(
			'content' => 'string',
			'encoding' => 'string',
			'sha' => 'string',
			'size' => 'int',
		));
	}
	
	/**
	 * @var string
	 */
	protected $content;

	/**
	 * @var string
	 */
	protected $encoding;

	/**
	 * @var string
	 */
	protected $sha;

	/**
	 * @var int
	 */
	protected $size;

	/**
	 * @return string
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * @return string
	 */
	public function getEncoding()
	{
		return $this->encoding;
	}

	/**
	 * @return string
	 */
	public function getSha()
	{
		return $this->sha;
	}

	/**
	 * @return int
	 */
	public function getSize()
	{
		return $this->size;
	}

}

