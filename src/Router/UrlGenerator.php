<?php

namespace Kaliop\AdrBundle\Router;


use Symfony\Component\Routing\Router;

class UrlGenerator
{
    /** @var Router */
    protected $router;

    /**
     * AbstractResponder constructor.
     *
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Generate absolute urls
     *
     * @param string $name
     * @param array $parameters
     * @return string
     */
    public function absoluteUrl(string $name, array $parameters = [])
    {
        return $this->generateUrl($name, $parameters, Router::ABSOLUTE_URL);
    }

    /**
     * @param string $name
     * @param array $parameters
     * @param int $referenceType
     * @return string
     */
    public function generateUrl(string $name, array $parameters = [], int $referenceType = Router::ABSOLUTE_URL)
    {
        return $this->router->generate($name, $parameters, $referenceType);
    }}
