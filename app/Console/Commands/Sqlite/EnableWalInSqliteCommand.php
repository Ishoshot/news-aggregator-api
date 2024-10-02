<?php

declare(strict_types=1);

namespace App\Console\Commands\Sqlite;

use Illuminate\Console\Command;
use Illuminate\Database\Connection;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\SQLiteConnection;
use LogicException;

final class EnableWalInSqliteCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sqlite:wal-enable
                            {connection=sqlite : The connection to enable WAL journal}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enables WAL journal on SQLite databases as performance optimization.';

    /**
     * Execute the console command.
     */
    public function handle(DatabaseManager $manager): void
    {
        $this->setWalJournalMode(
            $db = $this->getDatabase($manager, $connection = $this->argument('connection'))
        );

        $journal = $this->getJournalMode($db);

        if ($journal !== 'wal') {
            $this->error('The '.$connection.' could not be set as WAL, returned ['.$journal.'] as journal mode.'); // @phpstan-ignore-line
        } else {
            $this->info('The '.$connection.' connection has been set as ['.$journal.'] journal mode.');
        }
    }

    /**
     * Returns the Database Connection
     */
    private function getDatabase(DatabaseManager $manager, string $connection): Connection
    {
        $db = $manager->connection($connection);

        // We will throw an exception if the database is not SQLite
        if (! $db instanceof SQLiteConnection) {
            throw new LogicException("The '$connection' connection must be sqlite, [{$db->getDriverName()}] given.");
        }

        return $db;
    }

    /**
     * Sets the Journal Mode to WAL
     */
    private function setWalJournalMode(ConnectionInterface $connection): bool
    {
        return $connection->statement('PRAGMA journal_mode=WAL;');
    }

    /**
     * Returns the current Journal Mode of the Database Connection
     */
    private function getJournalMode(ConnectionInterface $connection): mixed
    {
        return data_get($connection->select('PRAGMA journal_mode'), '0.journal_mode');
    }
}
