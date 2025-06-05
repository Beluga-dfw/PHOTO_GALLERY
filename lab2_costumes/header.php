<?php
// =======================
// header.php — мультимовність
// =======================

// 0) Буферизація та сесія
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1) Доступні мови та масив перекладів
$available_languages = [
    'uk' => 'Укр',
    'en' => 'Eng',
    'ru' => 'Рус',
    'de' => 'Deу'
];
if (isset($_GET['lang']) && array_key_exists($_GET['lang'], $available_languages)) {
    $_SESSION['lang'] = $_GET['lang'];
}
$lang_code = $_SESSION['lang'] ?? 'uk';

$TRANSLATIONS = [
    'uk' => [
        // header, кошик, логін/реєстрація (на прикладі вже існуючих ключів)
        'search_placeholder'    => 'Пошук за назвою...',
        'cart'                  => 'Кошик',
        'cart_items'            => 'Ваш кошик',
        'cart_empty'            => 'Кошик порожній',
        'cart_total'            => 'Разом',
        'clear_cart'            => 'Очистити',
        'login'                 => 'Авторизуватися',
        'logout'                => 'Вийти',
        'profile'               => 'Профіль',
        'login_header'          => 'Авторизація',
        'login_error'           => 'Невірний логін або пароль',
        'email'                 => 'E-mail',
        'password'              => 'Пароль',
        'login_button'          => 'Увійти',
        'no_account'            => 'Немає акаунта?',
        'register_hint'         => 'Зареєструватися',
        'register_header'       => 'Реєстрація',
        'register_error'        => 'Помилка реєстрації. Перевірте дані.',
        'register_button'       => 'Зареєструватися',

        // Фільтри / бокова панель
        'tab_filters'           => 'Фільтри',
        'tab_about'             => 'Про нас',
        'tab_contacts'          => 'Контакти',
        'filters_heading'       => 'Параметри фільтрації',
        'filter_category_label' => 'Категорія:',
        'filter_size_label'     => 'Розмір:',
        'filter_avail_all'      => 'Усі',
        'filter_avail_yes'      => 'В наявності',
        'filter_avail_no'       => 'Немає',
        'filter_price_label'    => 'Ціна (₴):',
        'price_from_placeholder'=> 'від',
        'price_to_placeholder'  => 'до',

        // About (“Про нас”)
        'about_heading'         => 'Про нас',
        'about_text'            => 'Ласкаво просимо до нашої «Photo Gallery» — це місце, де ви знайдете найрізноманітніші костюми на будь-який смак.
Ми працюємо з найкращими дизайнерами і пропонуємо тільки якісні вироби: історичні, супергеройські, принцесні, косплей та інші.
Наша мета — щоб кожен клієнт залишився задоволений: швидка доставка, гарантія якості та відмінний сервіс.
Адреса шоу-руму: м. Київ, вул. Прикладна, 12.
Графік роботи: Пн–Пт 10:00–19:00, Сб 11:00–17:00.',

        // Contacts (“Контакти”)
        'contacts_heading'      => 'Зв’язатися з адміністратором',
        'contact_phone_option'  => 'Залишити телефон і вказати час',
        'contact_phone_label'   => 'Ваш телефон:',
        'contact_time_label'    => 'Зручний час (години):',
        'contact_message_option'=> 'Залишити повідомлення',
        'contact_message_label' => 'Ваше повідомлення:',
        'contact_send_button'   => 'Надіслати',

        // Деталі костюма (costumeView.php)
        'costume_category'      => 'Категорія:',
        'costume_size'          => 'Розмір:',
        'costume_available_yes' => 'В наявності',
        'costume_available_no'  => 'Немає в наявності',
        'add_to_cart'           => 'Додати до кошика',
        'button_edit'           => 'Редагувати',

        // Відгуки
        'reviews_heading'       => 'Відгуки',
        'your_review_label'     => 'Ваш відгук:',
        'review_placeholder'    => 'Напишіть свій відгук тут...',
        'review_submit_button'  => 'Надіслати',
    ],

    'en' => [
        // header, cart, login/register
        'search_placeholder'    => 'Search by name...',
        'cart'                  => 'Cart',
        'cart_items'            => 'Your cart',
        'cart_empty'            => 'Cart is empty',
        'cart_total'            => 'Total',
        'clear_cart'            => 'Clear cart',
        'login'                 => 'Login',
        'logout'                => 'Logout',
        'profile'               => 'Profile',
        'login_header'          => 'Sign In',
        'login_error'           => 'Invalid login or password',
        'email'                 => 'E-mail',
        'password'              => 'Password',
        'login_button'          => 'Sign In',
        'no_account'            => 'No account?',
        'register_hint'         => 'Register',
        'register_header'       => 'Register',
        'register_error'        => 'Registration error. Check your data.',
        'register_button'       => 'Register',

        // Filters / sidebar
        'tab_filters'           => 'Filters',
        'tab_about'             => 'About Us',
        'tab_contacts'          => 'Contacts',
        'filters_heading'       => 'Filter Options',
        'filter_category_label' => 'Category:',
        'filter_size_label'     => 'Size:',
        'filter_avail_all'      => 'All',
        'filter_avail_yes'      => 'Available',
        'filter_avail_no'       => 'Unavailable',
        'filter_price_label'    => 'Price (₴):',
        'price_from_placeholder'=> 'from',
        'price_to_placeholder'  => 'to',

        // About (“About Us”)
        'about_heading'         => 'About Us',
        'about_text'            => 'Welcome to our “Photo Gallery” — the place where you will find a wide variety of costumes for every taste.
We work with top designers and offer only high-quality products: historical, superhero, princess, cosplay, etc.
Our goal is to ensure every customer is satisfied: fast delivery, quality guarantee, and excellent service.
Showroom address: Kyiv, Prykladna St., 12.
Hours: Mon–Fri 10:00–19:00, Sat 11:00–17:00.',

        // Contacts
        'contacts_heading'      => 'Contact the Administrator',
        'contact_phone_option'  => 'Leave phone and convenient time',
        'contact_phone_label'   => 'Your phone:',
        'contact_time_label'    => 'Convenient time (hours):',
        'contact_message_option'=> 'Leave a message',
        'contact_message_label' => 'Your message:',
        'contact_send_button'   => 'Send',

        // Costume details (costumeView.php)
        'costume_category'      => 'Category:',
        'costume_size'          => 'Size:',
        'costume_available_yes' => 'In Stock',
        'costume_available_no'  => 'Out of Stock',
        'add_to_cart'           => 'Add to Cart',
        'button_edit'           => 'Edit',

        // Reviews
        'reviews_heading'       => 'Reviews',
        'your_review_label'     => 'Your review:',
        'review_placeholder'    => 'Write your review here...',
        'review_submit_button'  => 'Submit',
    ],

 // ===== РУССКИЙ =====
    'ru' => [
        // header / корзина / логин
        'search_placeholder'    => 'Поиск по названию...',
        'cart'                  => 'Корзина',
        'cart_items'            => 'Ваша корзина',
        'cart_empty'            => 'Корзина пуста',
        'cart_total'            => 'Итого',
        'clear_cart'            => 'Очистить',
        'login'                 => 'Войти',
        'logout'                => 'Выйти',
        'profile'               => 'Профиль',
        'login_header'          => 'Авторизация',
        'login_error'           => 'Неверный логин или пароль',
        'email'                 => 'E-mail',
        'password'              => 'Пароль',
        'login_button'          => 'Войти',
        'no_account'            => 'Нет аккаунта?',
        'register_hint'         => 'Зарегистрироваться',
        'register_header'       => 'Регистрация',
        'register_error'        => 'Ошибка регистрации. Проверьте данные.',
        'register_button'       => 'Зарегистрироваться',

        // Sidebar / фильтры
        'tab_filters'           => 'Фильтры',
        'tab_about'             => 'О нас',
        'tab_contacts'          => 'Контакты',
        'filters_heading'       => 'Параметры фильтрации',
        'filter_category_label' => 'Категория:',
        'filter_size_label'     => 'Размер:',
        'filter_avail_all'      => 'Все',
        'filter_avail_yes'      => 'В наличии',
        'filter_avail_no'       => 'Нет в наличии',
        'filter_price_label'    => 'Цена (₴):',
        'price_from_placeholder'=> 'от',
        'price_to_placeholder'  => 'до',

        // О нас
        'about_heading'         => 'О нас',
        'about_text'            => 'Добро пожаловать в нашу «Photo Gallery» — это место, где вы найдете самые разнообразные костюмы на любой вкус.
Мы работаем с лучшими дизайнерами и предлагаем только качественные изделия: исторические, супергеройские, принцессы, косплей и другие.
Наша цель — чтобы каждый клиент оставался доволен: быстрая доставка, гарантия качества и отличный сервис.
Адрес шоурума: г. Киев, ул. Прикладная, 12.
Часы работы: Пн–Пт 10:00–19:00, Сб 11:00–17:00.',

        // Контакты
        'contacts_heading'      => 'Связаться с администратором',
        'contact_phone_option'  => 'Оставить телефон и указать время',
        'contact_phone_label'   => 'Ваш телефон:',
        'contact_time_label'    => 'Удобное время (часы):',
        'contact_message_option'=> 'Оставить сообщение',
        'contact_message_label' => 'Ваше сообщение:',
        'contact_send_button'   => 'Отправить',

        // Детали костюма
        'costume_category'      => 'Категория:',
        'costume_size'          => 'Размер:',
        'costume_available_yes' => 'В наличии',
        'costume_available_no'  => 'Нет в наличии',
        'add_to_cart'           => 'Добавить в корзину',
        'button_edit'           => 'Редактировать',

        // Отзывы
        'reviews_heading'       => 'Отзывы',
        'your_review_label'     => 'Ваш отзыв:',
        'review_placeholder'    => 'Напишите свой отзыв здесь...',
        'review_submit_button'  => 'Отправить',
    ],


    // ===== DEUTSCH =====
    'de' => [
        // header / Warenkorb / Anmeldung
        'search_placeholder'    => 'Suche nach Namen...',
        'cart'                  => 'Warenkorb',
        'cart_items'            => 'Ihr Warenkorb',
        'cart_empty'            => 'Warenkorb ist leer',
        'cart_total'            => 'Summe',
        'clear_cart'            => 'Leeren',
        'login'                 => 'Anmelden',
        'logout'                => 'Abmelden',
        'profile'               => 'Profil',
        'login_header'          => 'Anmeldung',
        'login_error'           => 'Ungültiger Login oder Passwort',
        'email'                 => 'E-Mail',
        'password'              => 'Passwort',
        'login_button'          => 'Einloggen',
        'no_account'            => 'Kein Konto?',
        'register_hint'         => 'Registrieren',
        'register_header'       => 'Registrierung',
        'register_error'        => 'Registrierungsfehler. Prüfen Sie die Daten.',
        'register_button'       => 'Registrieren',

        // Sidebar / Filter
        'tab_filters'           => 'Filter',
        'tab_about'             => 'Über uns',
        'tab_contacts'          => 'Kontakte',
        'filters_heading'       => 'Filteroptionen',
        'filter_category_label' => 'Kategorie:',
        'filter_size_label'     => 'Größe:',
        'filter_avail_all'      => 'Alle',
        'filter_avail_yes'      => 'Verfügbar',
        'filter_avail_no'       => 'Nicht verfügbar',
        'filter_price_label'    => 'Preis (₴):',
        'price_from_placeholder'=> 'von',
        'price_to_placeholder'  => 'bis',

        // Über uns
        'about_heading'         => 'Über uns',
        'about_text'            => 'Willkommen in unserer „Photo Gallery“ – dem Ort, an dem Sie eine große Auswahl an Kostümen für jeden Geschmack finden.
Wir arbeiten mit den besten Designern zusammen und bieten nur hochwertige Produkte: historische, Superhelden-, Prinzessinnen-, Cosplay-Kostüme und mehr.
Unser Ziel ist, dass jeder Kunde zufrieden ist: schnelle Lieferung, Qualitätsgarantie und exzellenter Service.
Adresse des Showrooms: Kiew, Prykladna Straße 12.
Öffnungszeiten: Mo–Fr 10:00–19:00, Sa 11:00–17:00.',

        // Kontakte
        'contacts_heading'      => 'Kontaktieren Sie den Administrator',
        'contact_phone_option'  => 'Telefonnummer hinterlassen und Zeit angeben',
        'contact_phone_label'   => 'Ihre Telefonnummer:',
        'contact_time_label'    => 'Bequeme Zeit (Stunden):',
        'contact_message_option'=> 'Nachricht hinterlassen',
        'contact_message_label' => 'Ihre Nachricht:',
        'contact_send_button'   => 'Senden',

        // Kostümdetails
        'costume_category'      => 'Kategorie:',
        'costume_size'          => 'Größe:',
        'costume_available_yes' => 'Verfügbar',
        'costume_available_no'  => 'Nicht verfügbar',
        'add_to_cart'           => 'In den Warenkorb',
        'button_edit'           => 'Bearbeiten',

        // Bewertungen
        'reviews_heading'       => 'Bewertungen',
        'your_review_label'     => 'Ihre Bewertung:',
        'review_placeholder'    => 'Schreiben Sie hier Ihre Bewertung...',
        'review_submit_button'  => 'Senden',
    ],
];
function __($key) {
    global $TRANSLATIONS, $lang_code;
    return $TRANSLATIONS[$lang_code][$key] ?? $key;
}
$currentLangName = $available_languages[$lang_code] ?? $lang_code;

// 2) Підключення до SQLite (файл у корені проекту)
try {
    $pdo = new PDO('sqlite:' . __DIR__ . '/database.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("Не вдалося підключитися до БД: " . htmlspecialchars($e->getMessage()));
}

// 3) Змінні для помилок форм
$login_error    = '';
$register_error = '';

// 4) Обробка POST-запитів:
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentUrl = $_SERVER['REQUEST_URI'];

    // 4.1 Logout
    if (isset($_POST['logout_submit'])) {
        session_unset();
        session_destroy();
        header("Location: " . $currentUrl);
        exit;
    }

    // 4.2 Remove from Cart
    if (isset($_POST['remove_from_cart'])) {
        $removeId = intval($_POST['remove_from_cart']);
        if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $key => $pid) {
                if (intval($pid) === $removeId) {
                    unset($_SESSION['cart'][$key]);
                    $_SESSION['cart'] = array_values($_SESSION['cart']);
                    break;
                }
            }
        }
        header("Location: " . $currentUrl);
        exit;
    }

    // 4.3 Clear Cart
    if (isset($_POST['clear_cart'])) {
        $_SESSION['cart'] = [];
        header("Location: " . $currentUrl);
        exit;
    }

    // 4.4 Login
    if (isset($_POST['login_submit'])) {
        $email    = trim($_POST['login_email']    ?? '');
        $password = trim($_POST['login_password'] ?? '');

        if ($email === '' || $password === '') {
            $login_error = __('login_error');
        } else {
            $stmt = $pdo->prepare("SELECT id, firstname, lastname, email, password FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_logged_in'] = true;
                $_SESSION['user'] = [
                    'id'        => $user['id'],
                    'firstname' => $user['firstname'],
                    'lastname'  => $user['lastname'],
                    'email'     => $user['email']
                ];
                header("Location: " . $currentUrl);
                exit;
            } else {
                $login_error = __('login_error');
            }
        }
    }

    // 4.5 Register
    if (isset($_POST['register_submit'])) {
        $firstname = trim($_POST['reg_firstname'] ?? '');
        $lastname  = trim($_POST['reg_lastname']  ?? '');
        $email     = trim($_POST['reg_email']     ?? '');
        $password  = trim($_POST['reg_password']  ?? '');
        $confirm   = trim($_POST['confirm_password'] ?? '');

        if ($firstname === '' || $lastname === '' || $email === '' || $password === '' || $confirm === '') {
            $register_error = __('register_error');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $register_error = __('register_error');
        } elseif ($password !== $confirm) {
            $register_error = __('register_error');
        } else {
            // Перевірка, чи існує такий email
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $exists = (bool) $stmt->fetchColumn();

            if ($exists) {
                $register_error = __('register_error');
            } else {
                // Створюємо нового користувача
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $ins = $pdo->prepare("
                    INSERT INTO users (firstname, lastname, email, password)
                    VALUES (:fn, :ln, :email, :pw)
                ");
                $ins->execute([
                    ':fn'    => $firstname,
                    ':ln'    => $lastname,
                    ':email' => $email,
                    ':pw'    => $hash
                ]);
                $newId = $pdo->lastInsertId();
                $_SESSION['user_logged_in'] = true;
                $_SESSION['user'] = [
                    'id'        => $newId,
                    'firstname' => $firstname,
                    'lastname'  => $lastname,
                    'email'     => $email
                ];
                header("Location: " . $currentUrl);
                exit;
            }
        }
    }
}

// 5) Ініціалізація корзини
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
$cartCount  = count($_SESSION['cart']);
$cartItems  = [];
$totalPrice = 0.0;

if ($cartCount > 0) {
    try {
        $sessionIds   = array_map('intval', $_SESSION['cart']);
        $placeholders = implode(',', array_fill(0, count($sessionIds), '?'));
        $itemStmt = $pdo->prepare("SELECT id, name, price, photo FROM costumes WHERE id IN ($placeholders)");
        $itemStmt->execute($sessionIds);
        $cartItems = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

        $existingIds = array_column($cartItems, 'id');
        $_SESSION['cart'] = $existingIds;
        $cartCount = count($existingIds);

        foreach ($cartItems as $ci) {
            $totalPrice += floatval($ci['price']);
        }
    } catch (Exception $e) {
        $_SESSION['cart'] = [];
        $cartItems  = [];
        $totalPrice = 0.0;
        $cartCount  = 0;
    }
}

// 6) Завершуємо буферизацію
ob_end_flush();
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang_code, ENT_QUOTES) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>PHOTO GALLERY</title>

  <!-- Підключаємо CSS -->
  <link rel="stylesheet" href="/lab2_costumes/css/style.css">
  <!-- Font Awesome для іконок -->
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    integrity="sha512-pE4/e+z5nu7b0UTyZ70Q+G9whQAHF+U/rhG3m2vOu4VVuKH4zXl7t3QvGf7Yzs+StxqeOVwEyRv5hR1X+7ycA=="
    crossorigin="anonymous"
    referrerpolicy="no-referrer"
  />
</head>
<body>

<div id="overlay"></div>

<header class="header">
  <!-- ЛОГОТИП -->
  <div class="header__logo">
    <a href="home.view.php?lang=<?= urlencode($lang_code) ?>">
      PHOTO GALLERY
    </a>
  </div>

  <!-- ПРАВИЙ БЛОК: ПОШУК, МОВА, АВТОРИЗАЦІЯ, КОШИК -->
  <div class="header__controls">

    <!-- === ПОШУК === -->
    <div class="header__search">
      <form action="home.view.php" method="get" class="search-form">
        <input
          type="text"
          name="search"
          class="search-input"
          placeholder="<?= __('search_placeholder') ?>"
          value="<?= htmlspecialchars($_GET['search'] ?? '', ENT_QUOTES) ?>"
        >
        <button type="submit" class="search-btn">
          <i class="fas fa-search"></i>
        </button>
        <input type="hidden" name="lang" value="<?= htmlspecialchars($lang_code, ENT_QUOTES) ?>">
        <?php if (!empty($_GET['category'])): ?>
          <input type="hidden" name="category" value="<?= htmlspecialchars($_GET['category'], ENT_QUOTES) ?>">
        <?php endif; ?>
      </form>
    </div>

    <!-- === ВИБІР МОВИ === -->
    <div class="header__lang">
      <button type="button" id="langBtn" class="lang-btn">
        <?= htmlspecialchars($currentLangName, ENT_QUOTES) ?> <i class="fas fa-chevron-down"></i>
      </button>
    </div>

    <!-- === АВТОРИЗАЦІЯ / ПРОФІЛЬ === -->
    <div class="header__auth">
      <?php if (empty($_SESSION['user_logged_in'])): ?>
        <button
          type="button"
          id="loginBtn"
          class="auth-link"
        >
          <i class="fas fa-sign-in-alt"></i> <?= __('login') ?>
        </button>
      <?php else: ?>
        <form method="GET" action="profile.php" style="display:inline;">
          <button type="submit" class="auth-link">
            <i class="fas fa-user"></i> <?= __('profile') ?>
          </button>
        </form>
        <form method="POST" action="" style="display:inline; margin-left:4px;">
          <button type="submit" name="logout_submit" class="auth-link">
            <i class="fas fa-sign-out-alt"></i> <?= __('logout') ?>
          </button>
        </form>
      <?php endif; ?>
    </div>

    <!-- === КОШИК === -->
    <div class="header__cart" id="cartIcon" title="<?= __('cart') ?>">
      <button type="button" class="cart-btn">
        <?= __('cart') ?> (<?= intval($cartCount) ?>) <i class="fas fa-shopping-cart"></i>
      </button>
      <div class="cart-dropdown" id="cartDropdown">
        <div class="cart-dropdown__header">
          <?= __('cart_items') ?> (<?= intval($cartCount) ?>)
        </div>
        <div class="cart-dropdown__items">
          <?php if ($cartCount === 0): ?>
            <div
              class="cart-dropdown__item"
              style="justify-content:center; color:#555; padding: 12px;"
            >
              <?= __('cart_empty') ?>
            </div>
          <?php else: ?>
            <?php foreach ($cartItems as $item): ?>
              <div class="cart-dropdown__item" style="display:flex; align-items:center; padding:8px 12px;">
                <img 
                  src="<?= htmlspecialchars($item['photo'] ?: 'uploads/default.jpg', ENT_QUOTES) ?>" 
                  alt="<?= htmlspecialchars($item['name'], ENT_QUOTES) ?>"
                  style="width:40px; height:40px; object-fit:cover; border-radius:4px; margin-right:10px;"
                >
                <div class="cart-dropdown__item-name" style="flex:1; font-size:0.9rem; color:#333;">
                  <?= htmlspecialchars($item['name'], ENT_QUOTES) ?>
                </div>
                <div class="cart-dropdown__item-price" style="font-size:0.9rem; color:#007bff; margin-right:10px;">
                  <?= number_format($item['price'], 2, ',', ' ') ?> ₴
                </div>
                <form method="POST" action="" class="cart-dropdown__remove-form">
                  <input type="hidden" name="remove_from_cart" value="<?= intval($item['id']) ?>">
                  <button
                    type="submit"
                    class="cart-dropdown__item-remove"
                    style="background:none; border:none; font-size:1.2rem; color:#888; cursor:pointer;"
                    title="<?= __('clear_cart') ?>"
                  >&times;</button>
                </form>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <?php if ($cartCount > 0): ?>
          <div class="cart-dropdown__footer" style="padding: 8px 12px; border-top:1px solid #ddd;">
            <div class="cart-dropdown__footer-total" style="font-size:0.95rem; color:#333; margin-bottom:8px;">
              <?= __('cart_total') ?>: <?= number_format($totalPrice, 2, ',', ' ') ?> ₴
            </div>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
              <!-- Кнопка Очистити -->
              <form method="POST" action="">
                <input type="hidden" name="clear_cart" value="1">
                <button 
                  type="submit" 
                  class="cart-dropdown__footer-clear"
                  style="background-color:#dc3545; color:#fff; border:none; padding:6px 12px; border-radius:4px; cursor:pointer; font-size:0.9rem;"
                >
                  <?= __('clear_cart') ?>
                </button>
              </form>

              <!-- Нова кнопка Оплатити -->
              <form method="GET" action="/lab2_costumes/views/payment.php">
                <!-- Передаємо загальну суму -->
                <input type="hidden" name="total" value="<?= htmlspecialchars($totalPrice) ?>">
                <button 
                  type="submit" 
                  class="cart-dropdown__footer-pay"
                  style="background-color:#28a745; color:#fff; border:none; padding:6px 12px; border-radius:4px; cursor:pointer; font-size:0.9rem;"
                >
                  Оплатити
                </button>
              </form>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>

  </div>
</header>

<!-- =========================================== -->
<!-- 7) МОДАЛЬНЕ ВІКНО ВИБОРУ МОВИ -->
<!-- =========================================== -->
<div class="modal" id="langModal">
  <div class="modal__content">
    <button class="modal__close" id="langClose">&times;</button>
    <h2>Виберіть мову</h2>
    <form method="GET" action="">
      <div class="modal-field">
        <label for="langSelect">Мова:</label>
        <select id="langSelect" name="lang">
          <?php foreach ($available_languages as $code => $label): ?>
            <option
              value="<?= htmlspecialchars($code, ENT_QUOTES) ?>"
              <?= ($code === $lang_code) ? 'selected' : '' ?>
            >
              <?= htmlspecialchars($label, ENT_QUOTES) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="modal-btn-primary">Підтвердити</button>
    </form>
  </div>
</div>

<!-- =========================================== -->
<!-- 8) МОДАЛЬНЕ ВІКНО ЛОГІНА / РЕЄСТРАЦІЇ -->
<!-- =========================================== -->
<div class="modal" id="authModal" style="display:none;">
  <div class="modal__content auth-modal-content">
    <button class="modal__close" id="authClose">&times;</button>

    <!-- ===== БЛОК ЛОГІНА ===== -->
    <div id="loginFormContainer">
      <h2><?= __('login_header') ?></h2>
      <?php if (!empty($login_error)): ?>
        <div class="login-error" style="color:#dc3545; margin-bottom:10px;">
          <?= htmlspecialchars($login_error, ENT_QUOTES) ?>
        </div>
      <?php endif; ?>
      <form method="POST" action="">
        <div class="login-input" style="margin-bottom:10px;">
          <label for="login_email"><?= __('email') ?>:</label><br>
          <input 
            type="email" 
            id="login_email" 
            name="login_email" 
            required 
            style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;"
          >
        </div>
        <div class="login-input" style="margin-bottom:10px;">
          <label for="login_password"><?= __('password') ?>:</label><br>
          <input 
            type="password" 
            id="login_password" 
            name="login_password" 
            required 
            style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;"
          >
        </div>
        <button 
          type="submit" 
          name="login_submit" 
          class="button-login"
          style="background-color:#007bff; color:#fff; border:none; padding:10px 16px; border-radius:4px; cursor:pointer; font-size:1rem;"
        >
          <i class="fas fa-sign-in-alt"></i> <?= __('login_button') ?>
        </button>
      </form>
      <div class="auth-switch" style="margin-top:12px; font-size:0.9rem; color:#555;">
        <?= __('no_account') ?> <a href="#" id="showRegister"><?= __('register_hint') ?></a>
      </div>
    </div>

    <!-- ===== БЛОК РЕЄСТРАЦІЇ ===== -->
    <div id="registerFormContainer" style="display: none;">
      <h2><?= __('register_header') ?></h2>
      <?php if (!empty($register_error)): ?>
        <div class="register-error" style="color:#dc3545; margin-bottom:10px;">
          <?= htmlspecialchars($register_error, ENT_QUOTES) ?>
        </div>
      <?php endif; ?>
      <form method="POST" action="">
        <div class="register-input" style="margin-bottom:10px;">
          <label for="reg_firstname">Прізвище:</label><br>
          <input 
            type="text" 
            id="reg_firstname" 
            name="reg_firstname" 
            required 
            style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;"
          >
        </div>
        <div class="register-input" style="margin-bottom:10px;">
          <label for="reg_lastname">Ім’я:</label><br>
          <input 
            type="text" 
            id="reg_lastname" 
            name="reg_lastname" 
            required 
            style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;"
          >
        </div>
        <div class="register-input" style="margin-bottom:10px;">
          <label for="reg_email"><?= __('email') ?>:</label><br>
          <input 
            type="email" 
            id="reg_email" 
            name="reg_email" 
            required 
            style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;"
          >
        </div>
        <div class="register-input" style="margin-bottom:10px;">
          <label for="reg_password"><?= __('password') ?>:</label><br>
          <input 
            type="password" 
            id="reg_password" 
            name="reg_password" 
            required 
            style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;"
          >
        </div>
        <div class="register-input" style="margin-bottom:10px;">
          <label for="confirm_password">Підтвердьте пароль:</label><br>
          <input 
            type="password" 
            id="confirm_password" 
            name="confirm_password" 
            required 
            style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;"
          >
        </div>
        <button 
          type="submit" 
          name="register_submit" 
          class="button-register"
          style="background-color:#28a745; color:#fff; border:none; padding:10px 16px; border-radius:4px; cursor:pointer; font-size:1rem;"
        >
          <i class="fas fa-user-plus"></i> <?= __('register_button') ?>
        </button>
      </form>
      <div class="auth-switch" style="margin-top:12px; font-size:0.9rem; color:#555;">
        <?= __('login') ?>? <a href="#" id="showLogin"><?= __('login') ?></a>
      </div>
    </div>

  </div>
</div>

<!-- =========================================== -->
<!-- 9) JS ДЛЯ КЕРУВАННЯ МОДАЛЬНИМИ ВІКНАМИ ТА КОШИКОМ -->
<!-- =========================================== -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Модалка вибору мови
  const langModal = document.getElementById('langModal');
  const langBtn   = document.getElementById('langBtn');
  const langClose = document.getElementById('langClose');

  langBtn.addEventListener('click', () => {
    langModal.classList.add('open');
  });
  langClose.addEventListener('click', () => {
    langModal.classList.remove('open');
  });
  langModal.addEventListener('click', function(e) {
    if (e.target === langModal) langModal.classList.remove('open');
  });

  // Модалка логіна/реєстрації
  const authModal             = document.getElementById('authModal');
  const authClose             = document.getElementById('authClose');
  const loginBtn              = document.getElementById('loginBtn');
  const loginFormContainer    = document.getElementById('loginFormContainer');
  const registerFormContainer = document.getElementById('registerFormContainer');
  const showRegisterLink      = document.getElementById('showRegister');
  const showLoginLink         = document.getElementById('showLogin');

  if (loginBtn) {
    loginBtn.addEventListener('click', () => {
      loginFormContainer.style.display = 'block';
      registerFormContainer.style.display = 'none';
      authModal.style.display = 'flex';
    });
  }
  authClose.addEventListener('click', () => authModal.style.display = 'none');
  authModal.addEventListener('click', e => {
    if (e.target === authModal) authModal.style.display = 'none';
  });

  if (showRegisterLink) {
    showRegisterLink.addEventListener('click', function(e) {
      e.preventDefault();
      loginFormContainer.style.display = 'none';
      registerFormContainer.style.display = 'block';
    });
  }
  if (showLoginLink) {
    showLoginLink.addEventListener('click', function(e) {
      e.preventDefault();
      loginFormContainer.style.display = 'block';
      registerFormContainer.style.display = 'none';
    });
  }

  // Випадаючий кошик
  const cartIcon     = document.getElementById('cartIcon');
  const cartDropdown = document.getElementById('cartDropdown');
  const overlay      = document.getElementById('overlay');

  cartIcon.addEventListener('click', function(e) {
    e.stopPropagation();
    if (cartDropdown.classList.contains('open')) {
      cartDropdown.classList.remove('open');
      overlay.style.display = 'none';
    } else {
      cartDropdown.classList.add('open');
      overlay.style.display = 'block';
    }
  });

  document.addEventListener('click', function(e) {
    if (!cartIcon.contains(e.target) && !cartDropdown.contains(e.target)) {
      cartDropdown.classList.remove('open');
      overlay.style.display = 'none';
    }
  });
  overlay.addEventListener('click', function() {
    cartDropdown.classList.remove('open');
    overlay.style.display = 'none';
  });
});
</script>

</body>
</html>
