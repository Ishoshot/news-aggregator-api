<?php

declare(strict_types=1);

namespace Tests\Unit\Commands\Sqlite;

use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use LogicException;

it('sets the WAL journal mode successfully', function (): void {

    $mockConnection = $this->mock(SQLiteConnection::class);

    $mockConnection->shouldReceive('statement')->once()->with('PRAGMA journal_mode=WAL;')->andReturnTrue();

    $mockConnection->shouldReceive('select')->once()->with('PRAGMA journal_mode')->andReturn([['journal_mode' => 'wal']]);

    $mockManager = $this->mock(DatabaseManager::class);

    $mockManager->shouldReceive('connection')->with('sqlite')->andReturn($mockConnection);

    // Replace the app's DatabaseManager with the mock
    $this->instance(DatabaseManager::class, $mockManager);

    Artisan::call('sqlite:wal-enable');

    Log::assertLogged('info', function ($message, $context) {
        return str_contains($message, 'The sqlite connection has been set as [wal] journal mode.');
    });

});

it('throws an exception if the connection is not SQLite', function (): void {

    $mockConnection = $this->mock(Connection::class);

    $mockConnection->shouldReceive('getDriverName')->andReturn('mysql');

    $mockManager = $this->mock(DatabaseManager::class);

    $mockManager->shouldReceive('connection')->with('sqlite')->andReturn($mockConnection);

    // Replace the app's DatabaseManager with the mock
    $this->instance(DatabaseManager::class, $mockManager);

    $this->expectException(LogicException::class);

    $this->expectExceptionMessage("The 'sqlite' connection must be sqlite, [mysql] given.");

    Artisan::call('sqlite:wal-enable');

});

it('fails to set the WAL journal mode if the journal mode is not wal', function (): void {

    $mockConnection = $this->mock(SQLiteConnection::class);

    $mockConnection->shouldReceive('statement')->once()->with('PRAGMA journal_mode=WAL;')->andReturnTrue();

    $mockConnection->shouldReceive('select')->once()->with('PRAGMA journal_mode')->andReturn([['journal_mode' => 'delete']]);

    $mockManager = $this->mock(DatabaseManager::class);

    $mockManager->shouldReceive('connection')->with('sqlite')->andReturn($mockConnection);

    // Replace the app's DatabaseManager with the mock
    $this->instance(DatabaseManager::class, $mockManager);

    Artisan::call('sqlite:wal-enable');

    expect(Artisan::output())->toContain('The sqlite could not be set as WAL, returned [delete] as journal mode.');
});
