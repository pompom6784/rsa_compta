<?php

namespace App\Tests\Services;

use PHPUnit\Framework\TestCase;
use App\Services\SogecomImportService;
use App\Models\LineBreakdown;

class SogecomImportServiceTest extends TestCase
{
    private SogecomImportService $service;

    protected function setUp(): void
    {
        $this->service = new SogecomImportService();
    }

    public function testCreateLineWithPlaneRenewal(): void
    {
        // Arrange - amount >= 120
        $data = [
            '01/01/2025 00:00:00',  // date (index 0)
            '150.50 EUR',           // amount (index 1)
            '',                     // unused (index 2)
            'Acme Corp',            // name (index 3)
            'Description line 1',   // description (index 4)
            'Description line 2',   // description (index 5)
            '2.50 EUR',             // fees (index 6)
        ];

        // Act
        $line = $this->service->createLine($data);

        // Assert
        $this->assertEquals(150.50, $line->amount);
        $this->assertContains(LineBreakdown::PLANE_RENEWAL, $line->breakdown);
        $this->assertEquals(120, $line->breakdown_plane_renewal);
        $this->assertEqualsWithDelta(30.50, $line->breakdown_customer_fees, 0.001);
    }

    public function testCreateLineWithSmallAmountIsRSAContribution(): void
    {
        // Arrange - amount > 0 but < 120
        $data = [
            '01/01/2025 00:00:00',
            '50.00 EUR',
            '',
            'Acme Corp',
            'Contribution',
            'Description',
            '0.50 EUR',
        ];

        // Act
        $line = $this->service->createLine($data);

        // Assert
        $this->assertEquals(50.00, $line->amount);
        $this->assertContains(LineBreakdown::RSA_NAV_CONTRIBUTION, $line->breakdown);
    }

    public function testCreateFeesLineCalculatesFees(): void
    {
        // Arrange
        $data = [
            '01/01/2025 00:00:00',
            '120.00 EUR',
            '',
            'Acme Corp',
            'Description',
            'Description',
            '2.50 EUR',
        ];

        // Act
        $line = $this->service->createFeesLine($data);

        // Assert
        $this->assertNotNull($line);
        $this->assertEquals(-2.50, $line->amount);
        $this->assertContains(LineBreakdown::SOGECOM_FEES, $line->breakdown);
    }

    public function testCreateFeesLineReturnsNullForZeroFees(): void
    {
        // Arrange
        $data = [
            '01/01/2025 00:00:00',
            '120.00 EUR',
            '',
            'Acme Corp',
            'Description',
            'Description',
            '0.00 EUR',
        ];

        // Act
        $line = $this->service->createFeesLine($data);

        // Assert
        $this->assertNull($line);
    }

    public function testToFloatHandlesEURCurrency(): void
    {
        // Arrange
        $reflection = new \ReflectionMethod($this->service, 'toFloat');
        $reflection->setAccessible(true);

        // Act
        $result = $reflection->invoke($this->service, '100,50 EUR');

        // Assert
        $this->assertEquals(100.50, $result);
    }

    public function testToFloatHandlesSpacesAndCommas(): void
    {
        // Arrange
        $reflection = new \ReflectionMethod($this->service, 'toFloat');
        $reflection->setAccessible(true);

        // Act
        $result = $reflection->invoke($this->service, '1 000,50 EUR');

        // Assert
        $this->assertEquals(1000.50, $result);
    }
}
