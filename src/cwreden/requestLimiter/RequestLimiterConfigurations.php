<?php


namespace cwreden\requestLimiter;


class RequestLimiterConfigurations
{
    const DEFAULT_REQUEST_LIMIT = 'requestLimiter.config.global.limit';
    const DEFAULT_REQUEST_LIMIT_AUTHENTICATED = 'requestLimiter.config.global.limitAuthenticated';
    const DEFAULT_REQUEST_LIMIT_RESET_INTERVAL = 'requestLimiter.config.global.limitResetInterval';
}