<?php


namespace winwin\discovery\apisix\application;


use DI\Annotation\Inject;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use kuiper\di\annotation\Service;
use Psr\Http\Message\ResponseInterface;
use winwin\discovery\apisix\application\exception\ResourceNotFoundException;

/**
 * @Service()
 */
class ApisixAdminClientImpl implements ApisixAdminClient
{
    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @Inject({"httpClient": "ApisixAdminHttpClient"})
     */
    public function __construct(ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @inheritDoc
     */
    public function getUpstream(string $upstreamId): array
    {
        try {
            $response = $this->httpClient->request("GET", "upstreams/" . $upstreamId);
            return $this->parseResponse($response)['node']['value'];
        } catch (RequestException $e) {
            if ($e->hasResponse() && $e->getResponse()->getStatusCode() === 404) {
                throw new ResourceNotFoundException("upstream '$upstreamId' not found");
            }
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function createUpstream(string $upstreamId, array $upstreamInfo): void
    {
        try {
            $upstreamInfo['id'] = $upstreamId;
            $this->httpClient->request('PUT', 'upstreams', [
                'json' => $upstreamInfo
            ]);
        } catch (RequestException $e) {
            if ($e->hasResponse() && $e->getResponse()->getStatusCode() === 400) {
                $error = json_decode($e->getResponse()->getBody(), true);
                throw new \InvalidArgumentException($error['error_msg'] ?? 'unknown error');
            }
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function updateUpstream(string $upstreamId, array $upstreamInfo): void
    {
        try {
            $this->httpClient->request("PATCH", "upstreams/" . $upstreamId, [
                'json' => $upstreamInfo
            ]);
        } catch (RequestException $e) {
            if ($e->hasResponse() && $e->getResponse()->getStatusCode() === 404) {
                throw new ResourceNotFoundException("upstream '$upstreamId' not found");
            }

            if ($e->hasResponse() && $e->getResponse()->getStatusCode() === 400) {
                $error = json_decode($e->getResponse()->getBody(), true);
                throw new \InvalidArgumentException($error['error_msg'] ?? 'unknown error');
            }
            throw $e;
        }
    }

    private function parseResponse(ResponseInterface $response)
    {
        return json_decode($response->getBody(), true);
    }
}