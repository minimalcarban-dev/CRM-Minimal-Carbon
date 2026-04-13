<?php

namespace Tests\Unit;

use App\Services\PaymentService;
use PHPUnit\Framework\TestCase;

class PaymentServiceTest extends TestCase
{
    private PaymentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PaymentService();
    }

    /**
     * @test
     * @dataProvider paymentDataProvider
     */
    public function it_correctly_normalizes_payment_summary(array $input, float $gross_sell, array $expected)
    {
        $result = $this->service->normalizePaymentSummary($input, $gross_sell);

        $this->assertEquals($expected['payment_status'], $result['payment_status']);
        $this->assertEquals($expected['amount_received'], $result['amount_received']);
        $this->assertEquals($expected['amount_due'], $result['amount_due']);
    }

    public static function paymentDataProvider(): array
    {
        return [
            'Full payment explicitly set' => [
                'input' => ['payment_status' => 'full', 'amount_received' => 1000, 'amount_due' => 0],
                'gross_sell' => 1000,
                'expected' => ['payment_status' => 'full', 'amount_received' => 1000.0, 'amount_due' => 0.0]
            ],
            'Partial payment with math' => [
                'input' => ['payment_status' => 'partial', 'amount_received' => 600, 'amount_due' => 400],
                'gross_sell' => 1000,
                'expected' => ['payment_status' => 'partial', 'amount_received' => 600.0, 'amount_due' => 400.0]
            ],
            'Due payment' => [
                'input' => ['payment_status' => 'due', 'amount_received' => 0, 'amount_due' => 1000],
                'gross_sell' => 1000,
                'expected' => ['payment_status' => 'due', 'amount_received' => 0.0, 'amount_due' => 1000.0]
            ],
            'Missing payment fields defaults to FULL' => [
                'input' => [], // payment_status, amount_received, amount_due omitted
                'gross_sell' => 1500,
                'expected' => ['payment_status' => 'full', 'amount_received' => 1500.0, 'amount_due' => 0.0]
            ],
            'Zero gross sell results in zero summary' => [
                'input' => ['payment_status' => 'partial', 'amount_received' => 100],
                'gross_sell' => 0,
                'expected' => ['payment_status' => 'full', 'amount_received' => 0.0, 'amount_due' => 0.0]
            ]
        ];
    }
}
