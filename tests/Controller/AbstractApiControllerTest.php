<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\TestCase;
use App\Controller\AbstractApiController;
use App\Entity\Product;

class AbstractApiControllerTest extends TestCase
{
    private $_apiController;

    public function setUp(): void
    {
        $this->_apiController = new class extends AbstractApiController {};
    }

    public function testCanSerializeObject(): void
    {
        $this->assertEquals('{"foo":"bar"}', $this->_apiController->jsonSerialize(['foo' => 'bar']));
    }
    
    /**
     * @depends testCanSerializeObject
     */
    public function testCanDeserializeJson(): void
    {
        // Arrange
        $product = new Product();
        $product->setName('foo');

        // Act
        $deserialized = $this->_apiController->jsonDeserialize('{"name":"foo"}', Product::class);

        // Assert
        $this->assertEquals($product, $deserialized);
    }

    public function testThrowsErrorOnInvalidJson(): void
    {
        $this->expectException(\Exception::class);
        $this->_apiController->jsonDeserialize('{"name":"', Product::class);
    }
}
