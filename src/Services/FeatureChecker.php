<?php

namespace Olivier\CustomButtons\Services;

use App\Models\Server;

class FeatureChecker
{
    public static function hasFeature(Server $server, ?string $requiredFeature): bool
    {
        if (!$requiredFeature) {
            return true;
        }

        $features = $server->egg->features ?? [];
        $tags = $server->egg->tags ?? [];

        return in_array($requiredFeature, $features) || in_array($requiredFeature, $tags);
    }
}
