<?php

namespace winwin\discovery\apisix\application;


use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use winwin\discovery\apisix\servant\Notification;
use winwin\server\hook\EventName;

class EventBusSubscriberServantImplTest extends TestCase
{
    /**
     * @var EventBusSubscriberServantImpl
     */
    private $eventBusSubscriberServant;
    /**
     * @var MockHandler
     */
    private $mockHandler;
    /**
     * @var array
     */
    private $requests;

    protected function setUp(): void
    {
        parent::setUp();
        $this->requests = [];
        $mockHandler = new MockHandler();
        $handler = HandlerStack::create($mockHandler);
        $handler->push(Middleware::history($this->requests));
        $httpClient = new Client([
            'handler' => $handler
        ]);

        $this->eventBusSubscriberServant = new EventBusSubscriberServantImpl(new ApisixAdminClientImpl($httpClient), []);
        $this->eventBusSubscriberServant->setLogger(new NullLogger());
        $this->mockHandler = $mockHandler;
    }

    public function testName()
    {
        $this->mockHandler->append(
            new Response(404),
            new Response(200)
        );
        $notification = new Notification();
        $notification->eventName = EventName::SERVER_START;
        $notification->payload = json_encode(['server' => 'FooServer', 'ip_address' => '10.0.0.1']);
        $this->eventBusSubscriberServant->handle($notification);
        $this->assertCount(2, $this->requests);
    }
}
