<?php


namespace cwreden\requestLimiter;


class Configuration
{
    /**
     * @var int
     */
    private $limit;
    /**
     * @var int
     */
    private $limitAuthenticated;
    /**
     * @var int
     */
    private $resetInterval;

    /**
     * Configuration constructor.
     * @param int $limit
     * @param int $limitAuthenticated
     * @param int $resetInterval
     */
    public function __construct(
        $limit,
        $limitAuthenticated,
        $resetInterval
    )
    {
        $this->limit = $limit;
        $this->limitAuthenticated = $limitAuthenticated;
        $this->resetInterval = $resetInterval;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @return int
     */
    public function getLimitAuthenticated(): int
    {
        return $this->limitAuthenticated;
    }

    /**
     * @return int
     */
    public function getResetInterval(): int
    {
        return $this->resetInterval;
    }

}