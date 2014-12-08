<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Database container for session data
 *
 * PEAR::MDB database container
 *
 * PHP version 4
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category  HTTP
 * @package   HTTP_Session
 * @author    Alexander Radivanovich <info@wwwlab.net>
 * @author    David Costa <gurugeek@php.net>
 * @author    Michael Metz <pear.metz@speedpartner.de>
 * @author    Stefan Neufeind <pear.neufeind@speedpartner.de>
 * @author    Torsten Roehr <torsten.roehr@gmx.de>
 * @copyright 1997-2005 The PHP Group
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   CVS: $Id: MDB.php,v 1.5 2007/07/14 12:11:55 troehr Exp $
 * @link      http://pear.php.net/package/HTTP_Session
 * @since     File available since Release 0.5.0
 */

require_once 'HTTP/Session/Container.php';
require_once 'MDB.php';

/**
 * Database container for session data
 *
 * Create the following table to store session data
 * <code>
 * CREATE TABLE `sessiondata` (
 *     `id` CHAR(32) NOT NULL,
 *     `expiry` INT UNSIGNED NOT NULL DEFAULT 0,
 *     `data` TEXT NOT NULL,
 *     PRIMARY KEY (`id`)
 * );
 * </code>
 *
 * @category  HTTP
 * @package   HTTP_Session
 * @author    David Costa <gurugeek@php.net>
 * @author    Michael Metz <pear.metz@speedpartner.de>
 * @author    Stefan Neufeind <pear.neufeind@speedpartner.de>
 * @author    Torsten Roehr <torsten.roehr@gmx.de>
 * @copyright 1997-2005 The PHP Group
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/HTTP_Session
 * @since     Class available since Release 0.4.0
 */
class HTTP_Session_Container_MDB extends HTTP_Session_Container
{

    /**
     * MDB connection object
     *
     * @var object MDB
     * @access private
     */
    var $db = null;

    /**
     * Session data cache id
     *
     * @var mixed
     * @access private
     */
    var $crc = false;

    /**
     * Constructor method
     *
     * $options is an array with the options.<br>
     * The options are:
     * <ul>
     * <li>'dsn' - The DSN string</li>
     * <li>'table' - Table with session data, default is 'sessiondata'</li>
     * <li>'autooptimize' - Boolean, 'true' to optimize
     * the table on garbage collection, default is 'false'.</li>
     * </ul>
     *
     * @param array $options Options
     *
     * @access public
     * @return object
     */
    function HTTP_Session_Container_MDB($options)
    {
        $this->_setDefaults();
        if (is_array($options)) {
            $this->_parseOptions($options);
        } else {
            $this->options['dsn'] = $options;
        }
    }

    /**
     * Connect to database by using the given DSN string
     *
     * @param string $dsn DSN string
     *
     * @access private
     * @return mixed  Object on error, otherwise bool
     */
    function _connect($dsn)
    {
        if (is_string($dsn) || is_array($dsn)) {
            $this->db = MDB::connect($dsn);
        } else if (is_object($dsn) && is_a($dsn, 'mdb_common')) {
            $this->db = $dsn;
        } else if (is_object($dsn) && MDB::isError($dsn)) {
            return new MDB_Error($dsn->code, PEAR_ERROR_DIE);
        } else {
            return new PEAR_Error("The given dsn was not valid in file " . __FILE__ 
                                  . " at line " . __LINE__,
                                  41,
                                  PEAR_ERROR_RETURN,
                                  null,
                                  null
                                  );

        }

        if (MDB::isError($this->db)) {
            return new MDB_Error($this->db->code, PEAR_ERROR_DIE);
        }

        return true;
    }

    /**
     * Set some default options
     *
     * @access private
     * @return void
     */
    function _setDefaults()
    {
        $this->options['dsn']          = null;
        $this->options['table']        = 'sessiondata';
        $this->options['autooptimize'] = false;
    }

    /**
     * Establish connection to a database
     *
     * @param string $save_path    Save path
     * @param string $session_name Session name
     *
     * @return bool
     */
    function open($save_path, $session_name)
    {
        if (MDB::isError($this->_connect($this->options['dsn']))) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Free resources
     *
     * @return bool
     */
    function close()
    {
        return true;
    }

    /**
     * Read session data
     *
     * @param string $id Session id
     *
     * @return mixed
     */
    function read($id)
    {
        $query = sprintf("SELECT data FROM %s WHERE id = %s AND expiry >= %d",
                         $this->options['table'],
                         $this->db->getTextValue(md5($id)),
                         time());
        $result = $this->db->getOne($query);
        if (MDB::isError($result)) {
            new MDB_Error($result->code, PEAR_ERROR_DIE);
            return false;
        }
        $this->crc = strlen($result) . crc32($result);
        return $result;
    }

    /**
     * Write session data
     *
     * @param string $id   Session id
     * @param mixed  $data Data
     *
     * @return bool
     */
    function write($id, $data)
    {
        if ((false !== $this->crc) && 
            ($this->crc === strlen($data) . crc32($data))) {
            // $_SESSION hasn't been touched, no need to update the blob column
            $query = sprintf("UPDATE %s SET expiry = %d WHERE id = %s",
                             $this->options['table'],
                             time() + ini_get('session.gc_maxlifetime'),
                             $this->db->getTextValue(md5($id)));
        } else {
            // Check if table row already exists
            $query = sprintf("SELECT COUNT(id) FROM %s WHERE id = %s",
                             $this->options['table'],
                             $this->db->getTextValue(md5($id)));
            $result = $this->db->getOne($query);
            if (MDB::isError($result)) {
                new MDB_Error($result->code, PEAR_ERROR_DIE);
                return false;
            }
            if (0 == intval($result)) {
                // Insert new row into table
                $query = sprintf("INSERT INTO %s (id, expiry, data) VALUES (%s, %d, %s)",
                                 $this->options['table'],
                                 $this->db->getTextValue(md5($id)),
                                 time() + ini_get('session.gc_maxlifetime'),
                                 $this->db->getTextValue($data));
            } else {
                // Update existing row
                $query = sprintf("UPDATE %s SET expiry = %d, data = %s WHERE id = %s",
                                 $this->options['table'],
                                 time() + ini_get('session.gc_maxlifetime'),
                                 $this->db->getTextValue($data),
                                 $this->db->getTextValue(md5($id)));
            }
        }
        $result = $this->db->query($query);
        if (MDB::isError($result)) {
            new MDB_Error($result->code, PEAR_ERROR_DIE);
            return false;
        }

        return true;
    }

    /**
     * Destroy session data
     *
     * @param string $id Session id
     *
     * @return bool
     */
    function destroy($id)
    {
        $query = sprintf("DELETE FROM %s WHERE id = %s",
                         $this->options['table'],
                         $this->db->getTextValue(md5($id)));
        $result = $this->db->query($query);
        if (MDB::isError($result)) {
            new MDB_Error($result->code, PEAR_ERROR_DIE);
            return false;
        }

        return true;
    }

    /**
     * Replicate session data to table specified in option 'replicateBeforeDestroy'
     *
     * @param string $targetTable Table to replicate to
     * @param string $id          Id of record to replicate
     *
     * @access private
     * @return bool
     */
    function replicate($targetTable, $id = null)
    {
        if (is_null($id)) {
            $id = HTTP_Session::id();
        }

        // Check if table row already exists
        $query = sprintf("SELECT COUNT(id) FROM %s WHERE id = %s",
                         $targetTable,
                         $this->db->getTextValue(md5($id)));
        $result = $this->db->getOne($query);
        if (MDB::isError($result)) {
            new MDB_Error($result->code, PEAR_ERROR_DIE);
            return false;
        }

        // Insert new row into dest table
        if (0 == intval($result)) {
            $query = sprintf("INSERT INTO %s SELECT * FROM %s WHERE id = %s",
                             $targetTable,
                             $this->options['table'],
                             $this->db->getTextValue(md5($id)));
        } else {
            // Update existing row
            $query = sprintf("UPDATE %s dst, %s src SET dst.expiry = src.expiry, dst.data = src.data WHERE dst.id = src.id AND src.id = %s",
                             $targetTable,
                             $this->options['table'],
                             $this->db->getTextValue(md5($id)));
        }

        $result = $this->db->query($query);
        if (MDB::isError($result)) {
            new MDB_Error($result->code, PEAR_ERROR_DIE);
            return false;
        }

        return true;
    }

    /**
     * Garbage collection
     *
     * @param int $maxlifetime Maximum lifetime
     *
     * @return bool
     */
    function gc($maxlifetime)
    {
        $query = sprintf("DELETE FROM %s WHERE expiry < %d",
                         $this->options['table'],
                         time());
        $result = $this->db->query($query);
        if (MDB::isError($result)) {
            new MDB_Error($result->code, PEAR_ERROR_DIE);
            return false;
        }
        if ($this->options['autooptimize']) {
            switch($this->db->phptype) {
            case 'mysql':
                $query = sprintf("OPTIMIZE TABLE %s", $this->options['table']);
                break;
            case 'pgsql':
                $query = sprintf("VACUUM %s", $this->options['table']);
                break;
            default:
                $query = null;
                break;
            }
            if (isset($query)) {
                $result = $this->db->query($query);
                if (MDB::isError($result)) {
                    new MDB_Error($result->code, PEAR_ERROR_DIE);
                    return false;
                }
            }
        }

        return true;
    }
}
?>