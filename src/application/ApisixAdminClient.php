<?php


namespace winwin\discovery\apisix\application;


use winwin\discovery\apisix\application\exception\ResourceNotFoundException;

interface ApisixAdminClient
{
    /**
     * 查询 upstream 信息
     * @param string $upstreamId
     * @return array
     * @throws ResourceNotFoundException
     */
    public function getUpstream(string $upstreamId): array ;

    /**
     * 创建 upstream 信息
     * @param string $upstreamId
     * @param array $upstreamInfo
     * @throws \InvalidArgumentException if required parameter is missing
     */
    public function createUpstream(string $upstreamId, array $upstreamInfo): void ;

    /**
     * 更新 upstream 信息
     * @param string $upstreamId
     * @param array $upstreamInfo
     * @throws ResourceNotFoundException
     * @throws \InvalidArgumentException if required parameter is missing
     */
    public function updateUpstream(string $upstreamId, array $upstreamInfo): void ;
}