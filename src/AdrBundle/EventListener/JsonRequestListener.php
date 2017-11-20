<?php


namespace Kaliop\AdrBundle\EventListener;


use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class JsonRequestListener
 * @package AppBundle\EventListener
 */
class JsonRequestListener implements EventSubscriberInterface
{
    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (false === $event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        if ($request->getContentType() !== 'json' || $request->getMethod() === Request::METHOD_GET) {
            return;
        }

        $content = json_decode($event->getRequest()->getContent(), true);
        if (null === $content) {
            throw new BadRequestHttpException("Invalid JSON");
        }

        if (!is_array($content)) {
            $content = [$content];
        }

        $event->getRequest()->request->add($content);
    }

    /**
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 40],
        ];
    }
}
