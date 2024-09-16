<?php
// tests/Service/ProductFilterServiceTest.php
namespace App\Tests\Service;

use App\Service\ProductFilterService;
use App\Model\Filter;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

class ProductFilterServiceTest extends KernelTestCase
{
    private $productFilterService;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->productFilterService = new ProductFilterService();
    }

    public function testCanParseFiltersWithOperators()
    {
        // Arrange
        $request = new Request([
            'weight' => '<2',
            'potass' => '>=100',
            'cups' => '!=1',
            'carbo' => '2..3',
        ]);

        // Act
        $filters = $this->productFilterService->parseFilters($request);

        // Assert
        $expected = [
            new Filter('weight', '<', '2'),
            new Filter('potass', '>=', '100'),
            new Filter('cups', '!=', '1'),
            new Filter('carbo', '>=', '2'),
            new Filter('carbo', '<=', '3'),
        ];

        $this->assertEquals($expected, $filters);
    }

    public function testParseFiltersWithoutOperators()
    {
        // Arrange
        $request = new Request([
            'name' => 'Cereal',
            'calories' => '100',
        ]);

        // Act
        $filters = $this->productFilterService->parseFilters($request);

        // Assert
        $expected = [
            new Filter('name', '=', 'Cereal'),
            new Filter('calories', '=', '100'),
        ];

        $this->assertEquals($expected, $filters);
    }

    public function testParseFiltersWithInvalidOperator()
    {
        // Arrange
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid operator '=>' for key 'name'");

        $request = new Request([
            'name' => '=>Cereal',
        ]);

        // Act
        $this->productFilterService->parseFilters($request);
    }
}