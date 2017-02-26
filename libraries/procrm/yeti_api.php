<?php
namespace Migoi\Yetiforce;
use Migoi\Yetiforce\Adapter\Adapter;

/**
 * Yetiforce api wrapper
 *
 */
class Yeti
{
    /**
     * @var Adapter The adapter we'll use for sending requests
     */
    protected $adapter;
    /**
     * @var string The host of the Vtiger instance
     */
    protected $host;
    /**
     * @var string The username of the user we'll login as
     */
    protected $user;
    /**
     * @var string The api token for this user
     */
    protected $token;
    /**
     * @var string The current session id
     */
    protected $sessionId;
    /**
     * @var int The time the session was made (for long running sessions, we'll automatically reconnect)
     */
    protected $sessionTime;
    /**
     * @var string The current user id
     */
    protected $userId;
    /**
     * Constructor
     *
     * @param Adapter $adapter The adapter we'll use for sending requests
     * @param string  $host    The host of the Vtiger instance
     * @param string  $user    The username of the user we'll login as
     * @param string  $token  The api token for this user
     */
    public function __construct(Adapter $adapter, $host, $user, $token)
    {
        $this->adapter = $adapter;
        $this->host = (string) $host;
        $this->user = (string) $user;
        $this->token = (string) $token;
    }
    /**
     * Issue a a request (delegate it to our adapter)
     *
     * @param string $method   The HTTP method for the request
     * @param string $endpoint The api endpoint for the request
     * @param array  $postVars A list of post variables
     *
     * @return array An associative array (parsed json response from the api)
     */
    protected function request($method, $endpoint, array $postVars = null)
    {
        return $this->adapter->request($method, $this->host, $endpoint, $postVars);
    }
    /**
     * Get a login token (the first step in authorizing)
     *
     * @return string A login token
     */
    protected function getToken()
    {
        $response = $this->request(
            'GET',
            '/webservice.php?operation=getchallenge&username=' . $this->user
        );
        return $response->result->token;
    }
    /**
     * Validate the login token (the second step in authorizing)
     *
     * @param string $token The token we received in the first step of authorizing
     *
     * @return string The session id for the created session
     */
    protected function login($token)
    {
        $accessKey = md5($token . $this->token);
        $response = $this->request(
            'POST',
            '/webservice.php',
            array(
                'operation' => 'login',
                'username' => $this->user,
                'accessKey' => $accessKey,
            )
        );
        return $response->result->sessionName;
    }
    /**
     * Get the current session id or walk through the authorizing process for a new one.
     *
     * @return string The session id for the current session
     */
    protected function getSession()
    {
        $time = time();
        if (empty($this->sessionId) || $time - 240 > $this->sessionTime) {
            $token = $this->getToken();
            $this->sessionId = $this->login($token);
            $this->sessionTime = $time;
        }
        return $this->sessionId;
    }
    /**
     * Get a list of all types that are in the CRM
     *
     * @return array A response object
     */
    public function getTypes()
    {
        $response = $this->request(
            'GET',
            '/webservice.php?operation=listtypes&sessionName=' . $this->getSession()
        );
        return $response;
    }
    /**
     * Create an entity
     *
     * @param string $type   The entity type
     * @param array  $entity The new entity
     *
     * @return array A response object
     */
    public function create($type, array $entity)
    {
        $response = $this->request(
            'POST',
            '/webservice.php',
            array(
                'operation' => 'create',
                'sessionName' => $this->getSession(),
                'element' => json_encode($entity),
                'elementType' => $type,
            )
        );
        return $response;
    }
    /**
     * Get the data from an entity
     *
     * @param string $id The entity's id
     *
     * @return array A response object
     */
    public function read($id)
    {
        $response = $this->request(
            'GET',
            '/webservice.php?operation=retrieve&sessionName=' . $this->getSession() . '&id=' . $id
        );
        return $response;
    }
    /**
     * Update an entity
     *
     * @param array $entity The new entity data (make sure to include id)
     *
     * @return array A response object
     */
    public function update($entity)
    {
        $response = $this->request(
            'POST',
            '/webservice.php',
            array(
                'operation' => 'update',
                'sessionName' => $this->getSession(),
                'element' => json_encode($entity),
            )
        );
        return $response;
    }
    /**
     * Delete an entity
     *
     * @param string $id The entity's id
     *
     * @return array A response object
     */
    public function delete($id)
    {
        $response = $this->request(
            'POST',
            '/webservice.php',
            array(
                'operation' => 'delete',
                'sessionName' => $this->getSession(),
                'id' => $id,
            )
        );
        return $response;
    }
    /**
     * Query the database
     *
     * @param string $select The selected fields
     * @param string $from   The name of the entities we want
     * @param string $where  The where filter
     * @param string $order  The order clause
     * @param string $limit  The limit clause
     * @param string $offset The offset clause
     *
     * @return array A response object
     */
    public function query($select, $from, $where = null, $order = null, $limit = null, $offset = null)
    {
        $query = 'SELECT ' . $select;
        $query .= ' FROM ' . $from;
        if ($where) {
            $query .= ' WHERE ' . $where;
        }
        if ($order) {
            $query .= ' ORDER BY  ' . $order;
        }
        if ($limit) {
            $query .= ' LIMIT ';
            if ($offset) {
                $query .= $offset . ', ';
            }
            $query .= $limit;
        }
        $query .= ';';
        $response = $this->request(
            'GET',
            '/webservice.php?operation=query&sessionName=' . $this->getSession() . '&query=' . urlencode($query)
        );
        return $response;
    }
    
    
    /**
     * request information about a VTiger entity
     *
     * @param $entity
     * @return array
     */
    public function describe($entity)
    {
        $response = $this->request(
            'GET',
            '/webservice.php?operation=describe&sessionName=' . $this->getSession() . '&elementType=' . urlencode($entity)
        );
        return $response;
    }
}

// use Buzz\Message\RequestInterface;
// use Buzz\Message\MessageInterface;
// use Buzz\Message\Request;
// use Buzz\Message\Form\FormRequest;
// use Buzz\Message\Response;
// use Buzz\Client\FileGetContents;
/**
 * An adapter for the Buzz HTTP client
 *
 * @author Toon Daelman <toon@sumocoders.be>
 */
class BuzzAdapter
{
    /**
     * Issue a request
     *
     * @param string $method   The HTTP method for the request
     * @param string $host     The api host for the request
     * @param string $endpoint The api endpoint for the request
     * @param array  $postVars A list of post variables
     *
     * @return \stdClass The decoded json response from the server
     */
    public function request($method, $host, $endpoint, array $postVars = null)
    {
        $request = $this->createRequest($method, $host, $endpoint, $postVars);
        $response = $this->send($request);
        $data = $this->decode($response);
        $this->validate($data, $response);
        return $data;
    }
    /**
     * Create a request object
     *
     * @param string $method   The HTTP method for the request
     * @param string $host     The api host for the request
     * @param string $endpoint The api endpoint for the request
     * @param array  $postVars A list of post variables
     *
     * @return RequestInterface The request
     */
    protected function createRequest($method, $host, $endpoint, array $postVars = null)
    {
        if (empty($postVars)) {
            $request = new Request($method, $endpoint, $host);
        } else {
            $request = new FormRequest($method, $endpoint, $host);
            foreach ($postVars as $key => $value) {
                $request->setField($key, $value);
            }
        }
        return $request;
    }
    /**
     * Send the request
     *
     * @param RequestInterface $request The request to send
     *
     * @return MessageInterface The response
     */
    protected function send(RequestInterface $request)
    {
        $response = new Response();
        $client = new FileGetContents();
        $client->send($request, $response);
        return $response;
    }
    /**
     * Decode the response content from json
     *
     * @param MessageInterface $response The response we received
     *
     * @return \stdClass The decoded json response from the server
     */
    protected function decode(MessageInterface $response)
    {
        return json_decode($response->getContent());
    }
    /**
     * Validate the json response
     *
     * @param \stdClass        $data     The decoded json response from the server
     * @param MessageInterface $response The response we received
     *
     * @throws InvalidResponseException when the response indicates failure
     */
    protected function validate($data, MessageInterface $response)
    {
        if (!isset($data->success) || $data->success !== true || !isset($data->result)) {
            throw new InvalidResponseException('Invalid response: ' . $response);
        }
    }
}
