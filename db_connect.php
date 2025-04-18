<?php
$servername = "localhost";
$username = "root";
$password = "root"; // Стандартный пароль для MAMP
$dbname = "calorie_calculator";

// Создаем соединение
$conn = new mysqli($servername, $username, $password);

// Проверяем соединение
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// Создаем базу данных, если она не существует
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    // Выбираем базу данных
    $conn->select_db($dbname);
    
    // Создаем таблицу, если она не существует
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        height INT(11) NOT NULL,
        weight INT(11) NOT NULL,
        age INT(11) NOT NULL,
        activity_level VARCHAR(20) NOT NULL,
        gender VARCHAR(10) NOT NULL,
        calories INT(11),
        proteins_min FLOAT,
        proteins_max FLOAT,
        fats_min FLOAT,
        fats_max FLOAT,
        carbs_min FLOAT,
        carbs_max FLOAT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql) === TRUE) {
        // Таблица создана успешно
    } else {
        echo "Ошибка создания таблицы: " . $conn->error;
    }
} else {
    echo "Ошибка создания базы данных: " . $conn->error;
}
?> 