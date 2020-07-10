<?php


namespace winwin\discovery\apisix;

use DI\Annotation\Inject;
use GuzzleHttp\ClientInterface;
use kuiper\di\annotation\Bean;
use kuiper\di\annotation\Configuration;
use kuiper\http\client\HttpClientFactoryInterface;

/**
 * @Configuration()
 */
class ApisixDiscoveryConfiguration
{
    /**
     * @Bean("ApisixAdminHttpClient")
     * @Inject({"options": "application.apisix"})
     */
    public function httpClient(HttpClientFactoryInterface $httpClientFactory, array $options): ClientInterface
    {
        return $httpClientFactory->create([
            'base_uri' => $options['admin_uri'] . '/apisix/admin/',
            'headers' => [
                'X-API-KEY' => $options['api_key']
            ]
        ]);
    }
}