<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use App\Model\Filter;

class ProductFilterService
{
    public function parseFilters(Request $request): array
    {
        $filters = $request->query->all();
        $supportedOperators = ['<', '>', '<=', '>=', '=', '!=', '..'];
        $parsedFilters = [];

        foreach ($filters as $key => $expression) {
            if (strpos($key, '_') !== false) {
                $lastUnderscoreIndex = strrpos($key, '_');
                $lastPart = substr($key, $lastUnderscoreIndex + 1);
                if (is_numeric($lastPart)) {
                    $key = substr($key, 0, $lastUnderscoreIndex);
                }
            }
            if(strlen($expression) == 0){
                continue;
            }
            if (strpos($expression, '..') !== false) {
                // Handle range operator '..'
                $rangeParts = explode('..', $expression);
                if (count($rangeParts) === 2) {
                    $parsedFilters[] = new Filter($key, '>=', trim($rangeParts[0]));
                    $parsedFilters[] = new Filter($key, '<=', trim($rangeParts[1]));
                } else {
                    throw new \InvalidArgumentException(sprintf('Invalid range expression for filter "%s"', $key));
                }
            } else {
                // Regex to match other operators and extract the value
                preg_match('/^([<>=!]*)(.*)$/', $expression, $matches);
                $operator = $matches[1] ?: '=';
                $value = $matches[2];

                if (!in_array($operator, $supportedOperators)) {
                    throw new \InvalidArgumentException("Invalid operator '$operator' for key '$key'");
                }

                $parsedFilters[] = new Filter($key, $operator, trim($value));
            }
        }

        return $parsedFilters;
    }
}