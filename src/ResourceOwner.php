<?php 

namespace ThriveCart;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class ResourceOwner implements ResourceOwnerInterface
{
    use ArrayAccessorTrait;

    /**
     * Raw response
     *
     * @var array
     */
    protected $response;

    /**
     * Creates new resource owner.
     *
     * @param array  $response
     */
    public function __construct(array $response = array())
    {
        $this->response = $response;
    }

    /**
     * Get user id
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->getResponseData('user_id');
    }

    /**
     * Get account id
     *
     * @return string|null
     */
    public function getAccountId()
    {
        return $this->getResponseData('account_id');
    }

    /**
     * Get account name
     *
     * @return string|null
     */
    public function getAccountName()
    {
        return $this->getResponseData('account_name');
    }

    /**
     * Get account email
     *
     * @return string|null
     */
    public function getAccountEmail()
    {
        return $this->getResponseData('name');
    }

    /**
     * Get user role
     *
     * @return string|null
     */
    public function getRole()
    {
        return $this->getResponseData('role');
    }

    /**
     * Attempts to pull value from array using dot notation.
     *
     * @param string $path
     * @param string $default
     *
     * @return mixed
     */
    protected function getResponseData($path, $default = null)
    {
        return $this->getValueByKey($this->response, $path, $default);
    }

    /**
     * Return all of the owner details available as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->response;
    }
}