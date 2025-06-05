<?php
namespace App;  

class Database {
    private $pdo;

    public function __construct($dbname = "database.sqlite") {
        try {
            $this->pdo = new \PDO("sqlite:$dbname");
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            $this->createTable();     // Створення таблиці, якщо її ще немає

            $this->fillDemoData();    // Заповнення тестовими даними

        } catch (\PDOException $e) {
            die("Неможливо створити базу даних: " . $e->getMessage());
        }
    }

    // Метод для створення таблиці costumes
    private function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS costumes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            category TEXT NOT NULL,
            size TEXT NOT NULL,
            price REAL NOT NULL,
            available TEXT NOT NULL,
            photo TEXT,
            description TEXT
        )";
        $this->pdo->exec($sql);
    }

     // Заповнення таблиці демонстраційним записом, якщо таблиця порожня
    private function fillDemoData() {
        $count = $this->pdo->query("SELECT COUNT(*) FROM costumes")->fetchColumn();
        if ($count == 0) {
            $stmt = $this->pdo->prepare("INSERT INTO costumes (name, category, size, price, available, photo, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                "Демо-костюм", "Класика", "M", 120.0, "так", "uploads/default.jpg", "Це демо-костюм"
            ]);
        }
    }

    // Метод для отримання усіх костюмів 
    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM costumes");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Метод для отримання усіх костюмів 
    public function getByCategory($category) {
        $stmt = $this->pdo->prepare("SELECT * FROM costumes WHERE category = ?");
        $stmt->execute([$category]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Додавання нового костюму до бази
    public function add($name, $category, $size, $price, $available, $photo, $description) {
        $stmt = $this->pdo->prepare("INSERT INTO costumes (name, category, size, price, available, photo, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $category, $size, $price, $available, $photo, $description]);
    }

     // Видалення костюму за id
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM costumes WHERE id = ?");
        $stmt->execute([$id]);
    }

    // Отримання одного костюму за id 
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM costumes WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    // Оновлення інформації про костюм 
    public function update($id, $name, $category, $size, $price, $available, $photo, $description) {
        $stmt = $this->pdo->prepare("UPDATE costumes SET name = ?, category = ?, size = ?, price = ?, available = ?, photo = ?, description = ? WHERE id = ?");
        $stmt->execute([$name, $category, $size, $price, $available, $photo, $description, $id]);
    }

    // Масове додавання костюмів в рамках транзакції для цілісності даних
    public function addMultipleCostumes(array $costumes) {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("INSERT INTO costumes (name, category, size, price, available, photo, description) VALUES (?, ?, ?, ?, ?, ?, ?)");

            foreach ($costumes as $costume) {
                $stmt->execute([
                    $costume['name'],
                    $costume['category'],
                    $costume['size'],
                    $costume['price'],
                    $costume['available'],
                    $costume['photo'],
                    $costume['description']
                ]);
            }

            $this->pdo->commit();
        } catch (\PDOException $e) {
            $this->pdo->rollBack();

            echo "PDOException: " . $e->getMessage() . PHP_EOL;
            echo "SQLSTATE error code: " . $e->getCode() . PHP_EOL;
            echo "Driver-specific error info: ";
            print_r($e->errorInfo);

            throw $e;
        }
    }
}
