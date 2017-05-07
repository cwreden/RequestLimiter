<?php


namespace cwreden\requestLimiter;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * Class RequestRateLimitServiceProvider
 * @package OpenCoders\Podb\Provider
 *
 * TODO exclude routes or check only selected routes
 */
class RequestLimiterServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given app.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Application $app An Application instance
     */
    public function register(Application $app)
    {
        if (!isset($app[RequestLimiterConfig::REQUEST_LIMIT_SECURED_URIS])) {
            $app[RequestLimiterConfig::REQUEST_LIMIT_SECURED_URIS] = array();
        }
        
        if (!isset($app[RequestLimiterConfig::REQUEST_LIMIT_EXCLUDED_URIS])) {
            $app[RequestLimiterConfig::REQUEST_LIMIT_EXCLUDED_URIS] = array();
        }

        if (!isset($app[RequestLimiterConfig::REQUEST_LIMIT])) {
            $app[RequestLimiterConfig::REQUEST_LIMIT] = 1000;
        }

        if (!isset($app[RequestLimiterConfig::AUTHENTICATED_REQUEST_LIMIT])) {
            $app[RequestLimiterConfig::AUTHENTICATED_REQUEST_LIMIT] = 10000;
        }

        if (!isset($app[RequestLimiterConfig::REQUEST_LIMIT_RESET_INTERVAL])) {
            $app[RequestLimiterConfig::REQUEST_LIMIT_RESET_INTERVAL] = 3600;
        }
        
        $app[RequestLimiterServices::RATE_LIMITER] = function ($pimple) {
            return new RateLimiter(
                $pimple['session'],
                $pimple[RequestLimiterConfig::REQUEST_LIMIT],
                $pimple[RequestLimiterConfig::AUTHENTICATED_REQUEST_LIMIT],
                $pimple[RequestLimiterConfig::REQUEST_LIMIT_RESET_INTERVAL],
                $pimple[RequestLimiterConfig::REQUEST_LIMIT_SECURED_URIS],
                $pimple[RequestLimiterConfig::REQUEST_LIMIT_EXCLUDED_URIS]
            );
        };
    }

    /**
     * Bootstraps the application.
     *
     * This method is called after all services are registered
     * and should be used for "dynamic" configuration (whenever
     * a service must be requested).
     * @param Application $app
     */
    public function boot(Application $app)
    {
        $app->before(function (Request $request) use ($app) {
            $pathInfo = $request->getPathInfo();

            /** @var RateLimiter $rateLimiter */
            $rateLimiter = $app[RequestLimiterServices::RATE_LIMITER];

            try {
                $rateLimiter->increaseUriUsage($pathInfo);
            } catch (RequestLimitExceededException $e) {
                $requestRateInformation = $e->getRequestRateInformation();
                $app->abort(403, $e->getMessage(), array(
                    'X-RequestLimit-Limit' => $requestRateInformation->getLimit(),
                    'X-RequestLimit-Remaining' => $requestRateInformation->getRemaining(),
                    'X-RequestLimit-Reset' => $requestRateInformation->getResetAt(),
                ));
            }
        }, 128);

        $app->after(function (Request $request, Response $response) use ($app) {
            $pathInfo = $request->getPathInfo();

            /** @var RateLimiter $rateLimiter */
            $rateLimiter = $app[RequestLimiterServices::RATE_LIMITER];
            $rateLimitInformation = $rateLimiter->get($pathInfo);

            if ($rateLimitInformation->getLimit() === -1) {
                return;
            }

            $response->headers->add(array(
                'X-RequestLimit-Limit' => $rateLimitInformation->getLimit(),
                'X-RequestLimit-Remaining' => $rateLimitInformation->getRemaining(),
                'X-RequestLimit-Reset' => $rateLimitInformation->getResetAt(),
            ));
        }, Application::LATE_EVENT);
    }
}
