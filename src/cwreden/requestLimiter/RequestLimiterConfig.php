<?php


namespace cwreden\requestLimiter;


class RequestLimiterConfig
{
    const REQUEST_LIMIT = 'requestLimiter.config.limit';
    const AUTHENTICATED_REQUEST_LIMIT = 'requestLimiter.config.authenticatedLimit';
    const REQUEST_LIMIT_RESET_INTERVAL = 'requestLimiter.config.resetInterval';
    const REQUEST_LIMIT_SECURED_URIS = 'requestLimiter.config.secured_uris';
    const REQUEST_LIMIT_EXCLUDED_URIS = 'requestLimiter.config.excluded_uris';
}