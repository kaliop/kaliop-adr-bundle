<?php


namespace Kaliop\AdrBundle\Response;


use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactory;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactoryInterface;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArgumentResolver
{
    /** @var ArgumentMetadataFactory  */
    protected $argumentMetadataFactory;

    /**
     * ArgumentResolver constructor.
     * @param ArgumentMetadataFactoryInterface|null $argumentMetadataFactory
     * @param array $argumentValueResolvers
     */
    public function __construct(ArgumentMetadataFactoryInterface $argumentMetadataFactory = null, $argumentValueResolvers = array())
    {
        $this->argumentMetadataFactory = $argumentMetadataFactory ?: new ArgumentMetadataFactory();
    }

    /**
     * @param $responder
     * @param array $args
     * @return array
     */
    public function getArguments($responder, array $args)
    {
        $argumentMetadata = $this->argumentMetadataFactory->createArgumentMetadata($responder);
        try {
            $args = $this->resolve($argumentMetadata, $args);
        } catch (UndefinedOptionsException $e) {
            throw new UndefinedOptionsException($this->handleExceptionMessage($responder, $e));
        } catch (MissingOptionsException $e) {
            throw new MissingOptionsException($this->handleExceptionMessage($responder, $e));
        }

        $arguments = [];
        /** @var ArgumentMetadata $metadata */
        foreach ($argumentMetadata as $metadata) {
            $arguments[] = $args[$metadata->getName()];
        }

        return $arguments;
    }

    /**
     * @param $responder
     * @param \Exception $e
     * @return string
     */
    protected function handleExceptionMessage($responder, \Exception $e)
    {
        return sprintf(
            'An exception occured while invoking %s: %s',
            get_class($responder),
            lcfirst($e->getMessage())
        );
    }


    /**
     * @param array|ArgumentMetadata[] $argumentMetadata
     * @param array $args
     * @return array
     */
    private function resolve(array $argumentMetadata, array $args) : array
    {
        $resolver = new OptionsResolver();

        foreach ($argumentMetadata as $metadata) {

            if (false === $metadata->isNullable()) {
                $resolver->setRequired($metadata->getName());
            } else {
                $resolver->setDefined($metadata->getName());
            }

            if ($metadata->hasDefaultValue()) {
                $resolver->setDefault($metadata->getName(), $metadata->getDefaultValue());
            }

            $resolver->setAllowedTypes($metadata->getName(), $metadata->getType());
        }

        return $resolver->resolve($args);
    }
}
