<?php


namespace cwreden\requestLimiter;

use cwreden\requestLimiter\cache\SessionCache;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\EventListenerProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;


/**
 * Class RequestRateLimitServiceProvider
 * @package OpenCoders\Podb\Provider
 *
 * TODO exclude routes or check only selected routes
 */
class RequestLimiterServiceProvider implements ServiceProviderInterface, EventListenerProviderInterface
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $pimple A container instance
     */
    public function register(Container $pimple)
    {
        if (!isset($pimple[RequestLimiterConfigurations::DEFAULT_REQUEST_LIMIT])) {
            $pimple[RequestLimiterConfigurations::DEFAULT_REQUEST_LIMIT] = 100;
        }

        if (!isset($pimple[RequestLimiterConfigurations::DEFAULT_REQUEST_LIMIT_AUTHENTICATED])) {
            $pimple[RequestLimiterConfigurations::DEFAULT_REQUEST_LIMIT_AUTHENTICATED] = 10000;
        }

        if (!isset($pimple[RequestLimiterConfigurations::DEFAULT_REQUEST_LIMIT_RESET_INTERVAL])) {
            $pimple[RequestLimiterConfigurations::DEFAULT_REQUEST_LIMIT_RESET_INTERVAL] = 3600;
        }


        $pimple[RequestLimiterServices::DEFAULT_CONFIGURATION] = function (Container $container) {
            return new Configuration(
                $container[RequestLimiterConfigurations::DEFAULT_REQUEST_LIMIT],
                $container[RequestLimiterConfigurations::DEFAULT_REQUEST_LIMIT_AUTHENTICATED],
                $container[RequestLimiterConfigurations::DEFAULT_REQUEST_LIMIT_RESET_INTERVAL]
            );
        };

        $pimple->extend('session', function ($session) {
            /** @var SessionInterface $session */
            $requestLimiterBag = new AttributeBag(SessionCache::CACHE_STORAGE_KEY);
            $requestLimiterBag->setName(SessionCache::CACHE_BAG_NAME);
            $session->registerBag($requestLimiterBag);

            return $session;
        });


        $pimple[RequestLimiterServices::CACHE_SESSION] = function (Container $container) {
            return new SessionCache(
                $container['session']
            );
        };

        $pimple['requestLimiter.map'] = function () {
            return new RequestLimiterMap();
        };

        $pimple[RequestLimiterServices::LIMITER] = function (Container $container) {
            return new RequestLimiter(
                $container['requestLimiter.map'],
                $container[RequestLimiterServices::CACHE_SESSION],
                $container[RequestLimiterServices::DEFAULT_CONFIGURATION]
            );
        };
    }

    /**
     * @param Container $app
     * @param EventDispatcherInterface $dispatcher
     */
    public function subscribe(Container $app, EventDispatcherInterface $dispatcher)
    {
        $dispatcher->addSubscriber($app[RequestLimiterServices::LIMITER]);
    }
}
