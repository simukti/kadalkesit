## KADALKESIT

Kadalkesit is a skinny skeleton of [PSR-7](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-7-http-message.md) middleware wrapper using [Zend Diactoros](https://github.com/zendframework/zend-diactoros), [Zend Stratigility](https://github.com/zendframework/zend-stratigility), and [FastRoute](https://github.com/nikic/FastRoute). It will only dispatch a request to a callable action (should be small and fast enough), and the rest is yours.

------

### USAGE

#### ROUTE DEFINITION
Each route definition contains only 3 required keys (method, path, action), here's the basic sample :

```php
<?php
return [
    [
        'method'    => 'GET',
        'path'      => '/',
        'action'    => Action\Index::class,
    ],
    [
        'method'    => 'GET',
        'path'      => '/user',
        'action'    => Action\UserList::class,
    ],
    [
        'method'    => 'GET',
        'path'      => '/user/{username:[A-Za-z0-9_]{1,20}$}',
        'action'    => Action\UserView::class,
    ],
];
```

#### ACTION HANDLER
Action handler must be callable, this basic sample below is a invokable action class :

```php
<?php
namespace Action;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\JsonResponse;

class Index
{
    /**
     * @param   RequestInterface    $request
     * @param   ResponseInterface   $response
     * @param   array               $routeParams
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, array $routeParams = [])
    {
        return new JsonResponse([__METHOD__, $routeParams]);
    }
}
```

#### HOW IT HAPPENS ?

Simple, routes handling managed by `RouteManager` middleware which implements `Zend\Stratigility\MiddlewareInterface`, attached to main pipe (`Zend\Stratigility\MiddlewarePipe`) and return `Psr\Http\Message\ResponseInterface` or throw an exception. `Zend\Diactoros\Server` will act as a server, listen to http request and set final handler.

#### TODO

- group routing ?
- optional route params ?
- default value route params ?
- routes cache ?
- fix readme's spelling and its grammar :blush: ?

**FEEL FREE TO CONTRIBUTE**

#### LICENSE

BSD-3-Clause

```
Copyright (c) 2015 by Sarjono Mukti Aji.

Some rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are
met:

    * Redistributions of source code must retain the above copyright
      notice, this list of conditions and the following disclaimer.

    * Redistributions in binary form must reproduce the above
      copyright notice, this list of conditions and the following
      disclaimer in the documentation and/or other materials provided
      with the distribution.

    * The names of the contributors may not be used to endorse or
      promote products derived from this software without specific
      prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
"AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
```