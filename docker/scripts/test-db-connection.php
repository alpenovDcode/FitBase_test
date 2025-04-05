<?php
/**
 * Скрипт для проверки соединения с базой данных
 */

// Параметры подключения из переменных окружения или значения по умолчанию
$host = getenv('DB_HOST') ?: 'mysql';
$port = getenv('DB_PORT') ?: '3306';
$database = getenv('DB_DATABASE') ?: 'fitbase';
$username = getenv('DB_USERNAME') ?: 'fitbase';
$password = getenv('DB_PASSWORD') ?: 'fitbasePwd123';

echo "Проверка соединения с базой данных MySQL...\n";
echo "Хост: $host\n";
echo "Порт: $port\n";
echo "База данных: $database\n";
echo "Пользователь: $username\n";
echo "------------------------------------\n";

try {
    // Создаем подключение
    $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
    $conn = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    // Проверяем версию MySQL
    $stmt = $conn->query('SELECT VERSION() as version');
    $version = $stmt->fetch()['version'];
    
    echo "Соединение установлено успешно!\n";
    echo "Версия MySQL: $version\n";
    
    // Проверяем существующие таблицы
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($tables) > 0) {
        echo "\nСписок таблиц в базе данных '$database':\n";
        foreach ($tables as $table) {
            echo "- $table\n";
        }
    } else {
        echo "\nВ базе данных '$database' нет таблиц.\n";
    }
    
    echo "\nПроверка успешно завершена.\n";
    
} catch (PDOException $e) {
    echo "Ошибка соединения: " . $e->getMessage() . "\n";
    exit(1);
} 