<?php

namespace Tests\Feature\Traits;

use App\Http\Traits\ChannelMappingTrait;
use App\Models\Channel;
use App\Models\ChannelMapping;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ChannelMappingTest extends TestCase
{
    use DatabaseTransactions;

    private object $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new class {
            use ChannelMappingTrait;
        };
    }

    public function test_returns_internal_code_and_name_when_mapping_exists(): void
    {
        $channel = Channel::factory()->create(['code' => 'SMKT', 'name' => 'Supermarket']);
        ChannelMapping::create([
            'account_id'            => 999,
            'channel_id'            => $channel->id,
            'external_channel_code' => 'EXT-SM',
            'external_channel_name' => 'External Supermarket',
        ]);

        [$code, $name] = $this->subject->channelMapping(999, 'EXT-SM');

        $this->assertSame('SMKT', $code);
        $this->assertSame('Supermarket', $name);
    }

    public function test_returns_original_code_and_null_name_when_mapped_channel_is_soft_deleted(): void
    {
        $channel = Channel::factory()->create(['code' => 'GRO', 'name' => 'Grocery']);
        ChannelMapping::create([
            'account_id'            => 999,
            'channel_id'            => $channel->id,
            'external_channel_code' => 'EXT-GRO',
            'external_channel_name' => 'External Grocery',
        ]);
        $channel->delete(); // soft-delete the channel

        [$code, $name] = $this->subject->channelMapping(999, 'EXT-GRO');

        $this->assertSame('EXT-GRO', $code);
        $this->assertNull($name);
    }

    public function test_returns_original_code_and_null_name_when_no_mapping_exists(): void
    {
        [$code, $name] = $this->subject->channelMapping(999, 'UNKNOWN-CODE');

        $this->assertSame('UNKNOWN-CODE', $code);
        $this->assertNull($name);
    }
}
