<?php

namespace Kaliop\AdrBundle\Response;


use Kaliop\AdrBundle\Serializer\Serializer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ContentNegociator
 */
class ContentNegotiator
{
    const JSON = 'json';
    const XML = 'xml';

    /** @var null|\Symfony\Component\HttpFoundation\Request */
    private $request;

    /** @var Serializer  */
    private $serializer;

    /**
     * ContentNegotiator constructor.
     * @param RequestStack $requestStack
     * @param Serializer $serializer
     */
    public function __construct(RequestStack $requestStack, Serializer $serializer)
    {
        $this->request = $requestStack->getMasterRequest();
        $this->serializer = $serializer;
    }

    /**
     * @param array $data
     * @return Response
     * @throws \Exception
     */
    public function negotiate(array $data) : Response
    {
        $data = $this->resolve($data);
        $accept = $this->request->getAcceptableContentTypes();

        $response = null;

        if (count(array_intersect(['application/json', 'application/x-json'], $accept)) > 0) {
            $content = $this->serializer->normalize(
                $data['data'],
                self::JSON,
                $this->getSerializationGroups($data)
            );

            $response = new JsonResponse($content);
        }

        if (count(array_intersect(['application/xml'], $accept)) > 0) {
            $content = $data['data'];
            $response = new Response(
                $this->serializer->serialize($content, self::XML, $this->getSerializationGroups($data))
            );
        }

        if (!$response) {
            $response = new Response(serialize($data));
        }

        $response->setStatusCode($data['status_code']);

        return $response;
    }

    /**
     * @param array $data
     * @return array
     * @throws \Exception
     */
    private function getSerializationGroups(array $data) : array
    {
        if (!isset($data['serialization_groups'])) {
            return [];
        }

        $serializationGroups = $data['serialization_groups'];
        if (!is_array($serializationGroups)) {
            if (!is_string($serializationGroups)) {
                throw new \Exception('Value of key serialization_groups should be a string or an array of strings');
            }
            $serializationGroups = [$serializationGroups];
        }

        return ['groups' => $serializationGroups];
    }

    /**
     * @param array $data
     * @return array
     */
    protected function resolve(array $data) : array
    {
        return (new OptionsResolver())
            ->setRequired(['data'])
            ->setDefined(['serialization_groups'])
            ->setDefaults([
                'status_code' => Response::HTTP_OK,
            ])
            ->setAllowedTypes('data', 'array')
            ->setAllowedTypes('serialization_groups', ['string', 'array'])
            ->setAllowedTypes('status_code', 'int')
            ->setAllowedValues('status_code', array_keys(Response::$statusTexts))
            ->resolve($data);
    }
}
