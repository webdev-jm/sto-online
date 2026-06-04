<?php

namespace App\Http\Traits;

use App\Models\ChannelMapping;

Trait ChannelMappingTrait {

    public function channelMapping($account_id, $channel_code): array
    {
        $channel_mapping = ChannelMapping::with('channel')
            ->where('account_id', $account_id)
            ->where('external_channel_code', $channel_code)
            ->first();

        $channel_name = $channel_mapping?->channel?->name;
        $channel_code = $channel_mapping?->channel?->code ?? $channel_code;

        return [$channel_code, $channel_name];
    }
}
