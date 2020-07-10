<?php


namespace winwin\discovery\apisix\domain;


class EventPayload
{
    /**
     * @var string
     */
    private $server;

    /**
     * @var string
     */
    private $ipAddress;

    /**
     * @return string
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * @param string $server
     */
    public function setServer($server): void
    {
        $this->server = $server;
    }

    /**
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
    }

    /**
     * @param string $ipAddress
     */
    public function setIpAddress($ipAddress): void
    {
        $this->ipAddress = $ipAddress;
    }
}