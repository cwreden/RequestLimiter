<?php


namespace cwreden\requestLimiter;


use cwreden\requestLimiter\exception\RequestLimitExceededException;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Http\FirewallMapInterface;

class RequestLimiter implements EventSubscriberInterface
{
    /**
     * @var FirewallMapInterface
     */
    private $map;
    /**
     * @var CacheItemPoolInterface
     */
    private $cacheItemPool;
    /**
     * @var Configuration
     */
    private $defaultConfiguration;

    /**
     * RequestLimiter constructor.
     * @param FirewallMapInterface $map
     * @param CacheItemPoolInterface $cacheItemPool
     * @param Configuration $defaultConfiguration
     */
    public function __construct(
        FirewallMapInterface $map,
        CacheItemPoolInterface $cacheItemPool,
        Configuration $defaultConfiguration
    )
    {
        $this->map = $map;
        $this->cacheItemPool = $cacheItemPool;
        $this->defaultConfiguration = $defaultConfiguration;
    }

    /**
     * @param GetResponseEvent $event
     * @throws \HttpRequestException
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        $pathInfo = $request->getPathInfo();

        $cacheItem = $this->cacheItemPool->getItem($pathInfo);

        // @TODO authenticated
        // @TODO RequestMatcher
        $now = time();
        if ($cacheItem->get() === null) {
            $rateInformation = $this->createRequestRateInformation($pathInfo, $now);
        } else {
            $rateInformation = unserialize($cacheItem->get());
        }

        if ($rateInformation->getResetAt() < $now) {
            $rateInformation = $this->createRequestRateInformation($pathInfo, $now);
        }

        if ($rateInformation->getLimit() === -1) {
            return;
        }

        try {
            $rateInformation->increaseUsed();
            $cacheItem->set(serialize($rateInformation));
            $cacheItem->expiresAt($rateInformation->getResetAt());

            $this->cacheItemPool->save($cacheItem);
        } catch (RequestLimitExceededException $e) {
            $requestRateInformation = $e->getRequestRateInformation();

            $event->setResponse(new Response('', 403, array(
                'X-RequestLimit-Limit' => $requestRateInformation->getLimit(),
                'X-RequestLimit-Remaining' => $requestRateInformation->getRemaining(),
                'X-RequestLimit-Reset' => $requestRateInformation->getResetAt(),
                'X-RequestLimit-Now' => $now
            )));
            $event->stopPropagation();
            // @TODO json error response
        }
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $pathInfo = $request->getPathInfo();

        $cacheItem = $this->cacheItemPool->getItem($pathInfo);

        if ($cacheItem->get() === null) {
            var_dump($cacheItem->get());
            return;
        }

        $rateInformation = unserialize($cacheItem->get());
        if ($rateInformation->getLimit() === -1) {
            return;
        }

        $response = $event->getResponse();
        $response->headers->add(array(
            'X-RequestLimit-Limit' => $rateInformation->getLimit(),
            'X-RequestLimit-Remaining' => $rateInformation->getRemaining(),
            'X-RequestLimit-Reset' => $rateInformation->getResetAt(),
            'requestPath' => $pathInfo
        ));
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array('onKernelRequest', 7),
            KernelEvents::RESPONSE => 'onKernelResponse',
        );
    }

    /**
     * @param $pathInfo
     * @param $now
     * @return RequestRateInformation
     */
    private function createRequestRateInformation($pathInfo, $now): RequestRateInformation
    {
        $rateInformation = new RequestRateInformation($pathInfo, $this->defaultConfiguration->getLimit());
        $rateInformation->setResetAt($now + $this->defaultConfiguration->getResetInterval());
        return $rateInformation;
    }
}
