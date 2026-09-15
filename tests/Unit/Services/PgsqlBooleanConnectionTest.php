<?php

use App\Services\PgsqlBooleanConnection;

uses(Tests\TestCase::class);

function pgsqlConnection(): PgsqlBooleanConnection
{
    return new PgsqlBooleanConnection(fn () => throw new LogicException('no pdo needed'));
}

it('keeps booleans as booleans when preparing bindings', function () {
    $now = new DateTimeImmutable('2026-09-15 12:00:00');

    expect(pgsqlConnection()->prepareBindings([true, false, 1, 'x', $now]))
        ->toBe([true, false, 1, 'x', '2026-09-15 12:00:00']);
});

it('binds booleans with PDO::PARAM_BOOL', function () {
    $statement = Mockery::mock(PDOStatement::class);
    $statement->shouldReceive('bindValue')->once()->with(1, false, PDO::PARAM_BOOL);
    $statement->shouldReceive('bindValue')->once()->with(2, 7, PDO::PARAM_INT);
    $statement->shouldReceive('bindValue')->once()->with(3, 'x', PDO::PARAM_STR);

    pgsqlConnection()->bindValues($statement, [false, 7, 'x']);
});

it('is the resolved pgsql connection class', function () {
    expect(app('db')->connection('pgsql'))->toBeInstanceOf(PgsqlBooleanConnection::class);
});
