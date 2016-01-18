<?php
/**
 * <tasks:replace> - read/write version
 *
 * PHP versions 4 and 5
 *
 * @category  pear
 * @package   PEAR
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 1997-2009 The Authors
 * @license   http://opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://pear.php.net/package/PEAR
 * @since     File available since Release 1.4.0a10
 */
/**
 * Base class
 */
require_once 'PEAR/Task/Replace.php';
/**
 * Abstracts the replace task xml.
 * @category   pear
 * @package    PEAR
 * @author     Greg Beaver <cellog@php.net>
 * @copyright  1997-2009 The Authors
 * @license    http://opensource.org/licenses/bsd-license.php New BSD License
 * @version    Release: 1.10.1
 * @link       http://pear.php.net/package/PEAR
 * @since      Class available since Release 1.4.0a10
 */
class PEAR_Task_Replace_rw extends PEAR_Task_Replace
{
    public function __construct(&$pkg, &$config, &$logger, $fileXml)
    {
        parent::__construct($config, $logger, PEAR_TASK_PACKAGE);
        $this->_contents = $fileXml;
        $this->_pkg = &$pkg;
        $this->_params = array();
    }

    public function validate()
    {
        return $this->validateXml($this->_pkg, $this->_params, $this->config, $this->_contents);
    }

    public function setInfo($from, $to, $type)
    {
        $this->_params = array('attribs' => array('from' => $from, 'to' => $to, 'type' => $type));
    }

    public function getName()
    {
        return 'replace';
    }

    public function getXml()
    {
        return $this->_params;
    }
}
