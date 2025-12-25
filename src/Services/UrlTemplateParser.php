<?php

namespace Olivier\CustomButtons\Services;

use App\Models\Server;

class UrlTemplateParser
{
    public static function parse(string $url, Server $server): string
    {
        $replacements = [
            '{{env.P_SERVER_UUID}}' => $server->uuid,
            '{{env.P_SERVER_UUID_SHORT}}' => $server->uuid_short,
            '{{env.P_SERVER_NAME}}' => $server->name,
            '{{env.P_SERVER_ID}}' => $server->id,
            '{{env.P_SERVER_ALLOCATION_IP}}' => $server->allocation?->ip ?? '',
            '{{env.P_SERVER_ALLOCATION_PORT}}' => $server->allocation?->port ?? '',
            '{{env.P_SERVER_NODE}}' => $server->node->name,
            '{{env.P_SERVER_OWNER}}' => $server->user->username,
        ];

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $url
        );
    }
}
