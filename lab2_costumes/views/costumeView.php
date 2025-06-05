<?php
// ================================
// 1. Запуск сесії та ініціалізація кошика
// ================================
session_start();
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
$cartCount = count($_SESSION['cart']);

// ================================
// 2. Підключення до БД та отримання ID з GET
// ================================
$pdo = new PDO('sqlite:' . __DIR__ . '/../database.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$costumeId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($costumeId <= 0) {
    // Якщо некоректний id – повертаємо на головну
    header('Location: ../home.view.php');
    exit;
}

// ================================
// 3. Обробка POST-запитів (кошик, редагування, відгук)
// ================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 3.1 Додавання до кошика
    if (isset($_POST['add_to_cart'])) {
        if (!in_array($costumeId, $_SESSION['cart'])) {
            $_SESSION['cart'][] = $costumeId;
        }
        $query = !empty($_GET) ? ('?' . $_SERVER['QUERY_STRING']) : '';
        header("Location: costumeView.php" . $query);
        exit;
    }

    // 3.2 Видалення одного елемента з кошика
    if (isset($_POST['remove_from_cart'])) {
        $removeId = intval($_POST['remove_from_cart']);
        $_SESSION['cart'] = array_filter(
            $_SESSION['cart'],
            function($x) use ($removeId) {
                return $x !== $removeId;
            }
        );
        $query = !empty($_GET) ? ('?' . $_SERVER['QUERY_STRING']) : '';
        header("Location: costumeView.php" . $query);
        exit;
    }

    // 3.3 Очищення вмісту кошика
    if (isset($_POST['clear_cart'])) {
        $_SESSION['cart'] = [];
        $query = !empty($_GET) ? ('?' . $_SERVER['QUERY_STRING']) : '';
        header("Location: costumeView.php" . $query);
        exit;
    }

    // 3.4 Оновлення даних костюма
    if (isset($_POST['update_costume'])) {
        $name        = trim($_POST['name']);
        $price       = floatval($_POST['price']);
        $category    = trim($_POST['category']);
        $size        = trim($_POST['size']);
        $available   = isset($_POST['available']) ? 1 : 0;
        $description = trim($_POST['description']);
        $photo       = trim($_POST['photo']);

        $updateStmt = $pdo->prepare("
            UPDATE costumes 
            SET name        = :name,
                price       = :price,
                category    = :category,
                size        = :size,
                available   = :available,
                description = :description,
                photo       = :photo
            WHERE id = :id
        ");
        $updateStmt->execute([
            ':name'        => $name,
            ':price'       => $price,
            ':category'    => $category,
            ':size'        => $size,
            ':available'   => $available,
            ':description' => $description,
            ':photo'       => $photo,
            ':id'          => $costumeId
        ]);
        $query = !empty($_GET) ? ('?' . $_SERVER['QUERY_STRING']) : '';
        header("Location: costumeView.php" . $query);
        exit;
    }

    // ================================
    // 3.5 Додавання відгуку (без БД, у файл)
    // ================================
    if (isset($_POST['submit_review'])) {
        $rawContent = trim($_POST['review_content']);
        if ($rawContent !== '') {
            // Визначаємо ім'я користувача: якщо залогінений – беремо з сесії, інакше – “Гість”
            $username = isset($_SESSION['username']) && trim($_SESSION['username']) !== ''
                      ? $_SESSION['username']
                      : 'Гість';

            // Шлях до папки з відгуками та до конкретного файлу
            $reviewsDir  = __DIR__ . '/../reviews';
            $reviewsFile = $reviewsDir . '/' . $costumeId . '.json';

            // Якщо папки reviews ще немає – створимо (з правами 0755)
            if (!is_dir($reviewsDir)) {
                mkdir($reviewsDir, 0755, true);
            }

            // Формуємо масив нового відгуку
            $newReview = [
                'username'   => $username,
                'content'    => $rawContent,
                'created_at' => date('c')  // ISO-8601 формат, наприклад "2025-06-05T14:23:00+00:00"
            ];

            // Зчитуємо існуючі відгуки (якщо файл є)
            if (file_exists($reviewsFile)) {
                $json       = file_get_contents($reviewsFile);
                $allReviews = json_decode($json, true);
                if (!is_array($allReviews)) {
                    $allReviews = [];
                }
            } else {
                $allReviews = [];
            }

            // Додаємо новий відгук на початок (щоб найновіші були угорі)
            array_unshift($allReviews, $newReview);

            // Записуємо назад у файл (форматований JSON, із LOCK_EX)
            $encoded = json_encode($allReviews, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            file_put_contents($reviewsFile, $encoded, LOCK_EX);

            // Після збереження – редірект на цю ж сторінку, щоби уникнути повторної відправки форми
            $query = !empty($_GET) ? ('?' . $_SERVER['QUERY_STRING']) : '';
            header("Location: costumeView.php" . $query);
            exit;
        }
    }
}

// ================================
// 4. Вибірка даних конкретного костюма
// ================================
$costumeStmt = $pdo->prepare("SELECT * FROM costumes WHERE id = :id");
$costumeStmt->execute([':id' => $costumeId]);
$costume = $costumeStmt->fetch(PDO::FETCH_ASSOC);
if (!$costume) {
    header('Location: ../home.view.php');
    exit;
}

// ================================
// 5. Підготовка даних кошика (щоб передати в header.php)
// ================================
if ($cartCount > 0) {
    $placeholders = implode(',', array_fill(0, $cartCount, '?'));
    $itemStmt     = $pdo->prepare("SELECT id, name, price, photo FROM costumes WHERE id IN ($placeholders)");
    $itemStmt->execute(array_values($_SESSION['cart']));
    $cartItems = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $cartItems = [];
}

// ================================
// 6. Зчитування відгуків для цього костюма (якщо файл існує)
// ================================
$reviewsDir  = __DIR__ . '/../reviews';
$reviewsFile = $reviewsDir . '/' . $costumeId . '.json';

if (file_exists($reviewsFile)) {
    $json    = file_get_contents($reviewsFile);
    $reviews = json_decode($json, true);
    if (!is_array($reviews)) {
        $reviews = [];
    }
} else {
    $reviews = [];
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($costume['name'], ENT_QUOTES, 'UTF-8') ?> — PHOTO GALLERY</title>
  <link rel="stylesheet" href="/lab2_costumes/css/style.css">
  <!-- Можна підключити Google Fonts, FontAwesome тощо -->
  <style>
    /* Якщо потрібно, можна внести додаткові швидкі стилі тут, 
       але краще додати їх у ваш style.css (див. приклад нижче) */
  </style>
</head>
<body>
  <div class="wrapper">
    <!-- Підключаємо готовий header -->
    <?php include __DIR__ . '/../header.php'; ?>

    <main class="main-content" style="padding: 20px 40px;">

      <!-- Хлібні крихти з “Назад” -->
      <div class="breadcrumbs" style="margin-bottom: 20px;">
        <a href="/lab2_costumes/views/home.view.php?lang=<?= htmlspecialchars($_GET['lang'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
          &larr; <?= htmlspecialchars($t['back'] ?? 'Назад', ENT_QUOTES, 'UTF-8') ?>
        </a>
      </div>

      <!-- Деталі одного костюма -->
      <div class="costume-detail" style="display: flex; gap: 2rem; flex-wrap: wrap;">
        <div class="costume-detail__img" style="flex: 1; min-width: 300px;">
          <?php
            // Формуємо коректний шлях до фото (якщо нема – default.jpg)
            $imgPath = $costume['photo'] 
                     ? $costume['photo'] 
                     : 'uploads/default.jpg';
            // Відображаємо:
            $imgSrc = htmlspecialchars($imgPath, ENT_QUOTES, 'UTF-8');
          ?>
          <img 
            src="/lab2_costumes/<?= $imgSrc ?>" 
            alt="<?= htmlspecialchars($costume['name'], ENT_QUOTES, 'UTF-8') ?>" 
            class="costume-detail__photo" 
            style="width: 100%; height: auto; object-fit: cover; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
        </div>
        <div class="costume-detail__info" style="flex: 1; min-width: 300px;">
          <h2 style="margin-top: 0;"><?= htmlspecialchars($costume['name'], ENT_QUOTES, 'UTF-8') ?></h2>
          <p class="costume-detail__price" style="font-size: 1.25rem; font-weight: 600; color: #007bff;">
            <?= number_format($costume['price'], 0, '.', ' ') ?> грн
          </p>

          <?php if (!empty($costume['category'])): ?>
            <p><strong><?= htmlspecialchars($t['category'] ?? 'Категорія', ENT_QUOTES, 'UTF-8') ?>:</strong> 
              <?= htmlspecialchars($costume['category'], ENT_QUOTES, 'UTF-8') ?>
            </p>
          <?php endif; ?>

          <?php if (!empty($costume['size'])): ?>
            <p><strong><?= htmlspecialchars($t['size'] ?? 'Розмір', ENT_QUOTES, 'UTF-8') ?>:</strong> 
              <?= htmlspecialchars($costume['size'], ENT_QUOTES, 'UTF-8') ?>
            </p>
          <?php endif; ?>

          <?php if (!empty($costume['description'])): ?>
            <p><strong><?= htmlspecialchars($t['description'] ?? 'Опис', ENT_QUOTES, 'UTF-8') ?>:</strong><br>
              <?= nl2br(htmlspecialchars($costume['description'], ENT_QUOTES, 'UTF-8')) ?>
            </p>
          <?php endif; ?>

          <?php if ($costume['available']): ?>
            <p class="available" style="color: green; font-weight: 600;">
              <?= htmlspecialchars($t['in_stock'] ?? 'В наявності', ENT_QUOTES, 'UTF-8') ?>
            </p>
          <?php else: ?>
            <p class="not-available" style="color: red; font-weight: 600;">
              <?= htmlspecialchars($t['out_of_stock'] ?? 'Немає в наявності', ENT_QUOTES, 'UTF-8') ?>
            </p>
          <?php endif; ?>

          <!-- Кнопка “Додати до кошика” -->
          <form method="POST" action="costumeView.php?id=<?= $costumeId ?>">
            <button 
              type="submit" 
              name="add_to_cart" 
              class="button-add-to-cart"
              style="
                background-color: #28a745; 
                color: #fff; 
                padding: 0.6rem 1rem; 
                border: none; 
                border-radius: 4px; 
                cursor: pointer;
                font-size: 1rem;
                margin-top: 1rem;
              "
              <?= in_array($costumeId, $_SESSION['cart']) ? 'disabled' : '' ?>>
              <i class="fas fa-cart-plus"></i>
              <?= in_array($costumeId, $_SESSION['cart']) 
                  ? htmlspecialchars($t['in_cart'] ?? 'У кошику', ENT_QUOTES, 'UTF-8') 
                  : htmlspecialchars($t['add_to_cart'] ?? 'Додати до кошика', ENT_QUOTES, 'UTF-8') ?>
            </button>
          </form>

          <!-- Кнопка “Редагувати” -->
          <button 
            id="editBtn" 
            class="button-edit"
            style="
              background-color: #ffc107; 
              color: #000; 
              padding: 0.5rem 1rem; 
              border: none; 
              border-radius: 4px; 
              cursor: pointer; 
              font-size: 1rem; 
              margin-left: 1rem;
              margin-top: 1rem;
            ">
            <i class="fas fa-edit"></i> <?= htmlspecialchars($t['edit'] ?? 'Редагувати', ENT_QUOTES, 'UTF-8') ?>
          </button>
        </div>
      </div><!-- /.costume-detail -->

      <!-- Модальне вікно “Редагувати костюм” -->
      <div 
        id="editModal" 
        class="modal" 
        style="
          display: none;
          position: fixed;
          top: 0; left: 0; right: 0; bottom: 0;
          background-color: rgba(0,0,0,0.5);
          align-items: center;
          justify-content: center;
          z-index: 1000;
        ">
        <div 
          class="modal__content" 
          style="
            background-color: #fff; 
            padding: 20px; 
            border-radius: 8px; 
            width: 90%; 
            max-width: 500px;
            position: relative;
          ">
          <button 
            class="modal__close" 
            id="closeModal"
            style="
              position: absolute;
              top: 10px; 
              right: 10px; 
              background: none; 
              border: none; 
              font-size: 1.5rem; 
              cursor: pointer;
            ">
            &times;
          </button>
          <h2 style="margin-top: 0;"><?= htmlspecialchars($t['edit_costume'] ?? 'Редагування костюма', ENT_QUOTES, 'UTF-8') ?></h2>
          <form method="POST" action="costumeView.php?id=<?= $costumeId ?>">
            <div class="form-group" style="margin-bottom: 1rem;">
              <label for="name"><?= htmlspecialchars($t['name'] ?? 'Назва', ENT_QUOTES, 'UTF-8') ?></label>
              <input 
                type="text" 
                id="name" 
                name="name" 
                value="<?= htmlspecialchars($costume['name'], ENT_QUOTES, 'UTF-8') ?>" 
                required
                style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius:4px;"
              >
            </div>
            <div class="form-group" style="margin-bottom: 1rem;">
              <label for="price"><?= htmlspecialchars($t['price'] ?? 'Ціна', ENT_QUOTES, 'UTF-8') ?></label>
              <input 
                type="number" 
                id="price" 
                name="price" 
                value="<?= htmlspecialchars($costume['price'], ENT_QUOTES, 'UTF-8') ?>" 
                required 
                step="0.01"
                style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius:4px;"
              >
            </div>
            <div class="form-group" style="margin-bottom: 1rem;">
              <label for="category"><?= htmlspecialchars($t['category'] ?? 'Категорія', ENT_QUOTES, 'UTF-8') ?></label>
              <input 
                type="text" 
                id="category" 
                name="category" 
                value="<?= htmlspecialchars($costume['category'], ENT_QUOTES, 'UTF-8') ?>" 
                required
                style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius:4px;"
              >
            </div>
            <div class="form-group" style="margin-bottom: 1rem;">
              <label for="size"><?= htmlspecialchars($t['size'] ?? 'Розмір', ENT_QUOTES, 'UTF-8') ?></label>
              <input 
                type="text" 
                id="size" 
                name="size" 
                value="<?= htmlspecialchars($costume['size'], ENT_QUOTES, 'UTF-8') ?>"
                style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius:4px;"
              >
            </div>
            <div class="form-group" style="margin-bottom: 1rem;">
              <label>
                <input 
                  type="checkbox" 
                  name="available" 
                  <?= $costume['available'] ? 'checked' : '' ?>
                >
                <?= htmlspecialchars($t['available'] ?? 'Доступний', ENT_QUOTES, 'UTF-8') ?>
              </label>
            </div>
            <div class="form-group" style="margin-bottom: 1rem;">
              <label for="description"><?= htmlspecialchars($t['description'] ?? 'Опис', ENT_QUOTES, 'UTF-8') ?></label>
              <textarea 
                id="description" 
                name="description" 
                style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius:4px;"
              ><?= htmlspecialchars($costume['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
            <div class="form-group" style="margin-bottom: 1rem;">
              <label for="photo"><?= htmlspecialchars($t['photo_path'] ?? "Шлях до фото (наприклад: uploads/ім'я_файлу.jpg)", ENT_QUOTES, 'UTF-8') ?></label>
              <input 
                type="text" 
                id="photo" 
                name="photo" 
                value="<?= htmlspecialchars($costume['photo'], ENT_QUOTES, 'UTF-8') ?>"
                style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius:4px;"
              >
            </div>
            <button 
              type="submit" 
              name="update_costume"
              style="
                background-color: #007bff; 
                color: #fff; 
                padding: 0.6rem 1.2rem; 
                border: none; 
                border-radius: 4px; 
                cursor: pointer;
                font-size: 1rem;
              ">
              <?= htmlspecialchars($t['save'] ?? 'Зберегти', ENT_QUOTES, 'UTF-8') ?>
            </button>
          </form>
        </div>
      </div>

      <script>
        // JS для відкриття/закриття модального вікна редагування
        document.addEventListener('DOMContentLoaded', () => {
          const editBtn    = document.getElementById('editBtn');
          const editModal  = document.getElementById('editModal');
          const closeModal = document.getElementById('closeModal');

          editBtn.addEventListener('click', () => {
            editModal.style.display = 'flex';
          });
          closeModal.addEventListener('click', () => {
            editModal.style.display = 'none';
          });
          window.addEventListener('click', (e) => {
            if (e.target === editModal) {
              editModal.style.display = 'none';
            }
          });
        });
      </script>

      <!-- (Опціонально) Блок “Товари у вашому кошику” -->
      <?php if ($cartCount > 0): ?>
        <section style="margin-top:40px;">
          <h3><?= htmlspecialchars($t['in_your_cart'] ?? 'Товари у вашому кошику', ENT_QUOTES, 'UTF-8') ?></h3>
          <div 
            class="cart-overview-grid" 
            style="
              display: grid;
              grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
              gap: 1rem;
              margin-top: 1rem;
            ">
            <?php foreach ($cartItems as $ci): ?>
              <div 
                class="cart-overview-card" 
                onclick="window.location.href='costumeView.php?id=<?= intval($ci['id']) ?>&lang=<?= htmlspecialchars($_GET['lang'] ?? '', ENT_QUOTES, 'UTF-8') ?>'"
                style="
                  background-color: #fff;
                  border-radius: 8px;
                  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
                  padding: 0.5rem;
                  cursor: pointer;
                  text-align: center;
                ">
                <img 
                  src="/lab2_costumes/<?= htmlspecialchars($ci['photo'] ?: 'uploads/default.jpg', ENT_QUOTES, 'UTF-8') ?>" 
                  alt="<?= htmlspecialchars($ci['name'], ENT_QUOTES, 'UTF-8') ?>" 
                  style="object-fit: cover; width: 100%; height: 100px; border-radius: 4px;">
                <p style="margin: 0.5rem 0 0.25rem; font-weight: 500;">
                  <?= htmlspecialchars($ci['name'], ENT_QUOTES, 'UTF-8') ?>
                </p>
                <p style="margin: 0; color: #555;">
                  <?= number_format($ci['price'], 0, '.', ' ') ?> грн
                </p>
              </div>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>

      <!-- ================================ -->
      <!-- 7. Блок “Відгуки” без БД (JSON)    -->
      <!-- ================================ -->
      <section class="reviews-section" style="margin-top: 40px;">
        <h3 style="margin-top: 0; margin-bottom: 1rem;"><?= htmlspecialchars($t['reviews'] ?? 'Відгуки', ENT_QUOTES, 'UTF-8') ?></h3>

        <!-- Форма для додавання нового відгуку -->
        <form method="POST" action="costumeView.php?id=<?= $costumeId ?>" class="review-form" style="margin-bottom: 1.5rem;">
          <label for="review_content" style="font-weight: 500;">
            <?= htmlspecialchars($t['your_review'] ?? 'Ваш відгук', ENT_QUOTES, 'UTF-8') ?>:
          </label><br>
          <textarea 
            id="review_content" 
            name="review_content" 
            rows="4"
            placeholder="<?= htmlspecialchars($t['review_placeholder'] ?? 'Напишіть свій відгук тут...', ENT_QUOTES, 'UTF-8') ?>"
            required
            style="
              width: 100%; 
              padding: 0.5rem; 
              border: 1px solid #ccc; 
              border-radius: 4px;
              font-family: inherit;
              font-size: 1rem;
              resize: vertical;
            "></textarea><br>
          <button 
            type="submit" 
            name="submit_review" 
            class="btn-review-submit"
            style="
              margin-top: 0.5rem;
              background-color: #28a745;
              color: #fff;
              padding: 0.5rem 1rem;
              border: none;
              border-radius: 4px;
              cursor: pointer;
              font-size: 1rem;
            ">
            <?= htmlspecialchars($t['submit_review'] ?? 'Надіслати', ENT_QUOTES, 'UTF-8') ?>
          </button>
        </form>

        <!-- Перелік існуючих відгуків -->
        <?php if (!empty($reviews)): ?>
          <ul class="reviews-list" style="list-style: none; padding-left:0; margin:0;">
            <?php foreach ($reviews as $rev): ?>
              <li class="single-review" style="border-top: 1px solid #e0e0e0; padding: 0.75rem 0;">
                <div class="review-meta" style="font-size: 0.85rem; color: #555; margin-bottom: 0.3rem;">
                  <span class="review-user" style="font-weight: 600;">
                    <?= htmlspecialchars($rev['username'], ENT_QUOTES, 'UTF-8') ?>
                  </span>
                  <span class="review-date" style="margin-left: 1rem; color: #888;">
                    <?= date('d.m.Y H:i', strtotime($rev['created_at'])) ?>
                  </span>
                </div>
                <div class="review-content" style="font-size: 1rem; line-height: 1.4; color: #333;">
                  <?= nl2br(htmlspecialchars($rev['content'], ENT_QUOTES, 'UTF-8')) ?>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p class="no-reviews" style="font-style: italic; color: #666;">
            <?= htmlspecialchars($t['no_reviews'] ?? 'Поки що немає відгуків. Станьте першим!', ENT_QUOTES, 'UTF-8') ?>
          </p>
        <?php endif; ?>
      </section>

    </main>
  </div>
</body>
</html>
