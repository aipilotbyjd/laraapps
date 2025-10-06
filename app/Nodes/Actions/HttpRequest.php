<?php

namespace App\Nodes\Actions;

use App\Nodes\Base\BaseNode;
use Illuminate\Support\Facades\Http;

class HttpRequest extends BaseNode
{
    protected string $name = 'HTTP Request';
    protected string $type = 'http_request';
    protected string $group = 'action';
    
    public function execute(array $input, array $parameters, array $credentials = []): array
    {
        $method = strtolower($parameters['method'] ?? 'GET');
        $url = $this->resolveParameter($parameters['url'], $input);
        $headers = $parameters['headers'] ?? [];
        $queryParams = $parameters['query_parameters'] ?? [];
        $body = $parameters['body'] ?? [];
        $authentication = $parameters['authentication'] ?? 'none';
        
        // Build request
        $request = Http::timeout($parameters['timeout'] ?? 30);
        
        // Add headers
        foreach ($headers as $header) {
            $request = $request->withHeaders([
                $header['name'] => $this->resolveParameter($header['value'], $input)
            ]);
        }
        
        // Add authentication
        if ($authentication !== 'none' && !empty($credentials)) {
            $request = $this->addAuthentication($request, $authentication, $credentials);
        }
        
        // Add query parameters
        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }
        
        // Execute request
        try {
            $response = match($method) {
                'get' => $request->get($url),
                'post' => $request->post($url, $body),
                'put' => $request->put($url, $body),
                'patch' => $request->patch($url, $body),
                'delete' => $request->delete($url, $body),
                default => throw new \Exception("Unsupported HTTP method: {$method}")
            };
            
            return [
                'status' => $response->status(),
                'headers' => $response->headers(),
                'body' => $response->json() ?? $response->body(),
            ];
            
        } catch (\Exception $e) {
            throw new \Exception("HTTP Request failed: " . $e->getMessage());
        }
    }
    
    protected function addAuthentication($request, $type, $credentials)
    {
        return match($type) {
            'basic_auth' => $request->withBasicAuth($credentials['username'], $credentials['password']),
            'bearer_token' => $request->withToken($credentials['token']),
            'api_key' => $request->withHeaders([
                $credentials['header_name'] ?? 'X-API-Key' => $credentials['api_key']
            ]),
            default => $request
        };
    }
    
    protected function getDescription(): string
    {
        return 'Make HTTP requests to any URL';
    }
    
    protected function getProperties(): array
    {
        return [
            [
                'name' => 'method',
                'displayName' => 'Method',
                'type' => 'select',
                'required' => true,
                'default' => 'GET',
                'options' => [
                    ['name' => 'GET', 'value' => 'GET'],
                    ['name' => 'POST', 'value' => 'POST'],
                    ['name' => 'PUT', 'value' => 'PUT'],
                    ['name' => 'PATCH', 'value' => 'PATCH'],
                    ['name' => 'DELETE', 'value' => 'DELETE'],
                ]
            ],
            [
                'name' => 'url',
                'displayName' => 'URL',
                'type' => 'string',
                'required' => true,
                'placeholder' => 'https://api.example.com/endpoint',
            ],
            [
                'name' => 'authentication',
                'displayName' => 'Authentication',
                'type' => 'select',
                'default' => 'none',
                'options' => [
                    ['name' => 'None', 'value' => 'none'],
                    ['name' => 'Basic Auth', 'value' => 'basic_auth'],
                    ['name' => 'Bearer Token', 'value' => 'bearer_token'],
                    ['name' => 'API Key', 'value' => 'api_key'],
                ]
            ],
            [
                'name' => 'headers',
                'displayName' => 'Headers',
                'type' => 'collection',
                'default' => [],
                'options' => [
                    ['name' => 'name', 'type' => 'string'],
                    ['name' => 'value', 'type' => 'string'],
                ]
            ],
            [
                'name' => 'query_parameters',
                'displayName' => 'Query Parameters',
                'type' => 'collection',
                'default' => [],
            ],
            [
                'name' => 'body',
                'displayName' => 'Body',
                'type' => 'json',
                'displayOptions' => [
                    'show' => [
                        'method' => ['POST', 'PUT', 'PATCH']
                    ]
                ],
            ],
            [
                'name' => 'timeout',
                'displayName' => 'Timeout',
                'type' => 'number',
                'default' => 30,
                'description' => 'Timeout in seconds',
            ],
        ];
    }
    
    protected function getCredentials(): array
    {
        return [
            [
                'name' => 'httpBasicAuth',
                'required' => false,
                'displayOptions' => [
                    'show' => [
                        'authentication' => ['basic_auth']
                    ]
                ],
            ],
        ];
    }
}