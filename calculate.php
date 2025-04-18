<?php
// Включаем отображение ошибок для отладки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Логируем запрос
file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - Запрос получен\n", FILE_APPEND);

try {
    // Подключаемся к базе данных
    require_once 'db_connect.php';
    
    // Получаем данные из POST-запроса
    $raw_data = file_get_contents('php://input');
    file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - Полученные данные: " . $raw_data . "\n", FILE_APPEND);
    
    $data = json_decode($raw_data, true);
    
    // Проверяем, что все необходимые данные получены
    if (!isset($data['height']) || !isset($data['weight']) || !isset($data['age']) || 
        !isset($data['activity']) || !isset($data['gender'])) {
        file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - Ошибка: не все данные предоставлены\n", FILE_APPEND);
        echo json_encode(['error' => 'Не все данные предоставлены']);
        exit;
    }
    
    $height = intval($data['height']);
    $weight = intval($data['weight']);
    $age = intval($data['age']);
    $activity = $data['activity'];
    $gender = $data['gender'];
    
    file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - Данные получены: рост=$height, вес=$weight, возраст=$age, активность=$activity, пол=$gender\n", FILE_APPEND);
    
    // Проверяем, есть ли уже такой расчет в базе данных
    $sql = "SELECT * FROM users WHERE height = ? AND weight = ? AND age = ? AND activity_level = ? AND gender = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiss", $height, $weight, $age, $activity, $gender);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Если расчет уже существует, возвращаем его
        $row = $result->fetch_assoc();
        $response = [
            'message' => sprintf(
                "Ваша суточная потребность составляет: %d Ккал\n" .
                "Белки: от %.1f г до %.1f г\n" .
                "Жиры: от %.1f г до %.1f г\n" .
                "Углеводы: от %.1f г до %.1f г\n\n" .
                "Такая энергетическая и пищевая ценность рациона позволит Вам стабилизировать массу тела и обеспечивать организм необходимыми питательными веществами.",
                $row['calories'],
                $row['proteins_min'],
                $row['proteins_max'],
                $row['fats_min'],
                $row['fats_max'],
                $row['carbs_min'],
                $row['carbs_max']
            ),
            'calories' => $row['calories'],
            'proteinsMin' => $row['proteins_min'],
            'proteinsMax' => $row['proteins_max'],
            'fatsMin' => $row['fats_min'],
            'fatsMax' => $row['fats_max'],
            'carbsMin' => $row['carbs_min'],
            'carbsMax' => $row['carbs_max']
        ];
        echo json_encode($response);
        exit;
    }

    // Если расчета нет, выполняем новый расчет
    // Рассчитываем базовый обмен веществ (BMR)
    $bmr = 0;
    if ($gender == 'male') {
        $bmr = 88.362 + (13.397 * $weight) + (4.799 * $height) - (5.677 * $age);
    } else {
        $bmr = 447.593 + (9.247 * $weight) + (3.098 * $height) - (4.330 * $age);
    }

    // Коэффициент активности
    $activityMultiplier = 1.2; // По умолчанию
    switch ($activity) {
        case 'высокая':
            $activityMultiplier = 1.725;
            break;
        case 'средняя':
            $activityMultiplier = 1.55;
            break;
        case 'низкая':
            $activityMultiplier = 1.375;
            break;
        case 'очень низкая':
            $activityMultiplier = 1.2;
            break;
    }

    // Суточная потребность в калориях
    $dailyCalories = (int)($bmr * $activityMultiplier);

    // Расчет белков, жиров и углеводов
    $proteinsMin = $weight * 1.5;
    $proteinsMax = $weight * 2.0;
    $fatsMin = $dailyCalories * 0.25 / 9;
    $fatsMax = $dailyCalories * 0.35 / 9;
    $carbsMin = $dailyCalories * 0.45 / 4;
    $carbsMax = $dailyCalories * 0.60 / 4;

    // Сохраняем результаты в базу данных
    $sql = "INSERT INTO users (height, weight, age, activity_level, gender, calories, 
            proteins_min, proteins_max, fats_min, fats_max, carbs_min, carbs_max) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiissidddddd", $height, $weight, $age, $activity, $gender, $dailyCalories, 
                      $proteinsMin, $proteinsMax, $fatsMin, $fatsMax, $carbsMin, $carbsMax);
    $stmt->execute();

    // Формируем ответ
    $response = [
        'message' => sprintf(
            "Ваша суточная потребность составляет: %d Ккал\n" .
            "Белки: от %.1f г до %.1f г\n" .
            "Жиры: от %.1f г до %.1f г\n" .
            "Углеводы: от %.1f г до %.1f г\n\n" .
            "Такая энергетическая и пищевая ценность рациона позволит Вам стабилизировать массу тела и обеспечивать организм необходимыми питательными веществами.",
            $dailyCalories,
            $proteinsMin,
            $proteinsMax,
            $fatsMin,
            $fatsMax,
            $carbsMin,
            $carbsMax
        ),
        'calories' => $dailyCalories,
        'proteinsMin' => $proteinsMin,
        'proteinsMax' => $proteinsMax,
        'fatsMin' => $fatsMin,
        'fatsMax' => $fatsMax,
        'carbsMin' => $carbsMin,
        'carbsMax' => $carbsMax
    ];

    echo json_encode($response);
} catch (Exception $e) {
    file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - Ошибка: " . $e->getMessage() . "\n", FILE_APPEND);
    echo json_encode(['error' => 'Произошла внутренняя ошибка: ' . $e->getMessage()]);
}
$conn->close();
?> 