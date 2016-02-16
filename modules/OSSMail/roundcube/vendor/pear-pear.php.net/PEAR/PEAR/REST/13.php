<?php
/**
 * PEAR_REST_13
 *
 * PHP versions 4 and 5
 *
 * @category   pear
 * @package    PEAR
 * @author     Greg Beaver <cellog@php.net>
 * @copyright  1997-2009 The Authors
 * @license    http://opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://pear.php.net/package/PEAR
 * @since      File available since Release 1.4.0a12
 */

/**
 * For downloading REST xml/txt files
 */
require_once 'PEAR/REST.php';
require_once 'PEAR/REST/10.php';

/**
 * Implement REST 1.3
 *
 * @category   pear
 * @package    PEAR
 * @author     Greg Beaver <cellog@php.net>
 * @copyright  1997-2009 The Authors
 * @license    http://opensource.org/licenses/bsd-license.php New BSD License
 * @version    Release: 1.10.1
 * @link       http://pear.php.net/package/PEAR
 * @since      Class available since Release 1.4.0a12
 */
class PEAR_REST_13 extends PEAR_REST_10
{
    /**
     * Retrieve information about a remote package to be downloaded from a REST server
     *
     * This is smart enough to resolve the minimum PHP version dependency prior to download
     * @param string $base The uri to prepend to all REST calls
     * @param array $packageinfo an array of format:
     * <pre>
     *  array(
     *   'package' => 'packagename',
     *   'channel' => 'channelname',
     *  ['state' => 'alpha' (or valid state),]
     *  -or-
     *  ['version' => '1.whatever']
     * </pre>
     * @param string $prefstate Current preferred_state config variable value
     * @param bool $installed the installed version of this package to compare against
     * @return array|false|PEAR_Error see {@link _returnDownloadURL()}
     */
    function getDownloadURL($base, $packageinfo, $prefstate, $installed, $channel = false)
    {
        $states = $this->betterStates($prefstate, true);
        if (!$states) {
            return PEAR::raiseError('"' . $prefstate . '" is not a valid state');
        }

        $channel  = $packageinfo['channel'];
        $package  = $packageinfo['package'];
        $state    = isset($packageinfo['state'])   ? $packageinfo['state']   : null;
        $version  = isset($packageinfo['version']) ? $packageinfo['version'] : null;
        $restFile = $base . 'r/' . strtolower($package) . '/allreleases2.xml';

        $info = $this->_rest->retrieveData($restFile, false, false, $channel);
        if (PEAR::isError($info)) {
            return PEAR::raiseError('No releases available for package "' .
                $channel . '/' . $package . '"');
        }

        if (!isset($info['r'])) {
            return false;
        }

        $release = $found = false;
        if (!is_array($info['r']) || !isset($info['r'][0])) {
            $info['r'] = array($info['r']);
        }

        $skippedphp = false;
        foreach ($info['r'] as $release) {
            if (!isset($this->_rest->_options['force']) && ($installed &&
                  version_compare($release['v'], $installed, '<'))) {
                continue;
            }

            if (isset($state)) {
                // try our preferred state first
                if ($release['s'] == $state) {
                    if (!isset($version) && version_compare($release['m'], phpversion(), '>')) {
                        // skip releases that require a PHP version newer than our PHP version
                        $skippedphp = $release;
                        continue;
                    }
                    $found = true;
                    break;
                }

                // see if there is something newer and more stable
                // bug #7221
                if (in_array($release['s'], $this->betterStates($state), true)) {
                    if (!isset($version) && version_compare($release['m'], phpversion(), '>')) {
                        // skip releases that require a PHP version newer than our PHP version
                        $skippedphp = $release;
                        continue;
                    }
                    $found = true;
                    break;
                }
            } elseif (isset($version)) {
                if ($release['v'] == $version) {
                    if (!isset($this->_rest->_options['force']) &&
                          !isset($version) &&
                          version_compare($release['m'], phpversion(), '>')) {
                        // skip releases that require a PHP version newer than our PHP version
                        $skippedphp = $release;
                        continue;
                    }
                    $found = true;
                    break;
                }
            } else {
                if (in_array($release['s'], $states)) {
                    if (version_compare($release['m'], phpversion(), '>')) {
                        // skip releases that require a PHP version newer than our PHP version
                        $skippedphp = $release;
                        continue;
                    }
                    $found = true;
                    break;
                }
            }
        }

        if (!$found && $skippedphp) {
            $found = null;
        }

        return $this->_returnDownloadURL($base, $package, $release, $info, $found, $skippedphp, $channel);
    }

    function getDepDownloadURL($base, $xsdversion, $dependency, $deppackage,
                               $prefstate = 'stable', $installed = false, $channel = false)
    {
        $states = $this->betterStates($prefstate, true);
        if (!$states) {
            return PEAR::raiseError('"' . $prefstate . '" is not a valid state');
        }

        $channel  = $dependency['channel'];
        $package  = $dependency['name'];
        $state    = isset($dependency['state'])   ? $dependency['state']   : null;
        $version  = isset($dependency['version']) ? $dependency['version'] : null;
        $restFile = $base . 'r/' . strtolower($package) .'/allreleases2.xml';

        $info = $this->_rest->retrieveData($restFile, false, false, $channel);
        if (PEAR::isError($info)) {
            return PEAR::raiseError('Package "' . $deppackage['channel'] . '/' . $deppackage['package']
                . '" dependency "' . $channel . '/' . $package . '" has no releases');
        }

        if (!is_array($info) || !isset($info['r'])) {
            return false;
        }

        $exclude = array();
        $min = $max = $recommended = false;
        if ($xsdversion == '1.0') {
            $pinfo['package'] = $dependency['name'];
            $pinfo['channel'] = 'pear.php.net'; // this is always true - don't change this
            switch ($dependency['rel']) {
                case 'ge' :
                    $min = $dependency['version'];
                break;
                case 'gt' :
                    $min = $dependency['version'];
                    $exclude = array($dependency['version']);
                break;
                case 'eq' :
                    $recommended = $dependency['version'];
                break;
                case 'lt' :
                    $max = $dependency['version'];
                    $exclude = array($dependency['version']);
                break;
                case 'le' :
                    $max = $dependency['version'];
                break;
                case 'ne' :
                    $exclude = array($dependency['version']);
                break;
            }
        } else {
            $pinfo['package'] = $dependency['name'];
            $min = isset($dependency['min']) ? $dependency['min'] : false;
            $max = isset($dependency['max']) ? $dependency['max'] : false;
            $recommended = isset($dependency['recommended']) ?
                $dependency['recommended'] : false;
            if (isset($dependency['exclude'])) {
                if (!isset($dependency['exclude'][0])) {
                    $exclude = array($dependency['exclude']);
                }
            }
        }

        $skippedphp = $found = $release = false;
        if (!is_array($info['r']) || !isset($info['r'][0])) {
            $info['r'] = array($info['r']);
        }

        foreach ($info['r'] as $release) {
            if (!isset($this->_rest->_options['force']) && ($installed &&
                  version_compare($release['v'], $installed, '<'))) {
                continue;
            }

            if (in_array($release['v'], $exclude)) { // skip excluded versions
                continue;
            }

            // allow newer releases to say "I'm OK with the dependent package"
            if ($xsdversion == '2.0' && isset($release['co'])) {
                if (!is_array($release['co']) || !isset($release['co'][0])) {
                    $release['co'] = array($release['co']);
                }

                foreach ($release['co'] as $entry) {
                    if (isset($entry['x']) && !is_array($entry['x'])) {
                        $entry['x'] = array($entry['x']);
                    } elseif (!isset($entry['x'])) {
                        $entry['x'] = array();
                    }

                    if ($entry['c'] == $deppackage['channel'] &&
                          strtolower($entry['p']) == strtolower($deppackage['package']) &&
                          version_compare($deppackage['version'], $entry['min'], '>=') &&
                          version_compare($deppackage['version'], $entry['max'], '<=') &&
                          !in_array($release['v'], $entry['x'])) {
                        if (version_compare($release['m'], phpversion(), '>')) {
                            // skip dependency releases that require a PHP version
                            // newer than our PHP version
                            $skippedphp = $release;
                            continue;
                        }

                        $recommended = $release['v'];
                        break;
                    }
                }
            }

            if ($recommended) {
                if ($release['v'] != $recommended) { // if we want a specific
                    // version, then skip all others
                    continue;
                }

                if (!in_array($release['s'], $states)) {
                    // the stability is too low, but we must return the
                    // recommended version if possible
                    return $this->_returnDownloadURL($base, $package, $release, $info, true, false, $channel);
                }
            }

            if ($min && version_compare($release['v'], $min, 'lt')) { // skip too old versions
                continue;
            }

            if ($max && version_compare($release['v'], $max, 'gt')) { // skip too new versions
                continue;
            }

            if ($installed && version_compare($release['v'], $installed, '<')) {
                continue;
            }

            if (in_array($release['s'], $states)) { // if in the preferred state...
                if (version_compare($release['m'], phpversion(), '>')) {
                    // skip dependency releases that require a PHP version
                    // newer than our PHP version
                    $skippedphp = $release;
                    continue;
                }

                $found = true; // ... then use it
                break;
            }
        }

        if (!$found && $skippedphp) {
            $found = null;
        }

        return $this->_returnDownloadURL($base, $package, $release, $info, $found, $skippedphp, $channel);
    }

    /**
     * List package upgrades but take the PHP version into account.
     */
    function listLatestUpgrades($base, $pref_state, $installed, $channel, &$reg)
    {
        $packagelist = $this->_rest->retrieveData($base . 'p/packages.xml', false, false, $channel);
        if (PEAR::isError($packagelist)) {
            return $packagelist;
        }

        $ret = array();
        if (!is_array($packagelist) || !isset($packagelist['p'])) {
            return $ret;
        }

        if (!is_array($packagelist['p'])) {
            $packagelist['p'] = array($packagelist['p']);
        }

        foreach ($packagelist['p'] as $package) {
            if (!isset($installed[strtolower($package)])) {
                continue;
            }

            $inst_version = $reg->packageInfo($package, 'version', $channel);
            $inst_state   = $reg->packageInfo($package, 'release_state', $channel);
            PEAR::pushErrorHandling(PEAR_ERROR_RETURN);
            $info = $this->_rest->retrieveData($base . 'r/' . strtolower($package) .
                '/allreleases2.xml', false, false, $channel);
            PEAR::popErrorHandling();
            if (PEAR::isError($info)) {
                continue; // no remote releases
            }

            if (!isset($info['r'])) {
                continue;
            }

            $release = $found = false;
            if (!is_array($info['r']) || !isset($info['r'][0])) {
                $info['r'] = array($info['r']);
            }

            // $info['r'] is sorted by version number
            usort($info['r'], array($this, '_sortReleasesByVersionNumber'));
            foreach ($info['r'] as $release) {
                if ($inst_version && version_compare($release['v'], $inst_version, '<=')) {
                    // not newer than the one installed
                    break;
                }
                if (version_compare($release['m'], phpversion(), '>')) {
                    // skip dependency releases that require a PHP version
                    // newer than our PHP version
                    continue;
                }

                // new version > installed version
                if (!$pref_state) {
                    // every state is a good state
                    $found = true;
                    break;
                } else {
                    $new_state = $release['s'];
                    // if new state >= installed state: go
                    if (in_array($new_state, $this->betterStates($inst_state, true))) {
                        $found = true;
                        break;
                    } else {
                        // only allow to lower the state of package,
                        // if new state >= preferred state: go
                        if (in_array($new_state, $this->betterStates($pref_state, true))) {
                            $found = true;
                            break;
                        }
                    }
                }
            }

            if (!$found) {
                continue;
            }

            $relinfo = $this->_rest->retrieveCacheFirst($base . 'r/' . strtolower($package) . '/' .
                $release['v'] . '.xml', false, false, $channel);
            if (PEAR::isError($relinfo)) {
                return $relinfo;
            }

            $ret[$package] = array(
                'version'  => $release['v'],
                'state'    => $release['s'],
                'filesize' => $relinfo['f'],
            );
        }

        return $ret;
    }
}