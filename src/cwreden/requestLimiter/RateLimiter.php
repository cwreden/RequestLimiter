<?php

namespace cwreden\requestLimiter;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class RateLimiter
 * @package cwreden\requestLimiter
 */
class RateLimiter
{
    /**
     * @var int
     */
    private $limit;
    /**
     * @var int
     */
    private $authenticatedLimit;
    /**
     * @var int
     */
    private $resetInterval;
    /**
     * @var SessionInterface
     */
    private $session;
    /**
     * @var array
     */
    private $securedUris;
    /**
     * @var array
     */
    private $excludedUris;

    /**
     * @param SessionInterface $session
     * @param $limit
     * @param $authenticatedLimit
     * @param $resetInterval
     * @param $securedUris
     * @param $excludedUris
     */
    public function __construct(
        SessionInterface $session,
        $limit,
        $authenticatedLimit,
        $resetInterval,
        $securedUris,
        $excludedUris
    ) {
        $this->session = $session;
        $this->limit = $limit;
        $this->authenticatedLimit = $authenticatedLimit;
        $this->resetInterval = $resetInterval;
        $this->securedUris = $securedUris;
        $this->excludedUris = $excludedUris;
    }

    /**
     * @return int
     */
    private function getLimit()
    {
        return $this->limit;
    }

    /**
     * @return int
     */
    private function getAuthenticatedLimit()
    {
        return $this->authenticatedLimit;
    }

    /**
     * @return int
     */
    private function getResetInterval()
    {
        return $this->resetInterval;
    }

    /**
     * TODO implement load
     * @param $uri
     * @return RequestRateInformation
     */
    protected function getRateInformation($uri)
    {
//        if (strpos($pathInfo, '/api') !== 0) {// TODO
//            return;
//        }

        // TODO check uri

//        $rate = $this->session->get('rateLimit'); // TODO load if 
        $requestRateInformation = new RequestRateInformation($uri, 0, 0, 0);
//        if (!is_array($rate)) {
//            $rate = array(
//                'reset_at' => (time() + $this->resetInterval),
//                'used' => 0
//            );
//            $requestRateInformation = new RequestRateInformation(0, time() + $this->resetInterval);
//        }

        $actualTime = time();
        if ($requestRateInformation->getResetAt() - $actualTime <= 0) {
            $requestRateInformation->setResetAt($actualTime + $this->getResetInterval());
            $requestRateInformation->setUsed(0);
//            $rate['reset_at'] = ($actualTime + $this->resetInterval);
//            $rate['used'] = 0;
        }

        return $requestRateInformation;
    }

    /**
     * @return int
     */
    private function getActiveLimit()
    {
        $limit = $this->getLimit();
        // TODO use symfony security layer
        if ($this->session->get('authenticated') === true) {
            $limit = $this->getAuthenticatedLimit();
        }
        return $limit;
    }

    /**
     * @param $uri
     */
    public function increaseUriUsage($uri)
    {
        $rateInformation = $this->getRateInformation($uri);
        if ($rateInformation->getLimit() === 0) {
            return;
        }

        if ($rateInformation->getRemaining() <= 0) {
            throw new RequestLimitExceededException($rateInformation);
        }
        $rateInformation->increaseUsed();
//        $this->session->set('rateLimit', $rateInformation); // TODO save

    }

    /**
     * @param $uri
     * @return RequestRateInformation
     */
    public function get($uri)
    {
        return $this->getRateInformation($uri);
    }
}
