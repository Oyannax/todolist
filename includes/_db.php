<?php
// todolist database connection
try {
    // Get environment configuration
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../');
    $dotenv->load();

    // Connect to the database
    $dbCo = new PDO(
        $_ENV['DB_HOST'],
        $_ENV['DB_USER'],
        $_ENV['DB_PWD']
    );
    $dbCo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die('Unable to connect to the database. '.$e->getMessage());
}