<?php

/**
 * RSS for PHP - small and easy-to-use library for consuming an RSS Feed
 *
 * @copyright  Copyright (c) 2008 David Grudl
 * @license    New BSD License
 * @version    1.2
 */
class Feed
{
	/** @var int */
	public static $cacheExpire = '1 day';

	/** @var string */
	public static $cacheDir;

	/** @var SimpleXMLElement */
	protected $xml;


	/**
	 * Loads RSS or Atom feed.
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return Feed
	 * @throws FeedException
	 */
	public static function load($url, $user = NULL, $pass = NULL)
	{
		$xml = self::loadXml($url, $user, $pass);
		if ($xml->channel) {
			return self::fromRss($xml);
		} else {
			return self::fromAtom($xml);
		}
	}


	/**
	 * Loads RSS feed.
	 * @param  string  RSS feed URL
	 * @param  string  optional user name
	 * @param  string  optional password
	 * @return Feed
	 * @throws FeedException
	 */
	public static function loadRss($url, $user = NULL, $pass = NULL)
	{
		return self::fromRss(self::loadXml($url, $user, $pass));
	}


	/**
	 * Loads Atom feed.
	 * @param  string  Atom feed URL
	 * @param  string  optional user name
	 * @param  string  optional password
	 * @return Feed
	 * @throws FeedException
	 */
	public static function loadAtom($url, $user = NULL, $pass = NULL)
	{
		return self::fromAtom(self::loadXml($url, $user, $pass));
	}


	private static function fromRss(SimpleXMLElement $xml)
	{
		if (!$xml->channel) {
			throw new FeedException('Invalid feed.');
		}

		self::adjustNamespaces($xml);

		foreach ($xml->channel->item as $item) {
			// converts namespaces to dotted tags
			self::adjustNamespaces($item);

			// generate 'timestamp' tag
			if (isset($item->{'dc:date'})) {
				$item->timestamp = strtotime($item->{'dc:date'});
			} elseif (isset($item->pubDate)) {
				$item->timestamp = strtotime($item->pubDate);
			}
		}
		$feed = new self;
		$feed->xml = $xml->channel;
		return $feed;
	}


	private static function fromAtom(SimpleXMLElement $xml)
	{
		if (!in_array('http://www.w3.org/2005/Atom', $xml->getDocNamespaces(), TRUE)
			&& !in_array('http://purl.org/atom/ns#', $xml->getDocNamespaces(), TRUE)
		) {
			throw new FeedException('Invalid feed.');
		}

		// generate 'timestamp' tag
		foreach ($xml->entry as $entry) {
			$entry->timestamp = strtotime($entry->updated);
		}
		$feed = new self;
		$feed->xml = $xml;
		return $feed;
	}


	/**
	 * Returns property value. Do not call directly.
	 * @param  string  tag name
	 * @return SimpleXMLElement
	 */
	public function __get($name)
	{
		return $this->xml->{$name};
	}


	/**
	 * Sets value of a property. Do not call directly.
	 * @param  string  property name
	 * @param  mixed   property value
	 * @return void
	 */
	public function __set($name, $value)
	{
		throw new Exception("Cannot assign to a read-only property '$name'.");
	}


	/**
	 * Converts a SimpleXMLElement into an array.
	 * @param  SimpleXMLElement
	 * @return array
	 */
	public function toArray(SimpleXMLElement $xml = NULL)
	{
		if ($xml === NULL) {
			$xml = $this->xml;
		}

		if (!$xml->children()) {
			return (string) $xml;
		}

		$arr = array();
		foreach ($xml->children() as $tag => $child) {
			if (count($xml->$tag) === 1) {
				$arr[$tag] = $this->toArray($child);
			} else {
				$arr[$tag][] = $this->toArray($child);
			}
		}

		return $arr;
	}


	/**
	 * Load XML from cache or HTTP.
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return SimpleXMLElement
	 * @throws FeedException
	 */
	private static function loadXml($url, $user, $pass)
	{
		$e = self::$cacheExpire;
		$cacheFile = self::$cacheDir . '/feed.' . md5(serialize(func_get_args())) . '.xml';

		if (self::$cacheDir
			&& (time() - @filemtime($cacheFile) <= (is_string($e) ? strtotime($e) - time() : $e))
			&& $data = @file_get_contents($cacheFile)
		) {
			// ok
		} elseif ($data = trim(self::httpRequest($url, $user, $pass))) {
			if (self::$cacheDir) {
				file_put_contents($cacheFile, $data);
			}
		} elseif (self::$cacheDir && $data = @file_get_contents($cacheFile)) {
			// ok
		} else {
			throw new FeedException('Cannot load feed.');
		}

		return new SimpleXMLElement($data, LIBXML_NOWARNING | LIBXML_NOERROR);
	}


	/**
	 * Process HTTP request.
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return string|FALSE
	 * @throws FeedException
	 */
	private static function httpRequest($url, $user, $pass)
	{
		if (extension_loaded('curl')) {
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			if ($user !== NULL || $pass !== NULL) {
				curl_setopt($curl, CURLOPT_USERPWD, "$user:$pass");
			}
			curl_setopt($curl, CURLOPT_HEADER, FALSE);
			curl_setopt($curl, CURLOPT_TIMEOUT, 20);
			curl_setopt($curl, CURLOPT_ENCODING , '');
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE); // no echo, just return result
			if (!ini_get('open_basedir')) {
				curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE); // sometime is useful :)
			}
			$result = curl_exec($curl);
			return curl_errno($curl) === 0 && curl_getinfo($curl, CURLINFO_HTTP_CODE) === 200
				? $result
				: FALSE;

		} elseif ($user === NULL && $pass === NULL) {
			return file_get_contents($url);

		} else {
			throw new FeedException('PHP extension CURL is not loaded.');
		}
	}


	/**
	 * Generates better accessible namespaced tags.
	 * @param  SimpleXMLElement
	 * @return void
	 */
	private static function adjustNamespaces($el)
	{
		foreach ($el->getNamespaces(TRUE) as $prefix => $ns) {
			$children = $el->children($ns);
			foreach ($children as $tag => $content) {
				$el->{$prefix . ':' . $tag} = $content;
			}
		}
	}

}



/**
 * An exception generated by Feed.
 */
class FeedException extends Exception
{
}
