<?php
declare(strict_types=1);

function missionTestConnection(): PDO
{
    $driver = getenv('FACTORY_GOVERNANCE_TEST_DRIVER') ?: 'sqlite';
    if ($driver === 'sqlite') return new PDO('sqlite::memory:');
    if ($driver !== 'mariadb' || getenv('FACTORY_DISPOSABLE_DB') !== 'I_CONFIRM_DISPOSABLE_CI') {
        throw new RuntimeException('Explicit disposable CI database required');
    }
    // Fixed localhost-only fixture DB. Never load .env, an application DSN or VPS credentials.
    $db = new PDO('mysql:host=127.0.0.1;port=3306;dbname=factory_governance_ci;charset=utf8mb4',
        getenv('FACTORY_CI_DB_USER') ?: '', getenv('FACTORY_CI_DB_PASSWORD') ?: '',
        [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES=>false]);
    if ($db->query('SELECT DATABASE()')->fetchColumn() !== 'factory_governance_ci') {
        throw new RuntimeException('Unexpected CI database');
    }
    return $db;
}

function missionTestReset(PDO $db): void
{
    if ($db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
        $db->exec('DROP TABLE IF EXISTS factory_missions');
        $db->exec('CREATE TABLE factory_missions (id BIGINT PRIMARY KEY, product_id BIGINT, status VARCHAR(64), mission_dna JSON, updated_at DATETIME NULL) ENGINE=InnoDB');
        $engine = $db->query("SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='factory_missions'")->fetchColumn();
        if ($engine !== 'InnoDB') throw new RuntimeException('InnoDB required');
    } else {
        $db->exec('CREATE TABLE factory_missions (id INTEGER PRIMARY KEY, product_id INTEGER, status TEXT, mission_dna TEXT, updated_at TEXT)');
    }
}
