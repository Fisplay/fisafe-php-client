<?php
namespace Fisafe;

use GuzzleHttp\Client;
use InvalidArgumentException;
use DateTime;

class ApiClient
{
    private $apiUrl;
    private $client;

    public function __construct(string $apiUrl)
    {
        $this->apiUrl = $apiUrl;
    }

    public function authenticate($username, $password)
    {
        $client = new Client([
            'base_uri' => $this->apiUrl
        ]);
        $response = $client->request('GET', 'v1/token/auth', [
            'auth' => [$username, $password]
        ]);

        $token = json_decode($response->getBody(), true);

        $this->client = new Client([
            'base_uri' => $this->apiUrl . '/v1/api/',
            'headers' => [
                'Authorization' => 'Bearer ' . $token['access_token']
            ]
        ]);
        return $this;
    }

    /**
     * Grants access to a specific context for a user within an optional time frame.
     *
     * @param int     $contextId The unique identifier for the context to which access is being granted.
     * @param int     $userId    The unique identifier for the user being granted access.
     * @param DateTime|null $from Optional start date-time from which the access is valid. Default is null, indicating immediate access.
     * @param DateTime|null $to   Optional end date-time until which the access is valid. Default is null, indicating indefinite access.
     *
     * @return object              Returns an object representing the granted access.
     *                             @todo Description of the object structure, if needed (modify based on actual implementation).
     *
     * @throws Exception           @todo Description of the exception, if any (this is just an example, modify based on actual implementation).
     */

    public function createGrantedAccess(int $contextId, int $userId, ?DateTime $from = null, ?DateTime $to = null ): object
    {
        $data = array(
            "user_id" => $userId,
            "expiry_time_start" => $from->format('Y-m-d H:i:s'),
            "expiry_time_end" => $from->format('Y-m-d H:i:s'),
            "context_id" => $contextId,
        ); 

        return $this->create("grants/", $data);
    }


    /**
     * Updates an existing granted access record with new context, user details, and time frame.
     *
     * @param int       $grantedAccessId The unique identifier of the granted access record to update.
     * @param int       $contextId       The unique identifier for the context to which access is being updated.
     * @param int       $userId          The unique identifier for the user whose access is being updated.
     * @param DateTime|null $from       Optional start date-time from which the updated access will be valid.
     *                                   Default is null, indicating immediate access.
     * @param DateTime|null $to         Optional end date-time until which the updated access will be valid.
     *                                   Default is null, indicating indefinite access.
     *
     * @return object                    Returns an object representing the updated granted access.
     *                                   @todo
     *
     * @throws Exception                 @todo
     * @throws InvalidArgumentException If provided arguments don't match existing data or other validation errors.
     */
    public function updateGrantedAccess(int $grantedAccessId, int $contextId, int $userId, ?DateTime $from = null, ?DateTime $to = null ): object
    {
        $data = array(
            "user_id" => $userId,
            "expiry_time_start" => $from->format('Y-m-d H:i:s'),
            "expiry_time_end" => $from->format('Y-m-d H:i:s'),
            "context_id" => $contextId,
        ); 

        return $this->update("grants/$grantedAccessId", $data);
    }

    /**
     * Creates and associates an identifier with a given user.
     *
     * @param int    $userId     The unique identifier for the user.
     * @param string $identifier The value of the identifier to be associated.
     * @param string $type       The type of the identifier. Valid values are 'pin', 'rfid-tag', or 'licence-plate'. Default is 'rfid-tag'.
     *
     * @return object            Created identifier as object
     *
     * @throws Exception         @TODO Description of the exception, if any (this is just an example, modify based on actual implementation).
     * @throws InvalidArgumentException If an invalid $type is provided.
     */
    public function createIdentifier(int $userId, string $identifier, string $type = 'rfid-tag'): object
    {
        if(!in_array($type, ['rfid-tag', 'pin', 'licence-plate'])) {
            throw new InvalidArgumentException();
        }

        $data = array(
            "type" => $type,
            "value" => $identifier
        ); 
        return $this->create("users/$userId/identifiers", $data);
    }

    /**
     * Creates a user with the provided identifier.
     *
     * @param string $userIdentifier The unique identifier for the user.
     *
     * @return object                Created user object
     *
     * @throws Exception             @TODO Description of the exception, if any (this is just an example, modify based on actual implementation).
     */
    public function createUser(string $userIdentifier): object
    {
        $data = [
            "identifier" => $userIdentifier,
            // "tag" => $tag, //BUG!!  https://gitlab.com/fisplay/ac/kulva23/-/issues/112. Separated identifier creationg because of it.
            // "owner_id" => '', // Legacy stuff, not needed?
        ];
        
        return $this->create('users/', $data);
    }


    /**
     * Retrieves a list of users based on provided filters and pagination parameters.
     *
     * @param array $filters  An associative array of filters to apply when listing users:
     *                        - 'identifier': (string) [query] Find user by exact identifier.
     *                        - 'identifierSubstring': (string) [query] Find user by identifier substring.
     *                          At least 3 characters should be provided.
     *                        Default is an empty array.
     * @param int   $page     The page number to retrieve. Default is 1.
     * @param int   $perPage  The number of users to list per page. Default is 100.
     *
     * @return array          Returns an array of users matching the filters and pagination parameters.
     *                        @todo
     *
     * @throws Exception      @todo
     * @throws InvalidArgumentException If 'identifierSubstring' is provided with less than 3 characters.
     */
    public function listUsers(array $filters = [], $page = 1, $perPage=100)
    {
        return $this->list('users', $filters, $page, $perPage);
    }


    /**
     * Retrieves a list of granted accesses based on specified filters and pagination details.
     *
     * @param array $filters Optional associative array of filters to apply to the listing.
     *                       E.g. ['contextId' => 12345, 'userId' => 6789].
     * @param int   $page    Optional page number for pagination. Default is 1.
     * @param int   $perPage Optional number of granted accesses to retrieve per page. Default is 100.
     *
     * @return array         Returns an array of objects representing each granted access that matches the filters.
     *                       Each object contains details of the granted access, such as contextId, userId, and date-time details.
     *
     * @throws Exception     @todo
     */
    public function listGrantedAccesses(array $filters = [], $page = 1, $perPage=100)
    {
        return $this->list('grants', $filters, $page, $perPage);
    }

    private function create(string $path, array $data)
    {
        $response = $this->client->post($path, [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode($data)
        ]);

        return json_decode($response->getBody());
    }


    private function update(string $path, array $data)
    {
        $response = $this->client->patch($path, [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode($data)
        ]);

        return json_decode($response->getBody());
    }

    private function list($path, array $filters = [], int $page = 1, int $perPage = 100)
    {
        $filters['page'] = $page;
        $filters['itemsPerPage'] = $perPage;

        $response = $this->client->get($path, [
            'query' => $filters
        ]);

        return json_decode($response->getBody());
    }
}
