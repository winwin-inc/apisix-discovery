<?php


namespace winwin\discovery\apisix;

use DI\Annotation\Inject;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use kuiper\di\annotation\Bean;
use kuiper\di\annotation\Configuration;
use kuiper\http\client\HttpClientFactoryInterface;
use kuiper\logger\LoggerFactoryInterface;
use winwin\discovery\apisix\application\ApisixAdminClient;

/**
 * @Configuration()
 */
class ApisixDiscoveryConfiguration
{
    /**
     * @Bean("ApisixAdminHttpClient")
     * @Inject({"options": "application.apisix"})
     */
    public function httpClient(LoggerFactoryInterface $loggerFactory, HttpClientFactoryInterface $httpClientFactory, array $options): ClientInterface
    {
        $logger = $loggerFactory->create(ApisixAdminClient::class);
        $handler = HandlerStack::create();
        if (!empty($options['debug'])) {
            $handler->push(Middleware::log($logger, new MessageFormatter(MessageFormatter::DEBUG)));
        }
        return $httpClientFactory->create([
            'base_uri' => $options['admin_uri'] . '/apisix/admin/',
            'headers' => [
                'X-API-KEY' => $options['api_key']
            ],
            'handler' => $handler
        ]);
    }
}