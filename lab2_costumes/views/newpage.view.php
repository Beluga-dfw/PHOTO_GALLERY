<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>PHOTO GALLERY</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header>PHOTO GALLERY</header>
<nav>
    <a href="home.php">Головна</a>
     <a href="popular.php">Популярні</a>
    <a href="add.php">Додати костюм</a>
    <a href="index.php">Адмінка</a>
    <a href="form.php">Авторизація</a>
    <a href="chat.php">Чат</a>
</nav>

<div class="container">
    <!-- Пошук за назвою -->
    <form method="GET" style="text-align: center; margin-bottom: 20px;">
        <input type="text" name="search" placeholder="Пошук за назвою..." style="padding: 8px; width: 250px;"
               value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        <button type="submit">🔍 Пошук</button>
    </form>

    <!-- Категорії -->
    <div style="text-align: center; margin-bottom: 30px;">
        <a href="home.php" style="margin: 0 10px; <?= $category === '' ? 'font-weight:bold;' : '' ?>">Усі</a>
        <a href="home.php?category=костюм" style="margin: 0 10px; <?= $category === 'костюм' ? 'font-weight:bold;' : '' ?>">Костюм</a>
        <a href="home.php?category=історія" style="margin: 0 10px; <?= $category === 'історія' ? 'font-weight:bold;' : '' ?>">Історія</a>
        <a href="home.php?category=карнавал" style="margin: 0 10px; <?= $category === 'карнавал' ? 'font-weight:bold;' : '' ?>">Карнавал</a>
            <a href="home.php?category=сукня" style="margin: 0 10px; <?= $category === 'карнавал' ? 'font-weight:bold;' : '' ?>">Сукні</a>
        <a href="home.php?category=інше" style="margin: 0 10px; <?= $category === 'інше' ? 'font-weight:bold;' : '' ?>">Інше</a>
    </div>

    <!-- Вивід костюмів -->
    <?php if (count($costumes) > 0): ?>
        <?php foreach ($costumes as $costume): ?>
            <a href="costume.php?id=<?= $costume['id'] ?>" style="text-decoration: none; color: inherit;">
                <div class="card">
                    <img src="<?= htmlspecialchars($costume['photo'] ?? 'uploads/no-image.png') ?>" alt="Фото">
                    <div class="card-title"> <?= htmlspecialchars($costume['name']) ?> </div>
                    <div class="card-price"> <?= htmlspecialchars($costume['price']) ?> грн </div>
                </div>
            </a>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="text-align: center;">Нічого не знайдено.</p>
    <?php endif; ?>
</div>
</body>
</html>
