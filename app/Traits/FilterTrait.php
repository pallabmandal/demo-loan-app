<?php

namespace App\Traits;

use Illuminate\Support\Str;

use Exception;

trait FilterTrait
{
    public function filterQuery($filters, $query)
    {
        foreach ($filters as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $operator => $valueToOperate) {
                    $query->when(
                        Str::contains($operator, '.'), //We are checking if the Attribute is column or a relationship

                        function ($query) use ($operator, $valueToOperate) {
                            [$relationName, $relationAttribute] = explode('.', $operator);

                            $query->WhereHas($relationName, function ($query) use ($relationAttribute, $valueToOperate) {
                                // $x = array_keys($valueToOperate[0])[0];
                                // $searchTerm = $valueToOperate[0][$x];

                                // $query->where($relationAttribute, 'LIKE', "%{$searchTerm}%");
                                self::mapQuery($relationAttribute, $valueToOperate, $query);
                            });
                        },
                        function ($query) use ($operator, $valueToOperate) {
                            self::mapQuery($operator, $valueToOperate, $query);
                        }

                    );
                }
            } else {
                throw new Exception('Invalid Array Check Params');
            }
        }
        return $query;
    }

    private function mapQuery($operator, $valueToOperate, $query)
    {
        if (is_array($valueToOperate)) {
            foreach ($valueToOperate as $key => $value1) {
                if (is_array($value1)) {
                    $key     = array_keys($value1)[0];
                    $operand = $value1[$key];

                    if (!empty($operand) || $operand === 0) {
                        switch ($key) {
                        case 'equ':
                            $query = $query->where($operator, '=', $operand);
                            break;
                        case 'nte':
                            $query = $query->where($operator, '<>', $operand);
                            break;
                        case 'lte':
                            $query = $query->where($operator, '<=', $operand);
                            break;
                        case 'lt':
                            $query = $query->where($operator, '<', $operand);
                            break;
                        case 'gte':
                            $query = $query->where($operator, '>=', $operand);
                            break;
                        case 'gt':
                            $query = $query->where($operator, '>', $operand);
                            break;
                        case 'includes':
                            $query = $query->where($operator, 'like', "%{$operand}%");
                            break;
                        case 'startsWith':
                            $query = $query->where($operator, 'like', "{$operand}%");
                            break;
                        case 'endsWith':
                            $query = $query->where($operator, 'like', "%{$operand}");
                            break;
                        case 'between':
                            if (is_array($operand)) {
                                $query = $query->whereBetween($operator, $operand);
                            }

                            break;
                        case 'in':
                            if (is_array($operand)) {
                                $query = $query->whereIn($operator, $operand);
                            }

                            break;
                        }
                    }
                } else {
                    throw new Exception('Invalid Array Check Params');
                }
            }
        } else {
            $query->where($operator, 'LIKE', "%{$valueToOperate}%");
        }
    }
}
