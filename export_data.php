<?php
// Подключение к базе данных
require_once 'db_connect.php';

// Выбор всех записей из таблицы users
$sql = "SELECT * FROM users ORDER BY created_at DESC";
$result = $conn->query($sql);

// Проверка, есть ли записи
if ($result->num_rows > 0) {
    echo "<h2>Данные из базы данных калькулятора калорий</h2>";
    echo "<table border='1'>";
    echo "<tr>
            <th>ID</th>
            <th>Рост (см)</th>
            <th>Вес (кг)</th>
            <th>Возраст</th>
            <th>Уровень активности</th>
            <th>Пол</th>
            <th>Калории</th>
            <th>Белки (мин)</th>
            <th>Белки (макс)</th>
            <th>Жиры (мин)</th>
            <th>Жиры (макс)</th>
            <th>Углеводы (мин)</th>
            <th>Углеводы (макс)</th>
            <th>Дата создания</th>
          </tr>";
    
    // Вывод данных
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row["id"] . "</td>";
        echo "<td>" . $row["height"] . "</td>";
        echo "<td>" . $row["weight"] . "</td>";
        echo "<td>" . $row["age"] . "</td>";
        echo "<td>" . $row["activity_level"] . "</td>";
        echo "<td>" . $row["gender"] . "</td>";
        echo "<td>" . $row["calories"] . "</td>";
        echo "<td>" . $row["proteins_min"] . "</td>";
        echo "<td>" . $row["proteins_max"] . "</td>";
        echo "<td>" . $row["fats_min"] . "</td>";
        echo "<td>" . $row["fats_max"] . "</td>";
        echo "<td>" . $row["carbs_min"] . "</td>";
        echo "<td>" . $row["carbs_max"] . "</td>";
        echo "<td>" . $row["created_at"] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>В базе данных нет записей</p>";
}

// Закрываем соединение
$conn->close();
?> 