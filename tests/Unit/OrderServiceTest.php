<?php

namespace Tests\Unit;

use App\Services\OrderService;
use PHPUnit\Framework\TestCase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class OrderServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private OrderService $service;
    private $meleeStockService;

    protected function setUp(): void
    {
        parent::setUp();
        // MeleeStockService is needed for constructor but we can mock it
        $this->meleeStockService = Mockery::mock(\App\Services\MeleeStockService::class);
        $this->service = new OrderService($this->meleeStockService);
    }

    /** @test */
    public function it_extracts_skus_from_valid_data()
    {
        $data = [
            'diamond_sku' => 'SKU-001',
            'order_type' => 'ready_to_ship',
            'product_other' => 'Ring with SKU-002, and also SKU-003'
        ];

        $expected = ['SKU-001', 'SKU-002', 'SKU-003'];
        $result = $this->service->extractValidatedSkus($data);

        $this->assertEquals($expected, $result);
    }

    /** @test */
    public function it_extracts_skus_correctly_when_primary_sku_is_missing()
    {
        $data = [
            'product_other' => 'Old primary was SKU-X01, new is SKU-Y01'
        ];

        $result = $this->service->extractValidatedSkus($data);
        $this->assertEquals(['SKU-X01', 'SKU-Y01'], $result);
    }

    /** @test */
    public function it_extracts_melee_entries_correctly()
    {
        $data = [
            'melee_entries' => [
                ['melee_diamond_id' => 1, 'pieces' => 5, 'carat' => 0.5, 'price_per_ct' => 100],
                ['melee_diamond_id' => 2, 'pieces' => 10, 'carat' => 1.0, 'price_per_ct' => 150]
            ]
        ];

        $result = $this->service->extractValidatedMeleeEntries($data);
        $this->assertCount(2, $result);
        $this->assertEquals(1, $result[0]['melee_diamond_id']);
    }
}
