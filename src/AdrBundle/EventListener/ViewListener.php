<?php

namespace Kaliop\AdrBundle\EventListener;


use ApiBundle\Response\ArgumentResolver;
use ApiBundle\Response\ContentNegotiator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ViewListener implements EventSubscriberInterface
{
    /** @var ContainerInterface */
    private $container;

    /** @var ContentNegotiator */
    private $contentNegotiator;

    /** @var ArgumentResolver */
    private $argumentResolver;

    /**
     * ViewListener constructor.
     * @param ContainerInterface $container
     * @param ContentNegotiator $contentNegotiator
     * @param ArgumentResolver $argumentResolver
     */
    public function __construct(
        ContainerInterface $container,
        ContentNegotiator $contentNegotiator,
        ArgumentResolver $argumentResolver
    )
    {
        $this->container = $container;
        $this->contentNegotiator = $contentNegotiator;
        $this->argumentResolver = $argumentResolver;
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
        return [
            KernelEvents::VIEW => 'onKernelView',
        ];
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $responder = $this->container->get($event->getRequest()->get('_responder'));
        $args = $this->argumentResolver->getArguments($responder, $event->getControllerResult());
        $response = call_user_func_array($responder, $args);

        if ($response instanceof Response) {
            $event->setResponse($response);
        } else {
            $event->setResponse($this->contentNegotiator->negotiate($response));
        }
    }
}
