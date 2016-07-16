<?php


namespace cwreden\requestLimiter;

/**
 * Class RequestRateInformation
 * @package cwreden\requestLimiter
 */
class RequestRateInformation
{
    /**
     * @var string
     */
    private $uri;
    /**
     * @var int
     */
    private $limit;
    /**
     * @var int
     */
    private $used;
    /**
     * @var int
     */
    private $resetAt;

    /**
     * RequestRateInformation constructor.
     * @param string $uri
     * @param int $limit
     * @param int $used
     * @param int $resetAt
     */
    public function __construct($uri, $limit = -1, $used = 0 , $resetAt = null)
    {
        $this->limit = $limit;
        $this->used = $used;
        $this->resetAt = $resetAt;
        $this->uri = $uri;
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param string $uri
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * @return int
     */
    public function getResetAt()
    {
        return $this->resetAt;
    }

    /**
     * @param int $resetAt
     */
    public function setResetAt($resetAt)
    {
        $this->resetAt = $resetAt;
    }

    /**
     * @return int
     */
    public function getUsed()
    {
        return $this->used;
    }

    /**
     * @param int $used
     */
    public function setUsed($used)
    {
        $this->used = $used;
    }

    /**
     * TODO right place???
     */
    public function increaseUsed()
    {
        $this->used++;
    }

    /**
     * @return int
     */
    public function getRemaining()
    {
        return $this->getLimit() - $this->getUsed();
    }
}