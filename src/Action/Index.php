<?php
namespace Action;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Action\Index
 *
 * @author Sarjono Mukti Aji <me@simukti.net>
 */
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