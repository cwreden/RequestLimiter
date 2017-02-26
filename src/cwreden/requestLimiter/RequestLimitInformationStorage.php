<?php


namespace cwreden\requestLimiter;


class RequestLimitInformationStorage
{
    /**
     * @param $uri
     * @return RequestRateInformation
     */
    public function get($uri)
    {
        // TODO load from session
        return new RequestRateInformation('', 0, 0, 0);
    }
}