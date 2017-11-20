<?php


namespace Kaliop\AdrBundle\EventListener;


use ApiBundle\Response\ContentNegotiator;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class ExceptionListener
 * @package AppBundle\EventListener
 */
class ExceptionListener implements EventSubscriberInterface
{
    /** @var ContentNegotiator */
    protected $negotiator;

    /** @var LoggerInterface */
    protected $logger;

    /** @var bool */
    protected $isDebug;

    /**
     * ExceptionListener constructor.
     * @param ContentNegotiator $negotiator
     * @param LoggerInterface $logger
     * @param KernelInterface $kernel
     */
    public function __construct(
        ContentNegotiator $negotiator,
        LoggerInterface $logger,
        KernelInterface $kernel
    )
    {
        $this->isDebug = $kernel->isDebug();
        $this->negotiator = $negotiator;
        $this->logger = $logger;
    }

    /**
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    /**
     * @param GetResponseForExceptionEvent $event
     * @return Response
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if ($this->isDebug) {
            return;
        }

        $event->allowCustomResponseCode();
        $exception = $event->getException();

        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            $message = $exception->getMessage();
        } elseif ($exception instanceof \Swift_TransportException) {
            $statusCode = 1;
            $message = 'email.not_sent';
        } else {
            $statusCode = $exception->getCode();
            $message = $exception->getMessage();
        }

        if (!in_array($statusCode, array_keys(Response::$statusTexts))) {
            $statusCode = 500;
        }

        $data = [
            'data' => [
                'message' => isset($message) ? $message : $exception->getMessage(),
            ],
        ];

        $this->logger->error(sprintf(
            '%s %s in file %s at line %s',
            $statusCode,
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        ));

        $event->setResponse($this->negotiator->negotiate($data, $statusCode));
    }
}
