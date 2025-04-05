-- Устанавливаем кодировку
SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

-- Устанавливаем права доступа
GRANT ALL ON `fitbase`.* TO 'fitbase'@'%';

-- Очищаем схему базы данных
USE `fitbase`;

-- Дополнительные настройки при необходимости 