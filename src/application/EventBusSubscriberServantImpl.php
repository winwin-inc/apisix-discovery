<?php


namespace winwin\discovery\apisix\application;


use DI\Annotation\Inject;
use kuiper\di\annotation\Service;
use kuiper\helper\Arrays;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use winwin\discovery\apisix\application\exception\ResourceNotFoundException;
use winwin\discovery\apisix\domain\EventPayload;
use winwin\discovery\apisix\servant\EventBusSubscriberServant;
use winwin\discovery\apisix\servant\Notification;
use winwin\server\hook\EventName;

/**
 * @Service()
 */
class EventBusSubscriberServantImpl implements EventBusSubscriberServant, LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected const TAG = '[' . __CLASS__ . '] ';

    /**
     * @var ApisixAdminClient
     */
    private $adminClient;
    /**
     * @var array
     */
    private $upstreamTemplate;
    /**
     * @var int
     */
    private $weight;

    /**
     * @Inject({"upstreamTemplate": "application.apisix.upstream_template"})
     */
    public function __construct(ApisixAdminClient $adminClient, array $upstreamTemplate, int $weight = 100)
    {
        $this->adminClient = $adminClient;
        $this->upstreamTemplate = $upstreamTemplate;
        $this->weight = $weight;
    }

    /**
     * @inheritDoc
     */
    public function handle($notification)
    {
        if ($notification->eventName === EventName::SERVER_START) {
            $this->onServerStart($notification);
        } elseif ($notification->eventName === EventName::SERVER_SHUTDOWN) {
            $this->onServerShutdown($notification);
        }
    }

    private function onServerStart(Notification $notification): void
    {
        $eventPayload = $this->parsePayload($notification->payload);
        $upstreamInfo = [
            'nodes' => [
                $eventPayload->getIpAddress() => $this->weight
            ]
        ];
        $upstreamId = $this->toUpstreamId($eventPayload->getServer());
        try {
            $this->adminClient->getUpstream($upstreamId);
            $this->adminClient->updateUpstream($upstreamId, $upstreamInfo);
            $this->logger->info(static::TAG . 'update upstream', [
                'upstream_id' => $upstreamId,
                'info' => $upstreamInfo
            ]);
        } catch (ResourceNotFoundException $e) {
            $upstream = array_merge($this->upstreamTemplate, $upstreamInfo);
            $this->adminClient->createUpstream($upstreamId, $upstream);
            $this->logger->info(static::TAG . 'create upstream', [
                'upstream_id' => $upstreamId,
                'info' => $upstream
            ]);
        }
    }

    private function onServerShutdown(Notification $notification): void
    {
        $eventPayload = $this->parsePayload($notification->payload);
        $upstreamInfo = [
            'nodes' => [
                $eventPayload->getIpAddress() => null
            ]
        ];
        $upstreamId = $this->toUpstreamId($eventPayload->getServer());
        try {
            $this->adminClient->updateUpstream($upstreamId, $upstreamInfo);
        } catch (ResourceNotFoundException $e) {
            $this->logger->error(static::TAG . 'upstream not exist', [
                'upstream_id' => $upstreamId
            ]);
        } catch (\InvalidArgumentException $e) {
            $upstreamInfo['nodes'][$eventPayload->getIpAddress()] = 0;
            $this->adminClient->updateUpstream($upstreamId, $upstreamInfo);
        }
    }

    private function parsePayload(string $body): EventPayload
    {
        $payload = new EventPayload();
        Arrays::assign($payload, json_decode($body, true));
        return $payload;
    }

    private function toUpstreamId(string $server)
    {
        return str_replace('.', '_', $server);
    }
}