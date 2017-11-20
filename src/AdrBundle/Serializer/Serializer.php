<?php

namespace Kaliop\AdrBundle\Serializer;


use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer as BaseSerializer;

class Serializer
{
    /** @var Serializer */
    protected $serializer;

    /**
     * Serializer constructor.
     */
    public function __construct()
    {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer($classMetadataFactory));

        $this->serializer = new BaseSerializer($normalizers, $encoders);
    }

    public function getSerializer() : BaseSerializer
    {
        return $this->serializer;
    }

    /**
     * @param mixed $obj
     * @param string|null $format
     * @param array $context
     * @return mixed
     */
    public function normalize($obj, string $format = null, array $context = [])
    {
        return $this->serializer->normalize($obj, $format, $context);
    }

    /**
     * @param $obj
     * @param string|null $format
     * @param array $context
     * @return mixed
     */
    public function serialize($obj, string $format = null, array $context = [])
    {
        return $this->serializer->serialize($obj, $format, $context);
    }
}
