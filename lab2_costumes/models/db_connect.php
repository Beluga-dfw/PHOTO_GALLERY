<?php
try {
    $pdo = new PDO("sqlite:database.sqlite");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo " Підключення до бази даних успішне!";
} catch (PDOException $e) {
    die(" Помилка з'єднання: " . $e->getMessage());
}
