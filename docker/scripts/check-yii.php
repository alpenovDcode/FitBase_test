<?php
/**
 * Скрипт для проверки версии Yii и настроек окружения
 */

// Определение переменных окружения
$yiiEnv = getenv('YII_ENV') ?: 'dev';
$yiiDebug = getenv('YII_DEBUG') ?: 'true';
$dbHost = getenv('DB_HOST') ?: 'mysql';
$dbPort = getenv('DB_PORT') ?: '3306';
$dbName = getenv('DB_DATABASE') ?: 'fitbase';

echo "Проверка настроек Yii в контейнере\n";
echo "------------------------------------\n";
echo "YII_ENV: $yiiEnv\n";
echo "YII_DEBUG: $yiiDebug\n";
echo "DB_HOST: $dbHost\n";
echo "DB_PORT: $dbPort\n";
echo "DB_DATABASE: $dbName\n";
echo "------------------------------------\n";

// Получение текущей директории
$currentDir = getcwd();
echo "Текущая директория: $currentDir\n";

// Проверка наличия файла Yii
if (file_exists('/app/vendor/yiisoft/yii2/Yii.php')) {
    echo "Файл Yii.php найден\n";
    
    // Подключение файла Yii
    require_once '/app/vendor/yiisoft/yii2/Yii.php';
    
    // Получение версии Yii
    echo "Версия Yii: " . \Yii::getVersion() . "\n";
    
    // Проверка PHP extensions
    $extensions = get_loaded_extensions();
    echo "\nЗагруженные PHP-расширения:\n";
    $requiredExtensions = ['pdo', 'pdo_mysql', 'gd', 'intl', 'zip'];
    
    foreach ($requiredExtensions as $ext) {
        if (in_array($ext, $extensions)) {
            echo "✅ $ext\n";
        } else {
            echo "❌ $ext\n";
        }
    }
    
    echo "\nПроверка завершена успешно\n";
} else {
    echo "Ошибка: файл Yii.php не найден\n";
    exit(1);
} 