<?php

require_once 'Zend/Gdata/Feed.php';


class Zend_Gdata_Contacts_ContactListFeed extends Zend_Gdata_Feed
{

    protected $_entryClassName = 'Zend_Gdata_Contacts_ContactListEntry';

    /**
     * The classname for the feed.
     *
     * @var string
     */
    protected $_feedClassName = 'Zend_Gdata_Contacts_ContactListFeed';

    /**
     * Create a new instance of a feed for a list of documents.
     *
     * @param DOMElement $element (optional) DOMElement from which this
     *          object should be constructed.
     */
    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Docs::$namespaces);
        parent::__construct($element);
    }

}
