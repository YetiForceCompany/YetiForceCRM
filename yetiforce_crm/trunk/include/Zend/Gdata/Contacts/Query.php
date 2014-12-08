<?php

/**
 * https://github.com/prasad83/Zend-Gdata-Contacts
 * @author prasad
 * 
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Contacts
 */

/**
 * Zend_Gdata_Query
 */
require_once('Zend/Gdata/Query.php');

class Zend_Gdata_Contacts_Query extends Zend_Gdata_Query {
    
    const CONTACTS_FEED_URI = 'https://www.google.com/m8/feeds/contacts';

    /**
     * The default URI used for feeds.
     */
    protected $_defaultFeedUri = self::CONTACTS_FEED_URI;
    protected $_user = 'default';
    protected $_projection = 'full';
    protected  $_contact;


    /**
     * @return string url
     */
    public function getQueryUrl()
    {
        if (isset($this->_url)) {
            $uri = $this->_url;
        } else {
            $uri = $this->_defaultFeedUri;
        }
        if ($this->getUser() != null) {
            $uri .= '/' . $this->getUser();
        }
        if ($this->getProjection() != null) {
            $uri .= '/' . $this->getProjection();
        }
        if ($this->getContact() != null) {
            $uri .= '/' . $this->getContact();
        }
        
        $uri .= $this->getQueryString();
        return $uri;
    }
    
        /**
     * @see $_projection
     * @param string $value
     * @return Zend_Gdata_Calendar_EventQuery Provides a fluent interface
     */
    public function setProjection($value)
    {
        $this->_projection = $value;
        return $this;
    }

    /**
     * @see $_user
     * @param string $value
     * @return Zend_Gdata_Calendar_EventQuery Provides a fluent interface
     */
    public function setUser($value)
    {
        $this->_user = $value;
        return $this;
    }
    
    public function setContact($value){
        $this->_contact = $value;
    }
    
    public function getContact(){
        echo $this->_contact;
        return $this->_contact;
    }

    /**
     * @see $_visibility
     * @param bool $value
     * @return Zend_Gdata_Calendar_EventQuery Provides a fluent interface
     */
    public function setVisibility($value)
    {
        $this->_visibility = $value;
        return $this;
    }
    
    
    /**
     * @see $_projection
     * @return string projection
     */
    public function getProjection()
    {
        return $this->_projection;
    }

    /**
     * @see $_user
     * @return string user
     */
    public function getUser()
    {
        return $this->_user;
    }

    /**
     * @see $_visibility
     * @return string visibility
     */
    public function getVisibility()
    {
        return $this->_visibility;
    }
    
    public function setShowDeleted($value){
        if ($value !== null) {
            $this->_params['showdeleted'] = $value;
        } else {
            unset($this->_params['showdeleted']);
        }
        return $this;
    }
     
    /**
     * @param string $value
     * @return Zend_Gdata_Calendar_EventQuery Provides a fluent interface
     */
    public function setOrderBy($value)
    {
        if ($value != null) {
            $this->_params['orderby'] = $value;
        } else {
            unset($this->_params['orderby']);
        }
        return $this;
    }
    
    
 
     /**
     * @return string sortorder
     */
    public function setSortOrder($value)
    {
        if ($value != null) {
            $this->_params['sortorder'] = $value;
        } else {
            unset($this->_params['sortorder']);
        }
        return $this;
    }
    
    
    /**
     * @return string orderby
     */
    public function getOrderBy()
    {
        if (array_key_exists('orderby', $this->_params)) {
            return $this->_params['orderby'];
        } else {
            return null;
        }
    }

    /**
     * @return string sortorder
     */
    public function getSortOrder()
    {
        if (array_key_exists('sortorder', $this->_params)) {
            return $this->_params['sortorder'];
        } else {
            return null;
        }
    }
}