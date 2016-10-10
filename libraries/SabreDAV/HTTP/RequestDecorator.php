<?php

namespace Sabre\HTTP;

/**
 * Request Decorator
 *
 * This helper class allows you to easily create decorators for the Request
 * object.
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class RequestDecorator implements RequestInterface {

    use MessageDecoratorTrait;

    /**
     * Constructor.
     *
     * @param RequestInterface $inner
     */
    public function __construct(RequestInterface $inner) {

        $this->inner = $inner;

    }

    /**
     * Returns the current HTTP method
     *
     * @return string
     */
    public function getMethod() {

        return $this->inner->getMethod();

    }

    /**
     * Sets the HTTP method
     *
     * @param string $method
     * @return void
     */
    public function setMethod($method) {

        $this->inner->setMethod($method);

    }

    /**
     * Returns the request url.
     *
     * @return string
     */
    public function getUrl() {

        return $this->inner->getUrl();

    }

    /**
     * Sets the request url.
     *
     * @param string $url
     * @return void
     */
    public function setUrl($url) {

        $this->inner->setUrl($url);

    }

    /**
     * Returns the absolute url.
     *
     * @return string
     */
    public function getAbsoluteUrl() {

        return $this->inner->getAbsoluteUrl();

    }

    /**
     * Sets the absolute url.
     *
     * @param string $url
     * @return void
     */
    public function setAbsoluteUrl($url) {

        $this->inner->setAbsoluteUrl($url);

    }

    /**
     * Returns the current base url.
     *
     * @return string
     */
    public function getBaseUrl() {

        return $this->inner->getBaseUrl();

    }

    /**
     * Sets a base url.
     *
     * This url is used for relative path calculations.
     *
     * The base url should default to /
     *
     * @param string $url
     * @return void
     */
    public function setBaseUrl($url) {

        $this->inner->setBaseUrl($url);

    }

    /**
     * Returns the relative path.
     *
     * This is being calculated using the base url. This path will not start
     * with a slash, so it will always return something like
     * 'example/path.html'.
     *
     * If the full path is equal to the base url, this method will return an
     * empty string.
     *
     * This method will also urldecode the path, and if the url was incoded as
     * ISO-8859-1, it will convert it to UTF-8.
     *
     * If the path is outside of the base url, a LogicException will be thrown.
     *
     * @return string
     */
    public function getPath() {

        return $this->inner->getPath();

    }

    /**
     * Returns the list of query parameters.
     *
     * This is equivalent to PHP's $_GET superglobal.
     *
     * @return array
     */
    public function getQueryParameters() {

        return $this->inner->getQueryParameters();

    }

    /**
     * Returns the POST data.
     *
     * This is equivalent to PHP's $_POST superglobal.
     *
     * @return array
     */
    public function getPostData() {

        return $this->inner->getPostData();

    }

    /**
     * Sets the post data.
     *
     * This is equivalent to PHP's $_POST superglobal.
     *
     * This would not have been needed, if POST data was accessible as
     * php://input, but unfortunately we need to special case it.
     *
     * @param array $postData
     * @return void
     */
    public function setPostData(array $postData) {

        $this->inner->setPostData($postData);

    }


    /**
     * Returns an item from the _SERVER array.
     *
     * If the value does not exist in the array, null is returned.
     *
     * @param string $valueName
     * @return string|null
     */
    public function getRawServerValue($valueName) {

        return $this->inner->getRawServerValue($valueName);

    }

    /**
     * Sets the _SERVER array.
     *
     * @param array $data
     * @return void
     */
    public function setRawServerData(array $data) {

        $this->inner->setRawServerData($data);

    }

    /**
     * Serializes the request object as a string.
     *
     * This is useful for debugging purposes.
     *
     * @return string
     */
    public function __toString() {

        return $this->inner->__toString();

    }
}
