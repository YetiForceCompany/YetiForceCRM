<?php

namespace Sabre\DAV\Auth;

use Sabre\HTTP\RequestInterface;
use Sabre\HTTP\ResponseInterface;
use Sabre\HTTP\URLUtil;
use Sabre\DAV\Exception\NotAuthenticated;
use Sabre\DAV\Server;
use Sabre\DAV\ServerPlugin;

/**
 * This plugin provides Authentication for a WebDAV server.
 *
 * It works by providing a Auth\Backend class. Several examples of these
 * classes can be found in the Backend directory.
 *
 * It's possible to provide more than one backend to this plugin. If more than
 * one backend was provided, each backend will attempt to authenticate. Only if
 * all backends fail, we throw a 401.
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class Plugin extends ServerPlugin {

    /**
     * authentication backends
     */
    protected $backends;

    /**
     * The currently logged in principal. Will be `null` if nobody is currently
     * logged in.
     *
     * @var string|null
     */
    protected $currentPrincipal;

    /**
     * Creates the authentication plugin
     *
     * @param Backend\BackendInterface $authBackend
     */
    function __construct(Backend\BackendInterface $authBackend = null) {

        if (!is_null($authBackend)) {
            $this->addBackend($authBackend);
        }

    }

    /**
     * Adds an authentication backend to the plugin.
     *
     * @param Backend\BackendInterface $authBackend
     * @return void
     */
    function addBackend(Backend\BackendInterface $authBackend) {

        $this->backends[] = $authBackend;

    }

    /**
     * Initializes the plugin. This function is automatically called by the server
     *
     * @param Server $server
     * @return void
     */
    function initialize(Server $server) {

        $server->on('beforeMethod', [$this, 'beforeMethod'], 10);

    }

    /**
     * Returns a plugin name.
     *
     * Using this name other plugins will be able to access other plugins
     * using DAV\Server::getPlugin
     *
     * @return string
     */
    function getPluginName() {

        return 'auth';

    }

    /**
     * Returns the currently logged-in principal.
     *
     * This will return a string such as:
     *
     * principals/username
     * principals/users/username
     *
     * This method will return null if nobody is logged in.
     *
     * @return string|null
     */
    function getCurrentPrincipal() {

        return $this->currentPrincipal;

    }

    /**
     * Returns the current username.
     *
     * This method is deprecated and is only kept for backwards compatibility
     * purposes. Please switch to getCurrentPrincipal().
     *
     * @deprecated Will be removed in a future version!
     * @return string|null
     */
    function getCurrentUser() {

        // We just do a 'basename' on the principal to give back a sane value
        // here.
        list(, $userName) = URLUtil::splitPath(
            $this->getCurrentPrincipal()
        );

        return $userName;

    }

    /**
     * This method is called before any HTTP method and forces users to be authenticated
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return bool
     */
    function beforeMethod(RequestInterface $request, ResponseInterface $response) {

        if ($this->currentPrincipal) {

            // We already have authentication information. This means that the
            // event has already fired earlier, and is now likely fired for a
            // sub-request.
            //
            // We don't want to authenticate users twice, so we simply don't do
            // anything here. See Issue #700 for additional reasoning.
            //
            // This is not a perfect solution, but will be fixed once the
            // "currently authenticated principal" is information that's not
            // not associated with the plugin, but rather per-request.
            //
            // See issue #580 for more information about that.
            return;

        }
        if (!$this->backends) {
            throw new \Sabre\DAV\Exception('No authentication backends were configured on this server.');
        }
        $reasons = [];
        foreach ($this->backends as $backend) {

            $result = $backend->check(
                $request,
                $response
            );

            if (!is_array($result) || count($result) !== 2 || !is_bool($result[0]) || !is_string($result[1])) {
                throw new \Sabre\DAV\Exception('The authentication backend did not return a correct value from the check() method.');
            }

            if ($result[0]) {
                $this->currentPrincipal = $result[1];
                // Exit early
                return;
            }
            $reasons[] = $result[1];

        }

        // If we got here, it means that no authentication backend was
        // successful in authenticating the user.
        $this->currentPrincipal = null;

        foreach ($this->backends as $backend) {
            $backend->challenge($request, $response);
        }
        throw new NotAuthenticated(implode(', ', $reasons));

    }

    /**
     * Returns a bunch of meta-data about the plugin.
     *
     * Providing this information is optional, and is mainly displayed by the
     * Browser plugin.
     *
     * The description key in the returned array may contain html and will not
     * be sanitized.
     *
     * @return array
     */
    function getPluginInfo() {

        return [
            'name'        => $this->getPluginName(),
            'description' => 'Generic authentication plugin',
            'link'        => 'http://sabre.io/dav/authentication/',
        ];

    }

}
