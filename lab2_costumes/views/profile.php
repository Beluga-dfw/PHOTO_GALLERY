<?php
session_start();
include __DIR__ . '/../header.php';

$activeTab = $_GET['tab'] ?? 'user';

if ($activeTab === 'admin') {
    try {
        $pdo = new PDO('sqlite:' . __DIR__ . '/../database.sqlite');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (Exception $e) {
        die("<p style='color:red;'>Не вдалося підключитися до БД: " . htmlspecialchars($e->getMessage()) . "</p>");
    }

    // Видалення костюма
    if (isset($_POST['delete_id'])) {
        $delId   = intval($_POST['delete_id']);
        $delStmt = $pdo->prepare("DELETE FROM costumes WHERE id = ?");
        $delStmt->execute([$delId]);
        header("Location: profile.php?tab=admin");
        exit;
    }

    // Додавання нового костюма
    $errors = [];
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_costume'])) {
        $name        = trim($_POST['name'] ?? '');
        $category    = trim($_POST['category'] ?? '');
        $size        = trim($_POST['size'] ?? '');
        $price       = floatval($_POST['price'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $available   = ($_POST['available'] ?? '') === 'так' ? 1 : 0;

        if ($name === '') {
            $errors[] = 'Назва не може бути порожньою';
        }
        if ($category === '') {
            $errors[] = 'Категорія не може бути порожньою';
        }
        if ($size === '') {
            $errors[] = 'Розмір не може бути порожнім';
        }
        if ($price <= 0) {
            $errors[] = 'Ціна повинна бути більшою за 0';
        }
        if ($description === '') {
            $errors[] = 'Опис не може бути порожнім';
        }

        $photoPath = '';
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $tmpName   = $_FILES['photo']['tmp_name'];
                $origName  = basename($_FILES['photo']['name']);
                $ext       = pathinfo($origName, PATHINFO_EXTENSION);
                $newName   = time() . '_' . bin2hex(random_bytes(5)) . '.' . $ext;
                $uploadDir = __DIR__ . '/../uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $destPath = $uploadDir . $newName;
                if (move_uploaded_file($tmpName, $destPath)) {
                    $photoPath = 'uploads/' . $newName;
                } else {
                    $errors[] = 'Не вдалося завантажити фото';
                }
            } else {
                $errors[] = 'Помилка при завантаженні файлу';
            }
        }

        if (empty($errors)) {
            try {
                $ins = $pdo->prepare("
                    INSERT INTO costumes 
                        (name, category, size, price, available, photo, description)
                    VALUES 
                        (:name, :category, :size, :price, :available, :photo, :description)
                ");
                $ins->execute([
                    ':name'        => $name,
                    ':category'    => $category,
                    ':size'        => $size,
                    ':price'       => $price,
                    ':available'   => $available,
                    ':photo'       => $photoPath,
                    ':description' => $description
                ]);
                header("Location: profile.php?tab=admin");
                exit;
            } catch (PDOException $e) {
                $errors[] = 'Помилка при додаванні до БД: ' . htmlspecialchars($e->getMessage());
            }
        }
    }

    // Завантажуємо всі костюми
    try {
        $stmt        = $pdo->query("SELECT * FROM costumes");
        $allCostumes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $allCostumes = [];
        $dbError     = "Не вдалося завантажити костюми: " . htmlspecialchars($e->getMessage());
    }

    // Отримуємо кількість відвідувань із таблиці site_stats
    try {
        $stmt_visits = $pdo->prepare("SELECT stat_value FROM site_stats WHERE stat_key = 'visits'");
        $stmt_visits->execute();
        $visitsCount = (int)$stmt_visits->fetchColumn();
    } catch (Exception $e) {
        $visitsCount = 0;
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Профіль / Адмінка</title>
    <link rel="stylesheet" href="/lab2_costumes/css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            background-color: #f0f2f5;
        }
        .tabs {
            margin-bottom: 20px;
            padding-left: 20px;
        }
        .tabs a {
            padding: 10px 20px;
            text-decoration: none;
            color: #555;
            border: 1px solid #ddd;
            border-bottom: none;
            border-radius: 4px 4px 0 0;
            margin-right: 5px;
            background-color: #eaeaea;
            font-weight: 500;
        }
        .tabs a.active {
            background-color: #fff;
            font-weight: bold;
            color: #000;
            border-bottom: 1px solid #fff;
        }
        h1 {
            font-size: 28px;
            margin: 0 0 20px 20px;
            color: #333;
        }
        #showAddForm {
            display: inline-block;
            margin: 0 0 20px 20px;
            background: #0d6efd;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            cursor: pointer;
        }
        #showAddForm:hover {
            background: #084298;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto 40px;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .add-form {
            max-width: 600px;
            margin: 0 auto 30px;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            font-family: Arial, sans-serif;
            display: none;
        }
        .add-form h2 {
            text-align: center;
            margin-bottom: 20px;
            font-weight: 700;
            color: #333;
        }
        .add-form label {
            display: block;
            margin-top: 15px;
            font-weight: 600;
            color: #333;
        }
        .add-form input[type="text"],
        .add-form input[type="number"],
        .add-form select,
        .add-form textarea,
        .add-form input[type="file"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        .add-form input[type="text"]:focus,
        .add-form input[type="number"]:focus,
        .add-form select:focus,
        .add-form textarea:focus,
        .add-form input[type="file"]:focus {
            border-color: rgb(47, 0, 255);
            outline: none;
        }
        .add-form textarea {
            resize: vertical;
            min-height: 80px;
        }
        .add-form button {
            margin-top: 25px;
            padding: 12px 25px;
            background: rgb(8, 38, 102);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 20px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
            display: block;
            width: 100%;
        }
        .add-form button:hover {
            background: rgb(66, 124, 212);
        }
        .table-container {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
        }
        th, td {
            padding: 14px 20px;
            text-align: center;
            border-bottom: 1px solid #dee2e6;
        }
        th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        tr:hover {
            background-color: #f1f3f5;
        }
        .delete-btn {
            background-color: #dc3545;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 6px 12px;
            cursor: pointer;
            font-family: Arial, sans-serif;
        }
        .delete-btn:hover {
            background-color: #b21f2d;
        }
        .visits-counter {
            margin: 0 20px 20px;
            font-size: 1rem;
            color: #333;
            font-family: Arial, sans-serif;
        }
        .visits-counter span {
            font-weight: bold;
            color: #0d6efd;
        }
    </style>
</head>
<body>

    <div class="tabs">
        <a href="profile.php?tab=user"  class="<?= $activeTab === 'user'  ? 'active' : '' ?>"><?= __('profile') ?></a>
        <a href="profile.php?tab=admin" class="<?= $activeTab === 'admin' ? 'active' : '' ?>"><?= __('profile') ?>: Admin</a>
    </div>

    <?php if ($activeTab === 'user'): ?>
        <h1>Інформація про користувача</h1>
        <div class="container" style="max-width: 400px; padding: 20px;">
            <div style="display: flex; margin-bottom: 12px;">
                <label style="width: 120px; font-weight: 600;">Прізвище:</label>
                <span>Савченко</span>
            </div>
            <div style="display: flex; margin-bottom: 12px;">
                <label style="width: 120px; font-weight: 600;">Ім’я:</label>
                <span>Єгор</span>
            </div>
            <div style="display: flex; margin-bottom: 12px;">
                <label style="width: 120px; font-weight: 600;">E-mail:</label>
                <span>beluga2777@gmail.com</span>
            </div>
            <div style="display: flex; margin-bottom: 12px;">
                <label style="width: 120px; font-weight: 600;">Дата народження:</label>
                <span>06.06.2006</span>
            </div>
        </div>

    <?php else: ?>
        <h1>Адмін: Костюми</h1>

        <div class="visits-counter">
            Кількість відвідувань сайту: <span><?= htmlspecialchars($visitsCount) ?></span>
        </div>

        <button id="showAddForm">➕ Додати новий костюм</button>

        <form class="add-form" method="POST" action="profile.php?tab=admin" enctype="multipart/form-data">
            <h2>Додати новий костюм</h2>

            <?php if (!empty($errors)): ?>
                <ul style="color: red; margin-bottom: 15px; font-family: Arial, sans-serif;">
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <label for="name">Назва:</label>
            <input type="text" id="name" name="name" required>

            <label for="category">Категорія:</label>
            <input type="text" id="category" name="category" required>

            <label for="size">Розмір:</label>
            <input type="text" id="size" name="size" required>

            <label for="price">Ціна:</label>
            <input type="number" id="price" name="price" step="0.01" required>

            <label for="description">Опис:</label>
            <textarea id="description" name="description" rows="4" required></textarea>

            <label for="available">Доступність:</label>
            <select id="available" name="available">
                <option value="так">так</option>
                <option value="ні">ні</option>
            </select>

            <label for="photo">Фото костюму:</label>
            <input type="file" id="photo" name="photo">

            <input type="hidden" name="add_costume" value="1">
            <button type="submit">Додати</button>
        </form>

        <?php if (!empty($dbError)): ?>
            <p style="color: red; text-align: center; font-family: Arial, sans-serif;"><?= htmlspecialchars($dbError) ?></p>
        <?php endif; ?>

        <div class="container">
            <?php if (empty($allCostumes)): ?>
                <p style="font-size: 1.2rem; text-align: center; color: #555; font-family: Arial, sans-serif;">
                    Костюмів не знайдено.
                </p>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <tr>
                            <th>ID</th>
                            <th>Назва</th>
                            <th>Категорія</th>
                            <th>Розмір</th>
                            <th>Ціна</th>
                            <th>Доступність</th>
                            <th>Дії</th>
                        </tr>
                        <?php foreach ($allCostumes as $costume): ?>
                            <tr>
                                <td><?= $costume['id'] ?></td>
                                <td><?= htmlspecialchars($costume['name'], ENT_QUOTES) ?></td>
                                <td><?= htmlspecialchars($costume['category'], ENT_QUOTES) ?></td>
                                <td><?= htmlspecialchars($costume['size'], ENT_QUOTES) ?></td>
                                <td><?= number_format($costume['price'], 2, ',', ' ') ?> грн</td>
                                <td><?= $costume['available'] ? 'так' : 'ні' ?></td>
                                <td>
                                    <form method="POST" action="profile.php?tab=admin" style="display:inline;">
                                        <input type="hidden" name="delete_id" value="<?= $costume['id'] ?>">
                                        <button type="submit" class="delete-btn">Видалити</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div style="padding-left: 20px; font-family: Arial, sans-serif; margin-bottom: 40px;">
            <a href="profile.php?tab=user" style="margin-right: 24px; color: #007bff; text-decoration: none;">⭠ Профіль користувача</a>
            <a href="home.view.php" style="color: #007bff; text-decoration: none;">⭠ На головну</a>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const showBtn = document.getElementById('showAddForm');
                const formDiv = document.querySelector('.add-form');

                showBtn.addEventListener('click', function() {
                    if (formDiv.style.display === 'none' || formDiv.style.display === '') {
                        formDiv.style.display = 'block';
                        formDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    } else {
                        formDiv.style.display = 'none';
                    }
                });
            });
        </script>
    <?php endif; ?>

</body>
</html>
