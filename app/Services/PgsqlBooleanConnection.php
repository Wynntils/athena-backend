<?php

namespace App\Services;

use Illuminate\Database\PostgresConnection;
use PDO;

/**
 * Postgres runs with PDO::ATTR_EMULATE_PREPARES, which inlines bindings as literals. The base
 * connection casts PHP bools to 0/1 which Postgres rejects against boolean columns
 * ("operator does not exist: boolean = integer"), so bind them as real booleans instead.
 */
class PgsqlBooleanConnection extends PostgresConnection
{
    public function prepareBindings(array $bindings): array
    {
        $booleans = array_filter($bindings, is_bool(...));

        return array_replace(parent::prepareBindings($bindings), $booleans);
    }

    public function bindValues($statement, $bindings): void
    {
        foreach ($bindings as $key => $value) {
            $statement->bindValue(
                is_string($key) ? $key : $key + 1,
                $value,
                match (true) {
                    is_bool($value) => PDO::PARAM_BOOL,
                    is_int($value) => PDO::PARAM_INT,
                    is_resource($value) => PDO::PARAM_LOB,
                    default => PDO::PARAM_STR,
                },
            );
        }
    }
}
