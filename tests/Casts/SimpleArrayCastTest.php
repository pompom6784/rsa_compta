<?php

namespace Tests\Casts;

use App\Casts\SimpleArrayCast;
use App\Models\Line;
use PHPUnit\Framework\TestCase;

class SimpleArrayCastTest extends TestCase
{
    private SimpleArrayCast $cast;
    private Line $model;

    protected function setUp(): void
    {
        $this->cast = new SimpleArrayCast();
        $this->model = new Line();
    }

    public function testGetReturnsEmptyArrayForNull(): void
    {
        $result = $this->cast->get($this->model, 'breakdown', null, []);
        $this->assertSame([], $result);
    }

    public function testGetReturnsEmptyArrayForEmptyString(): void
    {
        $result = $this->cast->get($this->model, 'breakdown', '', []);
        $this->assertSame([], $result);
    }

    public function testGetDecodesJsonFormat(): void
    {
        $result = $this->cast->get($this->model, 'breakdown', '["PlaneRenewal","CustomerFees"]', []);
        $this->assertSame(['PlaneRenewal', 'CustomerFees'], $result);
    }

    public function testGetDecodesSimpleArrayFormat(): void
    {
        // Old Doctrine simple_array format: comma-separated string without quotes
        $result = $this->cast->get($this->model, 'breakdown', 'PlaneRenewal,CustomerFees', []);
        $this->assertSame(['PlaneRenewal', 'CustomerFees'], $result);
    }

    public function testGetDecodesSingleValueSimpleArrayFormat(): void
    {
        $result = $this->cast->get($this->model, 'breakdown', 'InternalTransfer', []);
        $this->assertSame(['InternalTransfer'], $result);
    }

    public function testSetEncodesArrayAsJson(): void
    {
        $result = $this->cast->set($this->model, 'breakdown', ['PlaneRenewal', 'CustomerFees'], []);
        $this->assertSame('["PlaneRenewal","CustomerFees"]', $result);
    }

    public function testSetReturnsNullForNull(): void
    {
        $result = $this->cast->set($this->model, 'breakdown', null, []);
        $this->assertNull($result);
    }

    public function testSetReindexesArray(): void
    {
        $result = $this->cast->set($this->model, 'breakdown', [1 => 'PlaneRenewal', 5 => 'CustomerFees'], []);
        $this->assertSame('["PlaneRenewal","CustomerFees"]', $result);
    }
}
