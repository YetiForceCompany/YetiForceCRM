<?php
/**
 * log4php is a PHP port of the log4j java logging package.
 * 
 * <p>This framework is based on log4j (see {@link http://jakarta.apache.org/log4j log4j} for details).</p>
 * <p>Design, strategies and part of the methods documentation are developed by log4j team 
 * (Ceki Gülcü as log4j project founder and 
 * {@link http://jakarta.apache.org/log4j/docs/contributors.html contributors}).</p>
 *
 * <p>PHP port, extensions and modifications by VxR. All rights reserved.<br>
 * For more information, please see {@link http://www.vxr.it/log4php/}.</p>
 *
 * <p>This software is published under the terms of the LGPL License
 * a copy of which has been included with this distribution in the LICENSE file.</p>
 * 
 * @package log4php
 * @subpackage varia
 */

/**
 * @ignore 
 */
if (!defined('LOG4PHP_DIR')) define('LOG4PHP_DIR', dirname(__FILE__) . '/..');
 
/**
 */
require_once(LOG4PHP_DIR . '/spi/LoggerFilter.php');

/**
 * This filter drops all logging events. 
 * 
 * <p>You can add this filter to the end of a filter chain to
 * switch from the default "accept all unless instructed otherwise"
 * filtering behaviour to a "deny all unless instructed otherwise"
 * behaviour.</p>
 *
 * @author VxR <vxr@vxr.it>
 * @version $Revision: 1.4 $
 * @package log4php
 * @subpackage varia
 * @since 0.3
 */
class LoggerDenyAllFilter extends LoggerFilter {

  /**
   * Always returns the integer constant {@link LOG4PHP_LOGGER_FILTER_DENY}
   * regardless of the {@link LoggerLoggingEvent} parameter.
   * 
   * @param LoggerLoggingEvent $event The {@link LoggerLoggingEvent} to filter.
   * @return LOG4PHP_LOGGER_FILTER_DENY Always returns {@link LOG4PHP_LOGGER_FILTER_DENY}
   */
  function decide($event)
  {
    return LOG4PHP_LOGGER_FILTER_DENY;
  }
}
?>