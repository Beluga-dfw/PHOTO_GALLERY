<?php
// =====================================
// home.view.php — виправлений файл з мультимовністю
// =====================================

ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1.1 Видалити конкретний товар із кошика
    if (isset($_POST['remove_from_cart'])) {
        $removeId = intval($_POST['remove_from_cart']);
        if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $k => $id) {
                if (intval($id) === $removeId) {
                    unset($_SESSION['cart'][$k]);
                    $_SESSION['cart'] = array_values($_SESSION['cart']);
                    break;
                }
            }
        }
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }

    // 1.2 Очистити увесь кошик
    if (isset($_POST['clear_cart'])) {
        $_SESSION['cart'] = [];
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }

    // 1.3 (За потреби) Логін/Логаут, реєстрація
    // if (isset($_POST['logout_submit'])) { ... }
}

include __DIR__ . '/../header.php';

try {
    $pdo = new PDO('sqlite:' . __DIR__ . '/../database.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die('Не вдалося підключитись до БД: ' . htmlspecialchars($e->getMessage()));
}

$login_error    = $_SESSION['login_error']    ?? '';
$register_error = $_SESSION['register_error'] ?? '';

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
        $itemStmt     = $pdo->prepare(
            "SELECT id, name, price, photo 
             FROM costumes 
             WHERE id IN ($placeholders)"
        );
        $itemStmt->execute($sessionIds);
        $cartItems = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

        $existingIds      = array_column($cartItems, 'id');
        $_SESSION['cart'] = $existingIds;
        $cartCount        = count($existingIds);

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

try {
    $stmt        = $pdo->prepare("SELECT * FROM costumes");
    $stmt->execute();
    $allCostumes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!is_array($allCostumes)) {
        $allCostumes = [];
    }
} catch (Exception $e) {
    $allCostumes = [];
    $dbError     = "Не вдалося завантажити костюми: " . htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang_code, ENT_QUOTES) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= __('tab_filters') ?> — PHOTO GALLERY</title>

  <!-- Підключення основного CSS -->
  <link rel="stylesheet" href="/lab2_costumes/css/style.css">

  <!-- FontAwesome для іконок -->
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    integrity="sha512-pE4/e+z5nu7b0UTyZ70Q+G9whQAHF+U/rhG3m2vOu4VVuKH4zXl7t3QvGf7Yzs+StxqeOVwEyRv5hR1X+7ycA=="
    crossorigin="anonymous"
    referrerpolicy="no-referrer"
  />

  <style>
    /* ================================== */
    /* 1) Кнопка фільтрів у лівому куті   */
    /* ================================== */
    .filters-button {
      margin: 20px;
      text-align: left;
    }
    .filter-toggle-btn {
      background: none;
      border: none;
      font-size: 1.2rem;
      cursor: pointer;
      color: #333;
      padding: 8px 12px;
      border: 1px solid #ccc;
      border-radius: 4px;
      transition: background-color 0.2s, border-color 0.2s;
    }
    .filter-toggle-btn:hover {
      background-color: #f0f0f0;
      border-color: #999;
    }

    /* ================================== */
    /* 2) СТИЛІ ДЛЯ БОКОВОЇ ПАНЕЛІ (ЗЛІВА) */
    /* ================================== */
    #filterSidebar {
      position: fixed;
      top: 0;
      left: -350px;
      width: 350px;
      height: 100%;
      background-color: #ffffff;
      box-shadow: 2px 0 8px rgba(0,0,0,0.15);
      transition: left 0.3s ease;
      z-index: 2500;
      padding: 24px 16px 16px 16px;
      overflow-y: auto;
    }
    #filterSidebar.open {
      left: 0;
    }
    body.sidebar-open .main-content {
      margin-left: 350px;
      transition: margin-left 0.3s ease;
    }
    #filterCloseBtn {
      position: absolute;
      top: 12px;
      right: 12px;
      background: none;
      border: none;
      font-size: 1.2rem;
      color: #999;
      cursor: pointer;
      transition: color 0.2s;
    }
    #filterCloseBtn:hover {
      color: #333;
    }
    .sidebar-tabs {
      display: flex;
      justify-content: space-between;
      margin-bottom: 24px;
      border-bottom: 1px solid #ddd;
    }
    .sidebar-tabs button {
      background: none;
      border: none;
      font-size: 0.95rem;
      font-weight: 600;
      color: #555;
      padding: 8px 12px;
      cursor: pointer;
      transition: color 0.2s, border-bottom 0.2s;
      border-bottom: 2px solid transparent;
    }
    .sidebar-tabs button.active {
      color: #000;
      border-bottom: 2px solid #000;
    }
    .sidebar-section {
      display: none;
    }
    .sidebar-section.active {
      display: block;
    }
    .sidebar-section h3 {
      font-size: 1.4rem;
      margin-bottom: 16px;
      color: #222;
    }
    .sidebar-section label {
      display: block;
      font-size: 0.95rem;
      color: #555;
      margin-bottom: 6px;
    }
    .sidebar-section select,
    .sidebar-section input[type="number"],
    .sidebar-section input[type="text"],
    .sidebar-section textarea {
      width: 100%;
      padding: 10px 12px;
      font-size: 0.95rem;
      border: 1px solid #ccc;
      border-radius: 4px;
      background-color: #fafafa;
      outline: none;
      transition: border-color 0.2s;
      box-sizing: border-box;
      margin-bottom: 16px;
    }
    .sidebar-section select:focus,
    .sidebar-section input:focus,
    .sidebar-section textarea:focus {
      border-color: #007bff;
    }
    .availability-group {
      display: flex;
      flex-direction: column;
      gap: 6px;
      margin-bottom: 16px;
    }
    .availability-group label {
      font-size: 0.95rem;
      color: #555;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .sidebar-section button.send-btn {
      display: inline-block;
      background-color: #000;
      color: #fff;
      border: none;
      padding: 10px 16px;
      font-size: 1rem;
      border-radius: 4px;
      cursor: pointer;
      transition: background-color 0.2s;
      margin-top: 8px;
    }
    .sidebar-section button.send-btn:hover {
      background-color: #333;
    }

    /* ================================== */
    /* 3) СТИЛІ ДЛЯ КАРТОК КОСТЮМІВ       */
    /* ================================== */
    .products-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 20px;
      max-width: 1200px;
      margin: 0 auto 40px;
      padding: 0 10px;
    }
    .product-card {
      position: relative;
      background-color: #ffffff;
      border-radius: 4px;
      overflow: hidden;
      box-shadow: 0 1px 4px rgba(0,0,0,0.08);
      transition: transform 0.2s, box-shadow 0.2s;
      display: flex;
      flex-direction: column;
    }
    .product-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 2px 8px rgba(0,0,0,0.10);
    }
    .product-card__img-wrapper {
      width: 100%;
      height: 300px;
      background-color: #f0f0f0;
      overflow: hidden;
      border-top-left-radius: 4px;
      border-top-right-radius: 4px;
      flex-shrink: 0;
    }
    .product-card__img-wrapper img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }
    .product-card__info {
      padding: 12px;
      flex-grow: 1;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }
    .product-card__name {
      font-size: 1.1rem;
      font-weight: 600;
      margin-bottom: 6px;
      color: #222;
    }
    .product-card__price {
      font-size: 1rem;
      color: #777 !important;
      font-weight: 600;
    }
    .product-card__badge {
      position: absolute;
      top: 10px;
      left: 10px;
      background-color: #dc3545;
      color: #fff;
      font-size: 0.8rem;
      font-weight: 600;
      padding: 4px 6px;
      border-radius: 4px;
    }

    /* Адаптивність: на менших екранах – по 2 картки в ряд */
    @media (max-width: 992px) {
      .products-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }
    /* Ще менше – по 1 картці */
    @media (max-width: 600px) {
      .products-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>

  <main class="main-content">
    <?php if (!empty($dbError)): ?>
      <p class="error" style="color:red; text-align:center;"><?= $dbError ?></p>
    <?php endif; ?>

    <?php if (!empty($login_error)): ?>
      <p class="error"><?= htmlspecialchars($login_error, ENT_QUOTES) ?></p>
    <?php endif; ?>
    <?php if (!empty($register_error)): ?>
      <p class="error"><?= htmlspecialchars($register_error, ENT_QUOTES) ?></p>
    <?php endif; ?>

    <!-- 1) Кнопка “— — —” у лівому куті -->
    <section class="filters-button">
      <button type="button" id="openFilterBtn" class="filter-toggle-btn">
        &mdash; &mdash; &mdash;
      </button>
    </section>

    <!-- 2) Сітка карток костюмів або повідомлення "Нічого не знайдено." -->
    <div class="products-grid" id="productsGrid">
      <?php if (empty($allCostumes)): ?>
        <p style="text-align:center; color:#555; width:100%; margin-top:40px; font-size:1.2rem;">
          <?= __('cart_empty') // можна замінити на відповідний ключ, якщо потрібно ?> 
        </p>
      <?php else: ?>
        <?php foreach ($allCostumes as $costume): ?>
          <?php
            $id          = intval($costume['id'] ?? 0);
            $name        = htmlspecialchars($costume['name'] ?? '—', ENT_QUOTES);
            $price       = floatval($costume['price'] ?? 0);
            $photoPath   = htmlspecialchars($costume['photo'] ?? 'uploads/default.jpg', ENT_QUOTES);
            $hasDiscount = isset($costume['discount']) && floatval($costume['discount']) > 0;
            $discountVal = intval($costume['discount'] ?? 0);
            $cAvailable  = isset($costume['availability']) ? intval($costume['availability']) : 0;
            $cCategory   = htmlspecialchars($costume['category'] ?? '', ENT_QUOTES);
            $cSize       = htmlspecialchars($costume['size'] ?? '', ENT_QUOTES);
          ?>
          <div
            class="product-card"
            data-category="<?= $cCategory ?>"
            data-size="<?= $cSize ?>"
            data-price="<?= $price ?>"
            data-available="<?= $cAvailable ?>"
          >
            <a href="costumeView.php?id=<?= $id ?>&lang=<?= htmlspecialchars($_GET['lang'] ?? '', ENT_QUOTES) ?>">
              <div class="product-card__img-wrapper">
                <img
                  src="../<?= $photoPath ?>"
                  alt="<?= $name ?>"
                  class="product-card__img"
                >
              </div>
            </a>

            <?php if ($hasDiscount): ?>
              <div class="product-card__badge">-<?= $discountVal ?>%</div>
            <?php endif; ?>

            <div class="product-card__info">
              <h3 class="product-card__name"><?= $name ?></h3>
              <div class="product-card__price">
                <?= number_format($price, 0, '.', ' ') ?> грн
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </main>

  <!-- 3) Бокова панель із вкладками та фільтрами -->
  <div id="filterSidebar">
    <button id="filterCloseBtn">&times;</button>

    <div class="sidebar-tabs">
      <button id="tabFilter" class="active"><?= __('tab_filters') ?></button>
      <button id="tabAbout"><?= __('tab_about') ?></button>
      <button id="tabContacts"><?= __('tab_contacts') ?></button>
    </div>

    <!-- Фільтри -->
    <div id="sectionFilter" class="sidebar-section active">
      <h3><?= __('filters_heading') ?></h3>
      <div class="modal-field">
        <label for="filterCategory"><?= __('filter_category_label') ?></label>
        <select id="filterCategory" name="filter_category">
          <option value=""><?= __('filter_category_label') ?> <?= __('tab_filters') ?></option>
          <?php
            $allCategories = ['superhero', 'princess', 'historical', 'cosplay'];
            foreach ($allCategories as $cat):
              $catEsc = htmlspecialchars($cat, ENT_QUOTES);
          ?>
            <option value="<?= $catEsc ?>"><?= ucfirst($catEsc) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="modal-field">
        <label for="filterSize"><?= __('filter_size_label') ?></label>
        <select id="filterSize" name="filter_size">
          <option value=""><?= __('filter_size_label') ?> <?= __('tab_filters') ?></option>
          <?php
            $allSizes = ['S', 'M', 'L', 'XL', 'XXL'];
            foreach ($allSizes as $sz):
              $szEsc = htmlspecialchars($sz, ENT_QUOTES);
          ?>
            <option value="<?= $szEsc ?>"><?= $szEsc ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="availability-group">
        <label><?= __('filter_avail_all') ?>:</label>
        <label>
          <input type="radio" name="filter_available" value="" checked> <?= __('filter_avail_all') ?>
        </label>
        <label>
          <input type="radio" name="filter_available" value="1"> <?= __('filter_avail_yes') ?>
        </label>
        <label>
          <input type="radio" name="filter_available" value="0"> <?= __('filter_avail_no') ?>
        </label>
      </div>
      <div class="modal-field">
        <label><?= __('filter_price_label') ?></label>
        <div style="display: flex; gap: 8px;">
          <input
            type="number"
            id="priceMin"
            name="price_min"
            placeholder="<?= __('price_from_placeholder') ?>"
            min="0"
          >
          <input
            type="number"
            id="priceMax"
            name="price_max"
            placeholder="<?= __('price_to_placeholder') ?>"
            min="0"
          >
        </div>
      </div>
    </div>

    <!-- Про нас -->
    <div id="sectionAbout" class="sidebar-section">
      <h3><?= __('about_heading') ?></h3>
      <p style="font-size: 0.95rem; color: #444; line-height: 1.4;">
        <?= nl2br(htmlspecialchars(__('about_text'), ENT_QUOTES)) ?>
      </p>
    </div>

    <!-- Контакти -->
    <div id="sectionContacts" class="sidebar-section">
      <h3><?= __('contacts_heading') ?></h3>
      <div class="modal-field">
        <label>
          <input type="radio" name="contact_option" value="phone" checked>
          <?= __('contact_phone_option') ?>
        </label>
      </div>
      <div id="contactPhoneBlock" class="contact-block">
        <label for="contactPhone"><?= __('contact_phone_label') ?></label>
        <input type="text" id="contactPhone" placeholder="+380XXXXXXXXX">
        <label for="contactTime"><?= __('contact_time_label') ?></label>
        <input type="text" id="contactTime" placeholder="<?= __('contact_time_label') ?>">
      </div>
      <div class="modal-field">
        <label>
          <input type="radio" name="contact_option" value="message">
          <?= __('contact_message_option') ?>
        </label>
      </div>
      <div id="contactMessageBlock" class="contact-block" style="display: none;">
        <label for="contactMessage"><?= __('contact_message_label') ?></label>
        <textarea id="contactMessage" rows="4" placeholder="<?= __('contact_message_label') ?>"></textarea>
      </div>
      <button type="button" class="send-btn" id="sendContactBtn"><?= __('contact_send_button') ?></button>
      <p id="contactSuccessMsg" style="display:none; margin-top:12px; color:green;">
        <?= __('contact_send_button') ?>…
      </p>
    </div>
  </div>

  <!-- 4) Підвал -->
  <footer style="background-color:#fff; border-top:1px solid #e0e0e0; padding:20px; text-align:center;">
    © 2025 Photo Gallery. Усі права захищені.
  </footer>

  <!-- 5) JS: відкриття/закриття бокової панелі, вкладки, фільтрація, контакти -->
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    // ————— Відкриття/закриття бокової панелі —————
    const sidebar  = document.getElementById('filterSidebar');
    const openBtn  = document.getElementById('openFilterBtn');
    const closeBtn = document.getElementById('filterCloseBtn');
    const body     = document.body;

    openBtn.addEventListener('click', function() {
      sidebar.classList.add('open');
      body.classList.add('sidebar-open');
      activateTab('filter');
    });
    closeBtn.addEventListener('click', function() {
      sidebar.classList.remove('open');
      body.classList.remove('sidebar-open');
    });
    sidebar.addEventListener('click', function(e) {
      if (e.target === sidebar) {
        sidebar.classList.remove('open');
        body.classList.remove('sidebar-open');
      }
    });

    // ————— Перемикання вкладок —————
    const tabFilter   = document.getElementById('tabFilter');
    const tabAbout    = document.getElementById('tabAbout');
    const tabContacts = document.getElementById('tabContacts');

    tabFilter.addEventListener('click', () => activateTab('filter'));
    tabAbout.addEventListener('click', () => activateTab('about'));
    tabContacts.addEventListener('click', () => activateTab('contacts'));

    function activateTab(name) {
      [tabFilter, tabAbout, tabContacts].forEach(btn => btn.classList.remove('active'));
      document.querySelectorAll('.sidebar-section').forEach(sec => sec.classList.remove('active'));

      if (name === 'filter') {
        tabFilter.classList.add('active');
        document.getElementById('sectionFilter').classList.add('active');
      }
      if (name === 'about') {
        tabAbout.classList.add('active');
        document.getElementById('sectionAbout').classList.add('active');
      }
      if (name === 'contacts') {
        tabContacts.classList.add('active');
        document.getElementById('sectionContacts').classList.add('active');
      }
    }

    // ————— Фільтрація карток —————
    const productCards = document.querySelectorAll('.product-card');
    const selCategory  = document.getElementById('filterCategory');
    const selSize      = document.getElementById('filterSize');
    const radiosAvail  = document.getElementsByName('filter_available');
    const inputPriceMin = document.getElementById('priceMin');
    const inputPriceMax = document.getElementById('priceMax');

    selCategory.addEventListener('change', updateFilter);
    selSize    .addEventListener('change', updateFilter);
    radiosAvail.forEach(r => r.addEventListener('change', updateFilter));
    inputPriceMin.addEventListener('input', updateFilter);
    inputPriceMax.addEventListener('input', updateFilter);

    function updateFilter() {
      const catValue     = selCategory.value;
      const sizeValue    = selSize.value;
      let availValue     = '';
      radiosAvail.forEach(r => { if (r.checked) availValue = r.value; });
      const priceMinVal  = parseFloat(inputPriceMin.value) || NaN;
      const priceMaxVal  = parseFloat(inputPriceMax.value) || NaN;

      productCards.forEach(card => {
        const cardCat    = card.getAttribute('data-category');
        const cardSize   = card.getAttribute('data-size');
        const cardPrice  = parseFloat(card.getAttribute('data-price'));
        const cardAvail  = card.getAttribute('data-available');

        let visible = true;

        if (catValue !== '' && cardCat !== catValue) visible = false;
        if (visible && sizeValue !== '' && cardSize !== sizeValue) visible = false;
        if (visible && availValue !== '') {
          if (availValue === '1' && cardAvail !== '1') visible = false;
          if (availValue === '0' && cardAvail !== '0') visible = false;
        }
        if (visible && !isNaN(priceMinVal) && cardPrice < priceMinVal) visible = false;
        if (visible && !isNaN(priceMaxVal) && cardPrice > priceMaxVal) visible = false;

        card.style.display = visible ? 'flex' : 'none';
      });
    }

    updateFilter();

    // ————— Логіка вкладки “Контакти” —————
    const radioPhone   = document.querySelector('input[name="contact_option"][value="phone"]');
    const radioMessage = document.querySelector('input[name="contact_option"][value="message"]');
    const blockPhone   = document.getElementById('contactPhoneBlock');
    const blockMessage = document.getElementById('contactMessageBlock');
    const sendBtn      = document.getElementById('sendContactBtn');
    const successMsg   = document.getElementById('contactSuccessMsg');

    radioPhone.addEventListener('change', () => {
      blockPhone.style.display = 'block';
      blockMessage.style.display = 'none';
      successMsg.style.display = 'none';
    });
    radioMessage.addEventListener('change', () => {
      blockPhone.style.display = 'none';
      blockMessage.style.display = 'block';
      successMsg.style.display = 'none';
    });

    sendBtn.addEventListener('click', function() {
      successMsg.style.display = 'block';
      document.getElementById('contactPhone').value = '';
      document.getElementById('contactTime').value = '';
      document.getElementById('contactMessage').value = '';
      radioPhone.checked = true;
      blockPhone.style.display = 'block';
      blockMessage.style.display = 'none';
    });
  });
  </script>
</body>
</html>

<?php
ob_end_flush();
?>
