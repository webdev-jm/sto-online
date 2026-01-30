<?php

namespace App\Http\Traits;

use App\Models\ChannelMapping;

Trait ChannelMappingTrait {

    public function channelMapping($account_id, $channel_code) {
        $channel_mapping = ChannelMapping::where('account_id', $account_id)
            ->where('external_channel_code', $channel_code)
            ->first();

        $channel_name = NULL;
        if(!empty($channel_mapping)) {
            $channel_code = $channel_mapping->channel->code;
            $channel_name = $channel_mapping->channel->name;
        }

        return [$channel_code, $channel_name];
    }
}
