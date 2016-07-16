<?php

namespace cwreden\requestLimiter;


use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class RequestLimiterException extends AccessDeniedHttpException
{
    
}
