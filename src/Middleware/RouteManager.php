<?php
namespace Middleware;

use Zend\Stratigility\MiddlewareInterface;
use Zend\Stratigility\Http\Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use FastRoute\Dispatcher;
use FastRoute\Dispatcher\GroupCountBased as GroupCountBasedDispatcher;
use FastRoute\DataGenerator\GroupCountBased as GroupCountBasedGenerator;
use FastRoute\RouteParser\Std;
use FastRoute\RouteCollector;
use function FastRoute\cachedDispatcher;
use function FastRoute\simpleDispatcher;

/**
 * Middleware\RouteManager
 *
 * @author Sarjono Mukti Aji <me@simukti.net>
 */
class RouteManager implements MiddlewareInterface
{
    /**
     * Fastroute dispatcher configs
     * 
     * @var array
     */
    protected $routerOptions = [
        'routeParser'   => Std::class,
        'dataGenerator' => GroupCountBasedGenerator::class,
        'dispatcher'    => GroupCountBasedDispatcher::class,
        'routeCollector' => RouteCollector::class,
        'cacheFile'     => null,
        'cacheDisabled' => true
    ];
    
    /**
     * Routes definition list
     * 
     * @var array
     */
    protected $routes = [];
    
    /**
     * @var Dispatcher
     */
    protected $dispatcher;
    
    /**
     * Add routes definition to collector and dispatch it
     * 
     * @param   array   $routes
     * @param   array   $routerOptions
     */
    public function __construct(array $routes, array $routerOptions = [])
    {
        $this->routes        = $routes;
        $this->routerOptions = array_merge(
            $this->routerOptions, 
            $routerOptions
        );
        
        if(! $this->routerOptions['cacheDisabled']) {
            $this->dispatcher = cachedDispatcher(
                $this->prepareRoutes(), 
                $this->routerOptions
            );
        } else {
            $this->dispatcher = simpleDispatcher(
                $this->prepareRoutes(), 
                $this->routerOptions
            );
        }
    }
    
    /**
     * @param   ServerRequestInterface  $request
     * @param   ResponseInterface       $response
     * @param   callable                $out
     */
    public function __invoke(
        ServerRequestInterface $request, 
        ResponseInterface $response, 
        callable $out = null
    ) {
        $path     = $request->getUri()->getPath();
        $endSlash = substr($path, -1);
        
        if(strlen($path) > 1 && $endSlash === '/') {
            // redirect *example* /tag/ --> /tag
            return $response->withHeader(
                'Location', 
                rtrim($request->getUri()->getPath(), '/')
            );
        }
        
        $currentRoute = $this->dispatcher->dispatch(
            $request->getMethod(), 
            $request->getUri()->getPath()
        );
        
        switch ($currentRoute[0]) {
            case Dispatcher::METHOD_NOT_ALLOWED:
                throw new \UnexpectedValueException('Method not allowed');
            case Dispatcher::NOT_FOUND:
                throw new \OutOfRangeException('Route not found');
            case Dispatcher::FOUND:
                $response = $this->handleRoute(
                    $request, 
                    $response, 
                    $currentRoute
                );
                break;
        }
        
        if(! $response instanceof ResponseInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Action handler must return '%s' instance",
                    ResponseInterface::class
                )
            );
        }
        
        $response = ($response instanceof Response)?: new Response($response);
        
        return $out($request, $response);
    }
    
    /**
     * Handle matched route
     * 
     * @param   ServerRequestInterface  $request
     * @param   ResponseInterface       $response
     * @param   array                   $currentRoute
     * @return  ResponseInterface
     * @throws  \OutOfBoundsException
     */
    protected function handleRoute(
        ServerRequestInterface $request, 
        ResponseInterface $response, 
        array $currentRoute
    ) {
        $actionClass    = $currentRoute[1];
        $routeParams    = $currentRoute[2];
        
        if(is_callable($actionClass)) {
            // action handler is callable, return it immediately
            return $actionClass($request, $response, $routeParams);
        }
        
        if(! class_exists($actionClass)) {
            throw new \OutOfBoundsException(
                sprintf(
                    "action class '%s' does not exists", 
                    $actionClass
                )
            );
        }
        
        $actionHandler  = new $actionClass;
        
        return $actionHandler($request, $response, $routeParams);
    }
    
    /**
     * Collect routes to RouteCollector
     * 
     * @return  Closure
     */
    protected function prepareRoutes()
    {
        $routeCollector = function(RouteCollector $router) {
            foreach($this->routes as $route) {
                if(is_string($route['method'])) {
                    $method = trim(strtoupper($route['method']));
                } elseif(is_array($route['method'])) {
                    $method = array_map(function($value) {
                        return trim(strtoupper($value));
                    }, $route['method']);
                } else {
                    throw new \InvalidArgumentException(
                        'Invalid method'
                    );
                }
                
                if($method === 'ALL') {
                    $method = [
                        'GET', 'POST', 'PUT', 'DELETE'
                    ];
                }
                
                if(strlen($route['path']) > 1) {
                    $route['path'] = rtrim($route['path'], '/');
                }
                
                $router->addRoute($method, $route['path'], $route['action']);
            }
        };
        
        return $routeCollector;
    }
}