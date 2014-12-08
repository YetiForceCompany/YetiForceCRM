<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Class for managing HTTP sessions
 *
 * Provides access to session-state values as well as session-level
 * settings and lifetime management methods.
 * Based on the standart PHP session handling mechanism
 * it provides for you more advanced features such as
 * database container, idle and expire timeouts, etc.
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
 * @author    David Costa <gurugeek@php.net>
 * @author    Michael Metz <pear.metz@speedpartner.de>
 * @author    Stefan Neufeind <pear.neufeind@speedpartner.de>
 * @author    Torsten Roehr <torsten.roehr@gmx.de>
 * @copyright 1997-2005 The PHP Group
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   CVS: $Id: Session.php,v 1.15 2007/07/14 12:11:54 troehr Exp $
 * @link      http://pear.php.net/package/HTTP_Session
 * @since     File available since Release 0.4.0
 */

// @const HTTP_SESSION_STARTED - The session was started with the current request
define("HTTP_SESSION_STARTED", 1);
// @const HTTP_SESSION_STARTED - No new session was started with the current request
define("HTTP_SESSION_CONTINUED", 2);

/**
 * Class for managing HTTP sessions
 *
 * Provides access to session-state values as well as session-level
 * settings and lifetime management methods.
 * Based on the standart PHP session handling mechanism
 * it provides for you more advanced features such as
 * database container, idle and expire timeouts, etc.
 *
 * Example 1:
 *
 * <code>
 * // Setting some options and detecting of a new session
 * HTTP_Session::setCookieless(false);
 * HTTP_Session::start('MySessionID');
 * HTTP_Session::set('variable', 'Tet string');
 * if (HTTP_Session::isNew()) {
 *     echo('new session was created with the current request');
 *     $visitors++; // Increase visitors count
 * }
 *
 * //HTTP_Session::regenerateId();
 * </code>
 *
 * Example 2:
 *
 * <code>
 * // Using database container
 * HTTP_Session::setContainer('DB');
 * HTTP_Session::start();
 * </code>
 *
 * Example 3:
 *
 * <code>
 * // Setting timeouts
 * HTTP_Session::start();
 * HTTP_Session::setExpire(time() + 60 * 60); // expires in one hour
 * HTTP_Session::setIdle(time()+ 10 * 60);    // idles in ten minutes
 * if (HTTP_Session::isExpired()) {
 *     // expired
 *     echo('Your session is expired!');
 *     HTTP_Session::destroy();
 * }
 * if (HTTP_Session::isIdle()) {
 *     // idle
 *     echo('You've been idle for too long!');
 *     HTTP_Session::destroy();
 * }
 * HTTP_Session::updateIdle();
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
class HTTP_Session
{
    /**
     * Sets user-defined session storage functions
     *
     * Sets the user-defined session storage functions which are used
     * for storing and retrieving data associated with a session.
     * This is most useful when a storage method other than
     * those supplied by PHP sessions is preferred.
     * i.e. Storing the session data in a local database.
     *
     * @param string $container         Container name
     * @param array  $container_options Container options
     *
     * @static
     * @access public
     * @return void
     * @see    session_set_save_handler()
     */
    function setContainer($container, $container_options = null)
    {
        $container_class     = 'HTTP_Session_Container_' . $container;
        $container_classfile = 'HTTP/Session/Container/' . $container . '.php';

        include_once $container_classfile;
        $container = new $container_class($container_options);

        $container->set();
    }

    /**
     * Initializes session data
     *
     * Creates a session (or resumes the current one
     * based on the session id being passed
     * via a GET variable or a cookie).
     * You can provide your own name and/or id for a session.
     *
     * @param string $name string Name of a session, default is 'SessionID'
     * @param string $id   string Id of a session which will be used
     *                            only when the session is new
     *
     * @static
     * @access public
     * @return void
     * @see    session_name()
     * @see    session_id()
     * @see    session_start()
     */
    function start($name = 'SessionID', $id = null)
    {
        HTTP_Session::name($name);
        if ($id) {
            HTTP_Session::id($id);
        } elseif (is_null(HTTP_Session::detectID())) {
            HTTP_Session::id($id ? $id : uniqid(dechex(rand())));
        }
        session_start();
        if (!isset($_SESSION['__HTTP_Session_Info'])) {
            $_SESSION['__HTTP_Session_Info'] = HTTP_SESSION_STARTED;
        } else {
            $_SESSION['__HTTP_Session_Info'] = HTTP_SESSION_CONTINUED;
        }
    }

    /**
     * Writes session data and ends session
     *
     * Session data is usually stored after your script
     * terminated without the need to call HTTP_Session::stop(),
     * but as session data is locked to prevent concurrent
     * writes only one script may operate on a session at any time.
     * When using framesets together with sessions you will
     * experience the frames loading one by one due to this
     * locking. You can reduce the time needed to load all the
     * frames by ending the session as soon as all changes
     * to session variables are done.
     *
     * @static
     * @access public
     * @return void
     * @see    session_write_close()
     */
    function pause()
    {
        session_write_close();
    }

    /**
     * Frees all session variables and destroys all data
     * registered to a session
     *
     * This method resets the $_SESSION variable and
     * destroys all of the data associated
     * with the current session in its storage (file or DB).
     * It forces new session to be started after this method
     * is called. It does not unset the session cookie.
     *
     * @static
     * @access public
     * @return void
     * @see    session_unset()
     * @see    session_destroy()
     */
    function destroy()
    {
        session_unset();
        session_destroy();

        // set session handlers again to avoid fatal error in case
        // HTTP_Session::start() will be called afterwards
        if (isset($GLOBALS['HTTP_Session_Container']) &&
            is_a($GLOBALS['HTTP_Session_Container'], 'HTTP_Session_Container')) {
            $GLOBALS['HTTP_Session_Container']->set();
        }
    }

    /**
     * Calls session_regenerate_id() if available
     *
     * @param bool $deleteOldSessionData Whether to delete data of old session
     *
     * @static
     * @access public
     * @return bool
     */
    function regenerateId($deleteOldSessionData = false)
    {
        if (function_exists('session_regenerate_id')) {
            return session_regenerate_id($deleteOldSessionData);

            // emulate session_regenerate_id()
        } else {

            do {
                $newId = uniqid(dechex(rand()));
            } while ($newId === session_id());

            if ($deleteOldSessionData) {
                session_unset();
            }

            session_id($newId);

            return true;
        }
    }

    /**
     * This function copies session data of specified id to specified table
     *
     * @param string $targetTable Table to replicate data to
     * @param string $id          ID of the session
     *
     * @static
     * @access public
     * @return bool
     */
    function replicate($targetTable, $id = null)
    {
        return $GLOBALS['HTTP_Session_Container']->replicate($targetTable, $id);
    }

    /**
     * Free all session variables
     *
     * @todo   TODO Save expire and idle timestamps?
     * @static
     * @access public
     * @return void
     */
    function clear()
    {
        $info = $_SESSION['__HTTP_Session_Info'];
        session_unset();
        $_SESSION['__HTTP_Session_Info'] = $info;
    }

    /**
     * Tries to find any session id in $_GET, $_POST or $_COOKIE
     *
     * @static
     * @access private
     * @return string Session ID (if exists) or null
     */
    function detectID()
    {
        if (HTTP_Session::useCookies()) {
            if (isset($_COOKIE[HTTP_Session::name()])) {
                return $_COOKIE[HTTP_Session::name()];
            }
        } else {
            if (isset($_GET[HTTP_Session::name()])) {
                return $_GET[HTTP_Session::name()];
            }
            if (isset($_POST[HTTP_Session::name()])) {
                return $_POST[HTTP_Session::name()];
            }
        }
        return null;
    }

    /**
     * Sets new name of a session
     *
     * @param string $name New name of a session
     *
     * @static
     * @access public
     * @return string Previous name of a session
     * @see    session_name()
     */
    function name($name = null)
    {
        return isset($name) ? session_name($name) : session_name();
    }

    /**
     * Sets new ID of a session
     *
     * @param string $id New ID of a session
     *
     * @static
     * @access public
     * @return string Previous ID of a session
     * @see    session_id()
     */
    function id($id = null)
    {
        return isset($id) ? session_id($id) : session_id();
    }

    /**
     * Sets the maximum expire time
     *
     * @param integer $time Time in seconds
     * @param bool    $add  Add time to current expire time or not
     *
     * @static
     * @access public
     * @return void
     */
    function setExpire($time, $add = false)
    {
        if ($add) {
            if (!isset($_SESSION['__HTTP_Session_Expire_TS'])) {
                $_SESSION['__HTTP_Session_Expire_TS'] = time() + $time;
            }

            // update session.gc_maxlifetime
            $currentGcMaxLifetime = HTTP_Session::setGcMaxLifetime(null);
            HTTP_Session::setGcMaxLifetime($currentGcMaxLifetime + $time);

        } elseif (!isset($_SESSION['__HTTP_Session_Expire_TS'])) {
            $_SESSION['__HTTP_Session_Expire_TS'] = $time;
        }
    }

    /**
     * Sets the maximum idle time
     *
     * Sets the time-out period allowed
     * between requests before the session-state
     * provider terminates the session.
     *
     * @param int  $time Time in seconds
     * @param bool $add  Add time to current maximum idle time or not
     *
     * @static
     * @access public
     * @return void
     */
    function setIdle($time, $add = false)
    {
        if ($add) {
            $_SESSION['__HTTP_Session_Idle'] = $time;
        } else {
            // substract time again because it doesn't make any sense to provide
            // the idle time as a timestamp
            // keep $add functionality to provide BC
            $_SESSION['__HTTP_Session_Idle'] = $time - time();
        }
    }

    /**
     * Returns the time up to the session is valid
     *
     * @static
     * @access public
     * @return integer Time when the session idles
     */
    function sessionValidThru()
    {
        if (!isset($_SESSION['__HTTP_Session_Idle_TS']) ||
            !isset($_SESSION['__HTTP_Session_Idle'])) {
            return 0;
        } else {
            return $_SESSION['__HTTP_Session_Idle_TS'] +
                   $_SESSION['__HTTP_Session_Idle'];
        }
    }

    /**
     * Check if session is expired
     *
     * @static
     * @access public
     * @return bool
     */
    function isExpired()
    {
        if (isset($_SESSION['__HTTP_Session_Expire_TS']) &&
            $_SESSION['__HTTP_Session_Expire_TS'] < time()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if session is idle
     *
     * @static
     * @access public
     * @return bool
     */
    function isIdle()
    {
        if (isset($_SESSION['__HTTP_Session_Idle_TS']) &&
            (($_SESSION['__HTTP_Session_Idle_TS'] +
              $_SESSION['__HTTP_Session_Idle']) < time())) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Updates the idletime
     *
     * @static
     * @access public
     * @return void
     */
    function updateIdle()
    {
        $_SESSION['__HTTP_Session_Idle_TS'] = time();
    }

    /**
     * If optional parameter is specified it indicates
     * whether the module will use cookies to store
     * the session id on the client side
     *
     * It returns the previous value of this property
     *
     * @param bool $useCookies If specified it will replace the previous value
     *                         of this property
     *
     * @static
     * @access public
     *
     * @return bool The previous value of the property
     */
    function useCookies($useCookies = null)
    {
        $return = ini_get('session.use_cookies') ? true : false;
        if (isset($useCookies)) {
            ini_set('session.use_cookies', $useCookies ? 1 : 0);
        }
        return $return;
    }

    /**
     * Gets a value indicating whether the session
     * was created with the current request
     *
     * You MUST call this method only after you have started
     * the session with the HTTP_Session::start() method.
     *
     * @static
     * @access public
     * @return bool   True if the session was created
     *                with the current request, false otherwise
     */
    function isNew()
    {
        // The best way to check if a session is new is to check
        // for existence of a session data storage
        // with the current session id, but this is impossible
        // with the default PHP module wich is 'files'.
        // So we need to emulate it.
        return !isset($_SESSION['__HTTP_Session_Info']) ||
            $_SESSION['__HTTP_Session_Info'] == HTTP_SESSION_STARTED;
    }

    /**
     * Register variable with the current session
     *
     * @param string $name Name of a global variable
     *
     * @deprecated Use set()/setRef() instead
     *
     * @static
     * @access public
     * @return bool
     * @see    session_register()
     */
    function register($name)
    {
        return session_register($name);
    }

    /**
     * Unregister a variable from the current session
     *
     * @param string $name Name of a global variable
     *
     * @deprecated Use get()/getRef() instead
     *
     * @static
     * @access public
     * @return bool
     * @see    session_unregister()
     */
    function unregister($name)
    {
        return session_unregister($name);
    }

    /**
     * Checks if a session variable is registered
     *
     * @param string $name Variable name
     *
     * @deprecated Use is_set() instead
     *
     * @static
     * @access public
     * @return bool
     */
    function registered($name)
    {
        return session_is_registered($name);
    }

    /**
     * Returns session variable
     *
     * @param string $name    Name of a variable
     * @param mixed  $default Default value of a variable if not set
     *
     * @static
     * @access public
     * @return mixed  Value of a variable
     */
    function get($name, $default = null)
    {
        if (!isset($_SESSION[$name]) && isset($default)) {
            $_SESSION[$name] = $default;
        }
        $return = (isset($_SESSION[$name])) ? $_SESSION[$name] : null;
        return $return;
    }

    /**
     * Returns session variable by reference
     *
     * @param string $name Name of a variable
     *
     * @static
     * @access public
     * @return mixed  Value of a variable
     */
    function &getRef($name)
    {
        if (isset($_SESSION[$name])) {
            $return =& $_SESSION[$name];
        } else {
            $return = null;
        }

        return $return;
    }

    /**
     * Sets session variable
     *
     * @param string $name  Name of a variable
     * @param mixed  $value Value of a variable
     *
     * @static
     * @access public
     * @return mixed  Old value of a variable
     */
    function set($name, $value)
    {
        $return = (isset($_SESSION[$name])) ? $_SESSION[$name] : null;
        if (null === $value) {
            unset($_SESSION[$name]);
        } else {
            $_SESSION[$name] = $value;
        }
        return $return;
    }

    /**
     * Sets session variable by reference
     *
     * @param string $name  Name of a variable
     * @param mixed  $value Value of a variable
     *
     * @static
     * @access public
     * @return mixed  Old value of a variable
     */
    function setRef($name, &$value)
    {
        $return = (isset($_SESSION[$name])) ? $_SESSION[$name] : null;

        $_SESSION[$name] =& $value;

        return $return;
    }

    /**
     * Checks if a session variable is set
     *
     * @param string $name Variable name
     *
     * @static
     * @access public
     * @return bool
     */
    function is_set($name)
    {
        return isset($_SESSION[$name]);
    }

    /**
     * Returns local variable of a script
     *
     * Two scripts can have local variables with the same names
     *
     * @param string $name    Name of a variable
     * @param mixed  $default Default value of a variable if not set
     *
     * @static
     * @access public
     * @return mixed  Value of a local variable
     */
    function &getLocal($name, $default = null)
    {
        $local = md5(HTTP_Session::localName());
        if (!isset($_SESSION[$local]) || !is_array($_SESSION[$local])) {
            $_SESSION[$local] = array();
        }
        if (!isset($_SESSION[$local][$name]) && isset($default)) {
            $_SESSION[$local][$name] = $default;
        }
        return $_SESSION[$local][$name];
    }

    /**
     * Sets local variable of a script.
     * Two scripts can have local variables with the same names.
     *
     * @param string $name  Name of a local variable
     * @param mixed  $value Value of a local variable
     *
     * @static
     * @access public
     * @return mixed  Old value of a local variable
     */
    function setLocal($name, $value)
    {
        $local = md5(HTTP_Session::localName());
        if (!isset($_SESSION[$local]) || !is_array($_SESSION[$local])) {
            $_SESSION[$local] = array();
        }
        $return = (isset($_SESSION[$local][$name])) ? $_SESSION[$local][$name]
                                                    : null;

        if (null === $value) {
            unset($_SESSION[$local][$name]);
        } else {
            $_SESSION[$local][$name] = $value;
        }
        return $return;
    }

    /**
     * Sets new local name
     *
     * @param string $name New local name
     *
     * @static
     * @access public
     * @return string Previous local name
     */
    function localName($name = null)
    {
        $return = (isset($GLOBALS['__HTTP_Session_Localname'])) ? $GLOBALS['__HTTP_Session_Localname']
                                                                : null;

        if (!empty($name)) {
            $GLOBALS['__HTTP_Session_Localname'] = $name;
        }
        return $return;
    }

    /**
     * Initialize
     *
     * @static
     * @access private
     * @return void
     */
    function _init()
    {
        // Disable auto-start of a sesion
        ini_set('session.auto_start', 0);

        // Set local name equal to the current script name
        HTTP_Session::localName($_SERVER['PHP_SELF']);
    }

    /**
     * If optional parameter is specified it indicates
     * whether the session id will automatically be appended to
     * all links
     *
     * It returns the previous value of this property
     *
     * @param bool $useTransSID If specified it will replace the previous value
     *                          of this property
     *
     * @static
     * @access public
     * @return bool   The previous value of the property
     */
    function useTransSID($useTransSID = null)
    {
        $return = ini_get('session.use_trans_sid') ? true : false;
        if (isset($useTransSID)) {
            ini_set('session.use_trans_sid', $useTransSID ? 1 : 0);
        }
        return $return;
    }

    /**
     * If optional parameter is specified it determines the number of seconds
     * after which session data will be seen as 'garbage' and cleaned up
     *
     * It returns the previous value of this property
     *
     * @param bool $gcMaxLifetime If specified it will replace the previous value
     *                            of this property
     *
     * @static
     * @access public
     * @return bool   The previous value of the property
     */
    function setGcMaxLifetime($gcMaxLifetime = null)
    {
        $return = ini_get('session.gc_maxlifetime');
        if (isset($gcMaxLifetime) && is_int($gcMaxLifetime) && $gcMaxLifetime >= 1) {
            ini_set('session.gc_maxlifetime', $gcMaxLifetime);
        }
        return $return;
    }

    /**
     * If optional parameter is specified it determines the
     * probability that the gc (garbage collection) routine is started
     * and session data is cleaned up
     *
     * It returns the previous value of this property
     *
     * @param bool $gcProbability If specified it will replace the previous value
     *                            of this property
     *
     * @static
     * @access public
     * @return bool   The previous value of the property
     */
    function setGcProbability($gcProbability = null)
    {
        $return = ini_get('session.gc_probability');
        if (isset($gcProbability)  &&
            is_int($gcProbability) &&
            $gcProbability >= 1    &&
            $gcProbability <= 100) {
            ini_set('session.gc_probability', $gcProbability);
        }
        return $return;
    }
}

HTTP_Session::_init();
?>