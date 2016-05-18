<?php

namespace Sabre\DAVACL;

use Sabre\DAV;
use Sabre\DAV\INode;
use Sabre\DAV\Exception\BadRequest;
use Sabre\HTTP\RequestInterface;
use Sabre\HTTP\ResponseInterface;
use Sabre\Uri;

/**
 * SabreDAV ACL Plugin
 *
 * This plugin provides functionality to enforce ACL permissions.
 * ACL is defined in RFC3744.
 *
 * In addition it also provides support for the {DAV:}current-user-principal
 * property, defined in RFC5397 and the {DAV:}expand-property report, as
 * defined in RFC3253.
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class Plugin extends DAV\ServerPlugin {

    /**
     * Recursion constants
     *
     * This only checks the base node
     */
    const R_PARENT = 1;

    /**
     * Recursion constants
     *
     * This checks every node in the tree
     */
    const R_RECURSIVE = 2;

    /**
     * Recursion constants
     *
     * This checks every parentnode in the tree, but not leaf-nodes.
     */
    const R_RECURSIVEPARENTS = 3;

    /**
     * Reference to server object.
     *
     * @var Sabre\DAV\Server
     */
    protected $server;

    /**
     * List of urls containing principal collections.
     * Modify this if your principals are located elsewhere.
     *
     * @var array
     */
    public $principalCollectionSet = [
        'principals',
    ];

    /**
     * By default ACL is only enforced for nodes that have ACL support (the
     * ones that implement IACL). For any other node, access is
     * always granted.
     *
     * To override this behaviour you can turn this setting off. This is useful
     * if you plan to fully support ACL in the entire tree.
     *
     * @var bool
     */
    public $allowAccessToNodesWithoutACL = true;

    /**
     * By default nodes that are inaccessible by the user, can still be seen
     * in directory listings (PROPFIND on parent with Depth: 1)
     *
     * In certain cases it's desirable to hide inaccessible nodes. Setting this
     * to true will cause these nodes to be hidden from directory listings.
     *
     * @var bool
     */
    public $hideNodesFromListings = false;

    /**
     * This list of properties are the properties a client can search on using
     * the {DAV:}principal-property-search report.
     *
     * The keys are the property names, values are descriptions.
     *
     * @var array
     */
    public $principalSearchPropertySet = [
        '{DAV:}displayname'                     => 'Display name',
        '{http://sabredav.org/ns}email-address' => 'Email address',
    ];

    /**
     * Any principal uri's added here, will automatically be added to the list
     * of ACL's. They will effectively receive {DAV:}all privileges, as a
     * protected privilege.
     *
     * @var array
     */
    public $adminPrincipals = [];

    /**
     * Returns a list of features added by this plugin.
     *
     * This list is used in the response of a HTTP OPTIONS request.
     *
     * @return array
     */
    function getFeatures() {

        return ['access-control', 'calendarserver-principal-property-search'];

    }

    /**
     * Returns a list of available methods for a given url
     *
     * @param string $uri
     * @return array
     */
    function getMethods($uri) {

        return ['ACL'];

    }

    /**
     * Returns a plugin name.
     *
     * Using this name other plugins will be able to access other plugins
     * using Sabre\DAV\Server::getPlugin
     *
     * @return string
     */
    function getPluginName() {

        return 'acl';

    }

    /**
     * Returns a list of reports this plugin supports.
     *
     * This will be used in the {DAV:}supported-report-set property.
     * Note that you still need to subscribe to the 'report' event to actually
     * implement them
     *
     * @param string $uri
     * @return array
     */
    function getSupportedReportSet($uri) {

        return [
            '{DAV:}expand-property',
            '{DAV:}principal-property-search',
            '{DAV:}principal-search-property-set',
        ];

    }


    /**
     * Checks if the current user has the specified privilege(s).
     *
     * You can specify a single privilege, or a list of privileges.
     * This method will throw an exception if the privilege is not available
     * and return true otherwise.
     *
     * @param string $uri
     * @param array|string $privileges
     * @param int $recursion
     * @param bool $throwExceptions if set to false, this method won't throw exceptions.
     * @throws Sabre\DAVACL\Exception\NeedPrivileges
     * @return bool
     */
    function checkPrivileges($uri, $privileges, $recursion = self::R_PARENT, $throwExceptions = true) {

        if (!is_array($privileges)) $privileges = [$privileges];

        $acl = $this->getCurrentUserPrivilegeSet($uri);

        if (is_null($acl)) {
            if ($this->allowAccessToNodesWithoutACL) {
                return true;
            } else {
                if ($throwExceptions)
                    throw new Exception\NeedPrivileges($uri, $privileges);
                else
                    return false;

            }
        }

        $failed = [];
        foreach ($privileges as $priv) {

            if (!in_array($priv, $acl)) {
                $failed[] = $priv;
            }

        }

        if ($failed) {
            if ($throwExceptions)
                throw new Exception\NeedPrivileges($uri, $failed);
            else
                return false;
        }
        return true;

    }

    /**
     * Returns the standard users' principal.
     *
     * This is one authorative principal url for the current user.
     * This method will return null if the user wasn't logged in.
     *
     * @return string|null
     */
    function getCurrentUserPrincipal() {

        $authPlugin = $this->server->getPlugin('auth');
        if (is_null($authPlugin)) return null;
        /** @var $authPlugin Sabre\DAV\Auth\Plugin */

        return $authPlugin->getCurrentPrincipal();

    }


    /**
     * Returns a list of principals that's associated to the current
     * user, either directly or through group membership.
     *
     * @return array
     */
    function getCurrentUserPrincipals() {

        $currentUser = $this->getCurrentUserPrincipal();

        if (is_null($currentUser)) return [];

        return array_merge(
            [$currentUser],
            $this->getPrincipalMembership($currentUser)
        );

    }

    /**
     * This array holds a cache for all the principals that are associated with
     * a single principal.
     *
     * @var array
     */
    protected $principalMembershipCache = [];


    /**
     * Returns all the principal groups the specified principal is a member of.
     *
     * @param string $principal
     * @return array
     */
    function getPrincipalMembership($mainPrincipal) {

        // First check our cache
        if (isset($this->principalMembershipCache[$mainPrincipal])) {
            return $this->principalMembershipCache[$mainPrincipal];
        }

        $check = [$mainPrincipal];
        $principals = [];

        while (count($check)) {

            $principal = array_shift($check);

            $node = $this->server->tree->getNodeForPath($principal);
            if ($node instanceof IPrincipal) {
                foreach ($node->getGroupMembership() as $groupMember) {

                    if (!in_array($groupMember, $principals)) {

                        $check[] = $groupMember;
                        $principals[] = $groupMember;

                    }

                }

            }

        }

        // Store the result in the cache
        $this->principalMembershipCache[$mainPrincipal] = $principals;

        return $principals;

    }

    /**
     * Returns the supported privilege structure for this ACL plugin.
     *
     * See RFC3744 for more details. Currently we default on a simple,
     * standard structure.
     *
     * You can either get the list of privileges by a uri (path) or by
     * specifying a Node.
     *
     * @param string|INode $node
     * @return array
     */
    function getSupportedPrivilegeSet($node) {

        if (is_string($node)) {
            $node = $this->server->tree->getNodeForPath($node);
        }

        if ($node instanceof IACL) {
            $result = $node->getSupportedPrivilegeSet();

            if ($result)
                return $result;
        }

        return self::getDefaultSupportedPrivilegeSet();

    }

    /**
     * Returns a fairly standard set of privileges, which may be useful for
     * other systems to use as a basis.
     *
     * @return array
     */
    static function getDefaultSupportedPrivilegeSet() {

        return [
            'privilege'  => '{DAV:}all',
            'abstract'   => true,
            'aggregates' => [
                [
                    'privilege'  => '{DAV:}read',
                    'aggregates' => [
                        [
                            'privilege' => '{DAV:}read-acl',
                            'abstract'  => false,
                        ],
                        [
                            'privilege' => '{DAV:}read-current-user-privilege-set',
                            'abstract'  => false,
                        ],
                    ],
                ], // {DAV:}read
                [
                    'privilege'  => '{DAV:}write',
                    'aggregates' => [
                        [
                            'privilege' => '{DAV:}write-acl',
                            'abstract'  => false,
                        ],
                        [
                            'privilege' => '{DAV:}write-properties',
                            'abstract'  => false,
                        ],
                        [
                            'privilege' => '{DAV:}write-content',
                            'abstract'  => false,
                        ],
                        [
                            'privilege' => '{DAV:}bind',
                            'abstract'  => false,
                        ],
                        [
                            'privilege' => '{DAV:}unbind',
                            'abstract'  => false,
                        ],
                        [
                            'privilege' => '{DAV:}unlock',
                            'abstract'  => false,
                        ],
                    ],
                ], // {DAV:}write
            ],
        ]; // {DAV:}all

    }

    /**
     * Returns the supported privilege set as a flat list
     *
     * This is much easier to parse.
     *
     * The returned list will be index by privilege name.
     * The value is a struct containing the following properties:
     *   - aggregates
     *   - abstract
     *   - concrete
     *
     * @param string|INode $node
     * @return array
     */
    final function getFlatPrivilegeSet($node) {

        $privs = $this->getSupportedPrivilegeSet($node);

        $fpsTraverse = null;
        $fpsTraverse = function($priv, $concrete, &$flat) use (&$fpsTraverse) {

            $myPriv = [
                'privilege'  => $priv['privilege'],
                'abstract'   => isset($priv['abstract']) && $priv['abstract'],
                'aggregates' => [],
                'concrete'   => isset($priv['abstract']) && $priv['abstract'] ? $concrete : $priv['privilege'],
            ];

            if (isset($priv['aggregates'])) {

                foreach ($priv['aggregates'] as $subPriv) {

                    $myPriv['aggregates'][] = $subPriv['privilege'];

                }

            }

            $flat[$priv['privilege']] = $myPriv;

            if (isset($priv['aggregates'])) {

                foreach ($priv['aggregates'] as $subPriv) {

                    $fpsTraverse($subPriv, $myPriv['concrete'], $flat);

                }

            }

        };

        $flat = [];
        $fpsTraverse($privs, null, $flat);

        return $flat;

    }

    /**
     * Returns the full ACL list.
     *
     * Either a uri or a INode may be passed.
     *
     * null will be returned if the node doesn't support ACLs.
     *
     * @param string|DAV\INode $node
     * @return array
     */
    function getACL($node) {

        if (is_string($node)) {
            $node = $this->server->tree->getNodeForPath($node);
        }
        if (!$node instanceof IACL) {
            return null;
        }
        $acl = $node->getACL();
        foreach ($this->adminPrincipals as $adminPrincipal) {
            $acl[] = [
                'principal' => $adminPrincipal,
                'privilege' => '{DAV:}all',
                'protected' => true,
            ];
        }
        return $acl;

    }

    /**
     * Returns a list of privileges the current user has
     * on a particular node.
     *
     * Either a uri or a DAV\INode may be passed.
     *
     * null will be returned if the node doesn't support ACLs.
     *
     * @param string|DAV\INode $node
     * @return array
     */
    function getCurrentUserPrivilegeSet($node) {

        if (is_string($node)) {
            $node = $this->server->tree->getNodeForPath($node);
        }

        $acl = $this->getACL($node);

        if (is_null($acl)) return null;

        $principals = $this->getCurrentUserPrincipals();

        $collected = [];

        foreach ($acl as $ace) {

            $principal = $ace['principal'];

            switch ($principal) {

                case '{DAV:}owner' :
                    $owner = $node->getOwner();
                    if ($owner && in_array($owner, $principals)) {
                        $collected[] = $ace;
                    }
                    break;


                // 'all' matches for every user
                case '{DAV:}all' :

                // 'authenticated' matched for every user that's logged in.
                // Since it's not possible to use ACL while not being logged
                // in, this is also always true.
                case '{DAV:}authenticated' :
                    $collected[] = $ace;
                    break;

                // 'unauthenticated' can never occur either, so we simply
                // ignore these.
                case '{DAV:}unauthenticated' :
                    break;

                default :
                    if (in_array($ace['principal'], $principals)) {
                        $collected[] = $ace;
                    }
                    break;

            }


        }

        // Now we deduct all aggregated privileges.
        $flat = $this->getFlatPrivilegeSet($node);

        $collected2 = [];
        while (count($collected)) {

            $current = array_pop($collected);
            $collected2[] = $current['privilege'];

            foreach ($flat[$current['privilege']]['aggregates'] as $subPriv) {
                $collected2[] = $subPriv;
                $collected[] = $flat[$subPriv];
            }

        }

        return array_values(array_unique($collected2));

    }


    /**
     * Returns a principal based on its uri.
     *
     * Returns null if the principal could not be found.
     *
     * @param string $uri
     * @return null|string
     */
    function getPrincipalByUri($uri) {

        $result = null;
        $collections = $this->principalCollectionSet;
        foreach ($collections as $collection) {

            $principalCollection = $this->server->tree->getNodeForPath($collection);
            if (!$principalCollection instanceof IPrincipalCollection) {
                // Not a principal collection, we're simply going to ignore
                // this.
                continue;
            }

            $result = $principalCollection->findByUri($uri);
            if ($result) {
                return $result;
            }

        }

    }

    /**
     * Principal property search
     *
     * This method can search for principals matching certain values in
     * properties.
     *
     * This method will return a list of properties for the matched properties.
     *
     * @param array $searchProperties    The properties to search on. This is a
     *                                   key-value list. The keys are property
     *                                   names, and the values the strings to
     *                                   match them on.
     * @param array $requestedProperties This is the list of properties to
     *                                   return for every match.
     * @param string $collectionUri      The principal collection to search on.
     *                                   If this is ommitted, the standard
     *                                   principal collection-set will be used.
     * @param string $test               "allof" to use AND to search the
     *                                   properties. 'anyof' for OR.
     * @return array     This method returns an array structure similar to
     *                  Sabre\DAV\Server::getPropertiesForPath. Returned
     *                  properties are index by a HTTP status code.
     */
    function principalSearch(array $searchProperties, array $requestedProperties, $collectionUri = null, $test = 'allof') {

        if (!is_null($collectionUri)) {
            $uris = [$collectionUri];
        } else {
            $uris = $this->principalCollectionSet;
        }

        $lookupResults = [];
        foreach ($uris as $uri) {

            $principalCollection = $this->server->tree->getNodeForPath($uri);
            if (!$principalCollection instanceof IPrincipalCollection) {
                // Not a principal collection, we're simply going to ignore
                // this.
                continue;
            }

            $results = $principalCollection->searchPrincipals($searchProperties, $test);
            foreach ($results as $result) {
                $lookupResults[] = rtrim($uri, '/') . '/' . $result;
            }

        }

        $matches = [];

        foreach ($lookupResults as $lookupResult) {

            list($matches[]) = $this->server->getPropertiesForPath($lookupResult, $requestedProperties, 0);

        }

        return $matches;

    }

    /**
     * Sets up the plugin
     *
     * This method is automatically called by the server class.
     *
     * @param DAV\Server $server
     * @return void
     */
    function initialize(DAV\Server $server) {

        $this->server = $server;
        $server->on('propFind',            [$this, 'propFind'], 20);
        $server->on('beforeMethod',        [$this, 'beforeMethod'], 20);
        $server->on('beforeBind',          [$this, 'beforeBind'], 20);
        $server->on('beforeUnbind',        [$this, 'beforeUnbind'], 20);
        $server->on('propPatch',           [$this, 'propPatch']);
        $server->on('beforeUnlock',        [$this, 'beforeUnlock'], 20);
        $server->on('report',              [$this, 'report']);
        $server->on('method:ACL',          [$this, 'httpAcl']);
        $server->on('onHTMLActionsPanel',  [$this, 'htmlActionsPanel']);

        array_push($server->protectedProperties,
            '{DAV:}alternate-URI-set',
            '{DAV:}principal-URL',
            '{DAV:}group-membership',
            '{DAV:}principal-collection-set',
            '{DAV:}current-user-principal',
            '{DAV:}supported-privilege-set',
            '{DAV:}current-user-privilege-set',
            '{DAV:}acl',
            '{DAV:}acl-restrictions',
            '{DAV:}inherited-acl-set',
            '{DAV:}owner',
            '{DAV:}group'
        );

        // Automatically mapping nodes implementing IPrincipal to the
        // {DAV:}principal resourcetype.
        $server->resourceTypeMapping['Sabre\\DAVACL\\IPrincipal'] = '{DAV:}principal';

        // Mapping the group-member-set property to the HrefList property
        // class.
        $server->xml->elementMap['{DAV:}group-member-set'] = 'Sabre\\DAV\\Xml\\Property\\Href';
        $server->xml->elementMap['{DAV:}acl'] = 'Sabre\\DAVACL\\Xml\\Property\\Acl';
        $server->xml->elementMap['{DAV:}expand-property'] = 'Sabre\\DAVACL\\Xml\\Request\\ExpandPropertyReport';
        $server->xml->elementMap['{DAV:}principal-property-search'] = 'Sabre\\DAVACL\\Xml\\Request\\PrincipalPropertySearchReport';
        $server->xml->elementMap['{DAV:}principal-search-property-set'] = 'Sabre\\DAVACL\\Xml\\Request\\PrincipalSearchPropertySetReport';

    }

    /* {{{ Event handlers */

    /**
     * Triggered before any method is handled
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return void
     */
    function beforeMethod(RequestInterface $request, ResponseInterface $response) {

        $method = $request->getMethod();
        $path = $request->getPath();

        $exists = $this->server->tree->nodeExists($path);

        // If the node doesn't exists, none of these checks apply
        if (!$exists) return;

        switch ($method) {

            case 'GET' :
            case 'HEAD' :
            case 'OPTIONS' :
                // For these 3 we only need to know if the node is readable.
                $this->checkPrivileges($path, '{DAV:}read');
                break;

            case 'PUT' :
            case 'LOCK' :
            case 'UNLOCK' :
                // This method requires the write-content priv if the node
                // already exists, and bind on the parent if the node is being
                // created.
                // The bind privilege is handled in the beforeBind event.
                $this->checkPrivileges($path, '{DAV:}write-content');
                break;


            case 'PROPPATCH' :
                $this->checkPrivileges($path, '{DAV:}write-properties');
                break;

            case 'ACL' :
                $this->checkPrivileges($path, '{DAV:}write-acl');
                break;

            case 'COPY' :
            case 'MOVE' :
                // Copy requires read privileges on the entire source tree.
                // If the target exists write-content normally needs to be
                // checked, however, we're deleting the node beforehand and
                // creating a new one after, so this is handled by the
                // beforeUnbind event.
                //
                // The creation of the new node is handled by the beforeBind
                // event.
                //
                // If MOVE is used beforeUnbind will also be used to check if
                // the sourcenode can be deleted.
                $this->checkPrivileges($path, '{DAV:}read', self::R_RECURSIVE);

                break;

        }

    }

    /**
     * Triggered before a new node is created.
     *
     * This allows us to check permissions for any operation that creates a
     * new node, such as PUT, MKCOL, MKCALENDAR, LOCK, COPY and MOVE.
     *
     * @param string $uri
     * @return void
     */
    function beforeBind($uri) {

        list($parentUri) = Uri\split($uri);
        $this->checkPrivileges($parentUri, '{DAV:}bind');

    }

    /**
     * Triggered before a node is deleted
     *
     * This allows us to check permissions for any operation that will delete
     * an existing node.
     *
     * @param string $uri
     * @return void
     */
    function beforeUnbind($uri) {

        list($parentUri) = Uri\split($uri);
        $this->checkPrivileges($parentUri, '{DAV:}unbind', self::R_RECURSIVEPARENTS);

    }

    /**
     * Triggered before a node is unlocked.
     *
     * @param string $uri
     * @param DAV\Locks\LockInfo $lock
     * @TODO: not yet implemented
     * @return void
     */
    function beforeUnlock($uri, DAV\Locks\LockInfo $lock) {


    }

    /**
     * Triggered before properties are looked up in specific nodes.
     *
     * @param DAV\PropFind $propFind
     * @param DAV\INode $node
     * @param array $requestedProperties
     * @param array $returnedProperties
     * @TODO really should be broken into multiple methods, or even a class.
     * @return bool
     */
    function propFind(DAV\PropFind $propFind, DAV\INode $node) {

        $path = $propFind->getPath();

        // Checking the read permission
        if (!$this->checkPrivileges($path, '{DAV:}read', self::R_PARENT, false)) {
            // User is not allowed to read properties

            // Returning false causes the property-fetching system to pretend
            // that the node does not exist, and will cause it to be hidden
            // from listings such as PROPFIND or the browser plugin.
            if ($this->hideNodesFromListings) {
                return false;
            }

            // Otherwise we simply mark every property as 403.
            foreach ($propFind->getRequestedProperties() as $requestedProperty) {
                $propFind->set($requestedProperty, null, 403);
            }

            return;

        }

        /* Adding principal properties */
        if ($node instanceof IPrincipal) {

            $propFind->handle('{DAV:}alternate-URI-set', function() use ($node) {
                return new DAV\Xml\Property\Href($node->getAlternateUriSet());
            });
            $propFind->handle('{DAV:}principal-URL', function() use ($node) {
                return new DAV\Xml\Property\Href($node->getPrincipalUrl() . '/');
            });
            $propFind->handle('{DAV:}group-member-set', function() use ($node) {
                $members = $node->getGroupMemberSet();
                foreach ($members as $k => $member) {
                    $members[$k] = rtrim($member, '/') . '/';
                }
                return new DAV\Xml\Property\Href($members);
            });
            $propFind->handle('{DAV:}group-membership', function() use ($node) {
                $members = $node->getGroupMembership();
                foreach ($members as $k => $member) {
                    $members[$k] = rtrim($member, '/') . '/';
                }
                return new DAV\Xml\Property\Href($members);
            });
            $propFind->handle('{DAV:}displayname', [$node, 'getDisplayName']);

        }

        $propFind->handle('{DAV:}principal-collection-set', function() {

            $val = $this->principalCollectionSet;
            // Ensuring all collections end with a slash
            foreach ($val as $k => $v) $val[$k] = $v . '/';
            return new DAV\Xml\Property\Href($val);

        });
        $propFind->handle('{DAV:}current-user-principal', function() {
            if ($url = $this->getCurrentUserPrincipal()) {
                return new Xml\Property\Principal(Xml\Property\Principal::HREF, $url . '/');
            } else {
                return new Xml\Property\Principal(Xml\Property\Principal::UNAUTHENTICATED);
            }
        });
        $propFind->handle('{DAV:}supported-privilege-set', function() use ($node) {
            return new Xml\Property\SupportedPrivilegeSet($this->getSupportedPrivilegeSet($node));
        });
        $propFind->handle('{DAV:}current-user-privilege-set', function() use ($node, $propFind, $path) {
            if (!$this->checkPrivileges($path, '{DAV:}read-current-user-privilege-set', self::R_PARENT, false)) {
                $propFind->set('{DAV:}current-user-privilege-set', null, 403);
            } else {
                $val = $this->getCurrentUserPrivilegeSet($node);
                if (!is_null($val)) {
                    return new Xml\Property\CurrentUserPrivilegeSet($val);
                }
            }
        });
        $propFind->handle('{DAV:}acl', function() use ($node, $propFind, $path) {
            /* The ACL property contains all the permissions */
            if (!$this->checkPrivileges($path, '{DAV:}read-acl', self::R_PARENT, false)) {
                $propFind->set('{DAV:}acl', null, 403);
            } else {
                $acl = $this->getACL($node);
                if (!is_null($acl)) {
                    return new Xml\Property\Acl($this->getACL($node));
                }
            }
        });
        $propFind->handle('{DAV:}acl-restrictions', function() {
            return new Xml\Property\AclRestrictions();
        });

        /* Adding ACL properties */
        if ($node instanceof IACL) {
            $propFind->handle('{DAV:}owner', function() use ($node) {
                return new DAV\Xml\Property\Href($node->getOwner() . '/');
            });
        }

    }

    /**
     * This method intercepts PROPPATCH methods and make sure the
     * group-member-set is updated correctly.
     *
     * @param string $path
     * @param DAV\PropPatch $propPatch
     * @return void
     */
    function propPatch($path, DAV\PropPatch $propPatch) {

        $propPatch->handle('{DAV:}group-member-set', function($value) use ($path) {
            if (is_null($value)) {
                $memberSet = [];
            } elseif ($value instanceof DAV\Xml\Property\Href) {
                $memberSet = array_map(
                    [$this->server, 'calculateUri'],
                    $value->getHrefs()
                );
            } else {
                throw new DAV\Exception('The group-member-set property MUST be an instance of Sabre\DAV\Property\HrefList or null');
            }
            $node = $this->server->tree->getNodeForPath($path);
            if (!($node instanceof IPrincipal)) {
                // Fail
                return false;
            }

            $node->setGroupMemberSet($memberSet);
            // We must also clear our cache, just in case

            $this->principalMembershipCache = [];

            return true;
        });

    }

    /**
     * This method handles HTTP REPORT requests
     *
     * @param string $reportName
     * @param mixed $report
     * @param mixed $path
     * @return bool
     */
    function report($reportName, $report, $path) {

        switch ($reportName) {

            case '{DAV:}principal-property-search' :
                $this->server->transactionType = 'report-principal-property-search';
                $this->principalPropertySearchReport($report);
                return false;
            case '{DAV:}principal-search-property-set' :
                $this->server->transactionType = 'report-principal-search-property-set';
                $this->principalSearchPropertySetReport($report);
                return false;
            case '{DAV:}expand-property' :
                $this->server->transactionType = 'report-expand-property';
                $this->expandPropertyReport($report);
                return false;

        }

    }

    /**
     * This method is responsible for handling the 'ACL' event.
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return bool
     */
    function httpAcl(RequestInterface $request, ResponseInterface $response) {

        $path = $request->getPath();
        $body = $request->getBodyAsString();

        if (!$body) {
            throw new DAV\Exception\BadRequest('XML body expected in ACL request');
        }

        $acl = $this->server->xml->expect('{DAV:}acl', $body);
        $newAcl = $acl->getPrivileges();

        // Normalizing urls
        foreach ($newAcl as $k => $newAce) {
            $newAcl[$k]['principal'] = $this->server->calculateUri($newAce['principal']);
        }
        $node = $this->server->tree->getNodeForPath($path);

        if (!$node instanceof IACL) {
            throw new DAV\Exception\MethodNotAllowed('This node does not support the ACL method');
        }

        $oldAcl = $this->getACL($node);

        $supportedPrivileges = $this->getFlatPrivilegeSet($node);

        /* Checking if protected principals from the existing principal set are
           not overwritten. */
        foreach ($oldAcl as $oldAce) {

            if (!isset($oldAce['protected']) || !$oldAce['protected']) continue;

            $found = false;
            foreach ($newAcl as $newAce) {
                if (
                    $newAce['privilege'] === $oldAce['privilege'] &&
                    $newAce['principal'] === $oldAce['principal'] &&
                    $newAce['protected']
                )
                $found = true;
            }

            if (!$found)
                throw new Exception\AceConflict('This resource contained a protected {DAV:}ace, but this privilege did not occur in the ACL request');

        }

        foreach ($newAcl as $newAce) {

            // Do we recognize the privilege
            if (!isset($supportedPrivileges[$newAce['privilege']])) {
                throw new Exception\NotSupportedPrivilege('The privilege you specified (' . $newAce['privilege'] . ') is not recognized by this server');
            }

            if ($supportedPrivileges[$newAce['privilege']]['abstract']) {
                throw new Exception\NoAbstract('The privilege you specified (' . $newAce['privilege'] . ') is an abstract privilege');
            }

            // Looking up the principal
            try {
                $principal = $this->server->tree->getNodeForPath($newAce['principal']);
            } catch (DAV\Exception\NotFound $e) {
                throw new Exception\NotRecognizedPrincipal('The specified principal (' . $newAce['principal'] . ') does not exist');
            }
            if (!($principal instanceof IPrincipal)) {
                throw new Exception\NotRecognizedPrincipal('The specified uri (' . $newAce['principal'] . ') is not a principal');
            }

        }
        $node->setACL($newAcl);

        $response->setStatus(200);

        // Breaking the event chain, because we handled this method.
        return false;

    }

    /* }}} */

    /* Reports {{{ */

    /**
     * The expand-property report is defined in RFC3253 section 3-8.
     *
     * This report is very similar to a standard PROPFIND. The difference is
     * that it has the additional ability to look at properties containing a
     * {DAV:}href element, follow that property and grab additional elements
     * there.
     *
     * Other rfc's, such as ACL rely on this report, so it made sense to put
     * it in this plugin.
     *
     * @param Xml\Request\ExpandPropertyReport $report
     * @return void
     */
    protected function expandPropertyReport($report) {

        $depth = $this->server->getHTTPDepth(0);
        $requestUri = $this->server->getRequestUri();

        $result = $this->expandProperties($requestUri, $report->properties, $depth);

        $xml = $this->server->xml->write(
            '{DAV:}multistatus',
            new DAV\Xml\Response\MultiStatus($result),
            $this->server->getBaseUri()
        );
        $this->server->httpResponse->setHeader('Content-Type', 'application/xml; charset=utf-8');
        $this->server->httpResponse->setStatus(207);
        $this->server->httpResponse->setBody($xml);

    }

    /**
     * This method expands all the properties and returns
     * a list with property values
     *
     * @param array $path
     * @param array $requestedProperties the list of required properties
     * @param int $depth
     * @return array
     */
    protected function expandProperties($path, array $requestedProperties, $depth) {

        $foundProperties = $this->server->getPropertiesForPath($path, array_keys($requestedProperties), $depth);

        $result = [];

        foreach ($foundProperties as $node) {

            foreach ($requestedProperties as $propertyName => $childRequestedProperties) {

                // We're only traversing if sub-properties were requested
                if (count($childRequestedProperties) === 0) continue;

                // We only have to do the expansion if the property was found
                // and it contains an href element.
                if (!array_key_exists($propertyName, $node[200])) continue;

                if (!$node[200][$propertyName] instanceof DAV\Xml\Property\Href) {
                    continue;
                }

                $childHrefs = $node[200][$propertyName]->getHrefs();
                $childProps = [];

                foreach ($childHrefs as $href) {
                    // Gathering the result of the children
                    $childProps[] = [
                        'name'  => '{DAV:}response',
                        'value' => $this->expandProperties($href, $childRequestedProperties, 0)[0]
                    ];
                }

                // Replacing the property with its expannded form.
                $node[200][$propertyName] = $childProps;

            }
            $result[] = new DAV\Xml\Element\Response($node['href'], $node);

        }

        return $result;

    }

    /**
     * principalSearchPropertySetReport
     *
     * This method responsible for handing the
     * {DAV:}principal-search-property-set report. This report returns a list
     * of properties the client may search on, using the
     * {DAV:}principal-property-search report.
     *
     * @param Xml\Request\PrincipalSearchPropertySetReport $report
     * @return void
     */
    protected function principalSearchPropertySetReport($report) {

        $httpDepth = $this->server->getHTTPDepth(0);
        if ($httpDepth !== 0) {
            throw new DAV\Exception\BadRequest('This report is only defined when Depth: 0');
        }

        $writer = $this->server->xml->getWriter();
        $writer->openMemory();
        $writer->startDocument();

        $writer->startElement('{DAV:}principal-search-property-set');

        foreach ($this->principalSearchPropertySet as $propertyName => $description) {

            $writer->startElement('{DAV:}principal-search-property');
            $writer->startElement('{DAV:}prop');

            $writer->writeElement($propertyName);

            $writer->endElement(); // prop

            if ($description) {
                $writer->write([[
                    'name'       => '{DAV:}description',
                    'value'      => $description,
                    'attributes' => ['xml:lang' => 'en']
                ]]);
            }

            $writer->endElement(); // principal-search-property


        }

        $writer->endElement(); // principal-search-property-set

        $this->server->httpResponse->setHeader('Content-Type', 'application/xml; charset=utf-8');
        $this->server->httpResponse->setStatus(200);
        $this->server->httpResponse->setBody($writer->outputMemory());

    }

    /**
     * principalPropertySearchReport
     *
     * This method is responsible for handing the
     * {DAV:}principal-property-search report. This report can be used for
     * clients to search for groups of principals, based on the value of one
     * or more properties.
     *
     * @param Xml\Request\PrincipalPropertySearchReport $report
     * @return void
     */
    protected function principalPropertySearchReport($report) {

        $uri = null;
        if (!$report->applyToPrincipalCollectionSet) {
            $uri = $this->server->httpRequest->getPath();
        }
        if ($this->server->getHttpDepth('0') !== 0) {
            throw new BadRequest('Depth must be 0');
        }
        $result = $this->principalSearch(
            $report->searchProperties,
            $report->properties,
            $uri,
            $report->test
        );

        $prefer = $this->server->getHTTPPrefer();

        $this->server->httpResponse->setStatus(207);
        $this->server->httpResponse->setHeader('Content-Type', 'application/xml; charset=utf-8');
        $this->server->httpResponse->setHeader('Vary', 'Brief,Prefer');
        $this->server->httpResponse->setBody($this->server->generateMultiStatus($result, $prefer['return'] === 'minimal'));

    }

    /* }}} */

    /**
     * This method is used to generate HTML output for the
     * DAV\Browser\Plugin. This allows us to generate an interface users
     * can use to create new calendars.
     *
     * @param DAV\INode $node
     * @param string $output
     * @return bool
     */
    function htmlActionsPanel(DAV\INode $node, &$output) {

        if (!$node instanceof PrincipalCollection)
            return;

        $output .= '<tr><td colspan="2"><form method="post" action="">
            <h3>Create new principal</h3>
            <input type="hidden" name="sabreAction" value="mkcol" />
            <input type="hidden" name="resourceType" value="{DAV:}principal" />
            <label>Name (uri):</label> <input type="text" name="name" /><br />
            <label>Display name:</label> <input type="text" name="{DAV:}displayname" /><br />
            <label>Email address:</label> <input type="text" name="{http://sabredav*DOT*org/ns}email-address" /><br />
            <input type="submit" value="create" />
            </form>
            </td></tr>';

        return false;

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
            'description' => 'Adds support for WebDAV ACL (rfc3744)',
            'link'        => 'http://sabre.io/dav/acl/',
        ];

    }
}
