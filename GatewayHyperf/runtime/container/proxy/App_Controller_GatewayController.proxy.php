<?php

declare (strict_types=1);
namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Guzzle\ClientFactory;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
class GatewayController extends AbstractController
{
    use \Hyperf\Di\Aop\ProxyTrait;
    use \Hyperf\Di\Aop\PropertyHandlerTrait;
    private ClientFactory $clientFactory;
    // Service URLs - these should match your docker-compose service names
    private const SERVICE_URLS = ['account' => 'http://account:80', 'payment' => 'http://payment:80', 'order' => 'http://order:80'];
    public function __construct(ClientFactory $clientFactory)
    {
        $this->__handlePropertyHandler(__CLASS__);
        $this->clientFactory = $clientFactory;
    }
    /**
     * Route to account service
     */
    public function account(RequestInterface $request, ResponseInterface $response)
    {
        return $this->proxyRequest($request, $response, 'account');
    }
    /**
     * Route to payment service
     */
    public function payment(RequestInterface $request, ResponseInterface $response)
    {
        return $this->proxyRequest($request, $response, 'payment');
    }
    /**
     * Route to order service
     */
    public function order(RequestInterface $request, ResponseInterface $response)
    {
        $data = $this->proxyRequest($request, $response, 'order');
        return $response->withStatus($data->getStatusCode())
            ->withBody($data->getBody())
            ->withHeaders($data->getHeaders())
            ->withHeader('Content-Length', strlen($data->getBody()->getContents()));
    }
    /**
     * Generic proxy method to forward requests to microservices
     */
    private function proxyRequest(RequestInterface $request, ResponseInterface $response, string $service) : PsrResponseInterface
    {
        $serviceUrl = self::SERVICE_URLS[$service];
        // Get the path after the service name (e.g., /order/api/health -> /api/health)
        $path = $request->getPathInfo();
        $path = preg_replace('/^\\/' . $service . '/', '', $path);
        if (empty($path) || $path === '/') {
            $path = '/';
        }
        $targetUrl = rtrim($serviceUrl, '/') . $path;
        // Debug logging
        error_log("Gateway Debug - Service: {$service}, ServiceURL: {$serviceUrl}, Path: {$path}, TargetURL: {$targetUrl}");
        // Get query parameters
        $queryParams = $request->getQueryParams();
        if (!empty($queryParams)) {
            $targetUrl .= '?' . http_build_query($queryParams);
        }
        // Prepare headers (exclude host and connection headers)
        $headers = $request->getHeaders();
        unset($headers['host'], $headers['connection']);
        // Prepare request options
        $options = ['headers' => $headers, 'timeout' => 30];
        // Add request body for POST/PUT/PATCH requests
        if (in_array($request->getMethod(), ['POST', 'PUT', 'PATCH'])) {
            $options['body'] = $request->getBody()->getContents();
        }
        try {
            $client = $this->clientFactory->create();
            $proxyResponse = $client->request($request->getMethod(), $targetUrl, $options);
            // Create response with the same status code and headers
            $response = $response->withStatus($proxyResponse->getStatusCode());
            // Copy headers from the proxied response
            foreach ($proxyResponse->getHeaders() as $name => $values) {
                foreach ($values as $value) {
                    $response = $response->withAddedHeader($name, $value);
                }
            }
            // Set the response body
            $response->getBody()->write($proxyResponse->getBody()->getContents());
            return $response;
        } catch (\Exception $e) {
            // Return error response if the service is unavailable
            return $response->withStatus(502)->withBody(new \Hyperf\HttpMessage\Stream\SwooleStream(json_encode(['error' => 'Service unavailable', 'message' => 'Unable to connect to ' . $service . ' service', 'details' => $e->getMessage()])))->withHeader('Content-Type', 'application/json');
        }
    }
}