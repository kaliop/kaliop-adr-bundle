# Kaliop ADR Bundle

> Action–domain–responder (ADR) is a software architectural pattern that was 
> proposed by Paul M. Jones[1] as a refinement of Model–view–controller (MVC) 
> that is better suited for web applications. 
> ADR was devised to match the request-response flow of HTTP communications 
> more closely than MVC, which was originally designed for desktop software applications.
>
> *from [Wikipedia](https://en.wikipedia.org/wiki/Action%E2%80%93domain%E2%80%93responder "https://en.wikipedia.org/wiki/Action%E2%80%93domain%E2%80%93responder")*

This bundle simplifies the setup of the ADR in a Symfony project. 


## Installation

### Configure repository
```bash
$ php composer.phar config repositories.kaliopAdrBundle '{ "type": "vcs", "url": "ssh://git@github.com:kaliop/kaliop-adr-bundle.git" }'
```
### Install library
```bash
$ php composer.phar require kaliop/adr-bundle
```
### Remove library
```bash
$ php composer.phar remove kaliop/adr-bundle
```

### Add bundle to AppKernel
```php
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            ...
            new Kaliop\AdrBundle\AdrBundle(),
            ...
        ];
    }
}
```

# Usage

## Routing

```yaml
post:
  path: /posts/{id}
  defaults:
    _controller: AppBundle\Controller\ViewPostAction
    _responder: AppBundle\Responder\ViewPostResponder
  methods: ["GET"]
```

## Action (Controller)
The action must return an associative array that will be pass to the responder `__invoke` method.
Each key of the array must match an argument of the responder `__invoke` method signature, 
otherwise an exception will be thrown. The order of the arguments in the array is not important.

```php
<?php

namespace AppBundle\Controller;

use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\Request;

class ViewPostAction
{
    public function __invoke(
        Request $request,
        RegistryInterface $doctrine
    )
    {
        // Get the ID of the post to be displayed
        $id = $request->attributes->get('id');
        $post = $doctrine->getRepository('AppBundle:Post')->find($id);

        if (!$post) {
            throw new EntityNotFoundException('Entity not found');
        }

        return [
            'post' => $post,
        ];
    }
}
```
## Responder
The responder can either:
* directly return an instance of `Symfony\Component\HttpFoundation\Response` (e.g. when you return a response containing HTML generated with Twig)
* an array of data to be serialized in the response (mostly the case when you're building an API that returns Json or XML). In that case you can specify serialization groups.
 
```php
<?php

namespace AppBundle\Responder;


use AppBundle\Entity\Post;

class ViewPostResponder
{
    /**
     * @param Post $post
     * @return array
     */
    public function __invoke(Post $post)
    {
        return [
            'data' => [
                'post' => $post,
            ],
            'serialization_groups' => 'view',
        ];
    }
}
```
