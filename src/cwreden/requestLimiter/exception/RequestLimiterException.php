<?php

namespace cwreden\requestLimiter\exception;


use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class RequestLimiterException extends AccessDeniedHttpException
{
    
}
