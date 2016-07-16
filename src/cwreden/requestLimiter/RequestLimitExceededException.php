<?php


namespace cwreden\requestLimiter;


class RequestLimitExceededException extends RequestLimiterException
{
    /**
     * @var RequestRateInformation
     */
    private $requestRateInformation;

    /**
     * RateLimitException constructor.
     * @param RequestRateInformation $requestRateInformation
     */
    public function __construct(RequestRateInformation $requestRateInformation)
    {
        parent::__construct('Max request limit exceeded!');
        $this->requestRateInformation = $requestRateInformation;
    }

    /**
     * @return RequestRateInformation
     */
    public function getRequestRateInformation()
    {
        return $this->requestRateInformation;
    }
}