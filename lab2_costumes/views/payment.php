<?php
// ===========================================
// views/payment.php — Сторінка поліпшеного оформлення замовлення
// ===========================================

session_start();
include __DIR__ . '/../header.php';

// Отримуємо загальну суму з GET-параметра
$total = isset($_GET['total']) ? floatval($_GET['total']) : 0.0;

// Якщо кошик порожній, перенаправляємо на головну
if ($total <= 0) {
    header("Location: /lab2_costumes/home.view.php");
    exit;
}

// Приклад: якщо форма підтвердження замовлення надіслана
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_order'])) {
    // У реальному проєкті тут має бути інтеграція з платіжною системою,
    // збереження замовлення в БД, очищення корзини тощо.
    // Для демонстрації просто очищаємо корзину і показуємо "Дякуємо".
    $_SESSION['cart'] = [];
    header("Location: thankyou.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
  <meta charset="UTF-8">
  <title>Оформлення замовлення</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Підключаємо основний CSS -->
  <link rel="stylesheet" href="/lab2_costumes/css/style.css">
  <!-- Додаємо стилі для кроків оформлення -->
  <style>
    .checkout-container {
      max-width: 600px;
      margin: 50px auto 80px;
      background: #fff;
      padding: 30px 20px 40px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      font-family: Arial, sans-serif;
    }
    .step-indicator {
      display: flex;
      justify-content: space-between;
      margin-bottom: 30px;
      position: relative;
    }
    .step-indicator::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 5%;
      right: 5%;
      height: 2px;
      background: #ddd;
      z-index: 1;
    }
    .step {
      z-index: 2;
      width: 33%;
      text-align: center;
      position: relative;
    }
    .step .circle {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      background: #ddd;
      margin: 0 auto;
      line-height: 32px;
      color: #fff;
      font-weight: bold;
      position: relative;
      z-index: 2;
    }
    .step.active .circle,
    .step.completed .circle {
      background: #28a745;
    }
    .step .label {
      margin-top: 8px;
      font-size: 0.9rem;
      color: #555;
    }
    .step.active .label,
    .step.completed .label {
      color: #000;
      font-weight: 600;
    }
    .step.completed::after {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 50%;
      height: 2px;
      background: #28a745;
      transform-origin: left;
      transform: translateY(-50%) translateX(16px) scaleX(1);
      z-index: 1;
    }
    .step:not(.completed):not(.active)::after {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 50%;
      height: 2px;
      background: #ddd;
      transform-origin: left;
      transform: translateY(-50%) translateX(16px) scaleX(1);
      z-index: 1;
    }
    .step:last-child::after {
      display: none;
    }

    .form-step {
      display: none;
    }
    .form-step.active {
      display: block;
    }
    .form-step h3 {
      margin-bottom: 20px;
      font-size: 1.2rem;
      color: #333;
      text-align: center;
    }
    .form-group {
      margin-bottom: 14px;
    }
    .form-group label {
      display: block;
      font-size: 0.95rem;
      color: #333;
      margin-bottom: 6px;
    }
    .form-group input,
    .form-group select,
    .form-group textarea {
      width: 100%;
      padding: 8px 10px;
      font-size: 0.95rem;
      border: 1px solid #ccc;
      border-radius: 4px;
      box-sizing: border-box;
      transition: border-color 0.2s;
    }
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
      border-color: #007bff;
      outline: none;
    }
    .buttons-group {
      display: flex;
      justify-content: space-between;
      margin-top: 30px;
    }
    .buttons-group button {
      padding: 10px 20px;
      font-size: 0.95rem;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      transition: background-color 0.2s;
    }
    .buttons-group .btn-next {
      background-color: #007bff;
      color: #fff;
    }
    .buttons-group .btn-next:hover {
      background-color: #0056b3;
    }
    .buttons-group .btn-prev {
      background-color: #6c757d;
      color: #fff;
    }
    .buttons-group .btn-prev:hover {
      background-color: #5a6268;
    }
    .confirm-button {
      background-color: #28a745;
      color: #fff;
      margin: 0 auto;
      display: block;
      width: 100%;
      font-size: 1rem;
    }
    .confirm-button:hover {
      background-color: #218838;
    }
  </style>
</head>
<body>

  <div class="checkout-container">
    <!-- Індикатор кроків -->
    <div class="step-indicator">
      <div class="step active" data-step="1">
        <div class="circle">1</div>
        <div class="label">Доставка</div>
      </div>
      <div class="step" data-step="2">
        <div class="circle">2</div>
        <div class="label">Оплата</div>
      </div>
      <div class="step" data-step="3">
        <div class="circle">3</div>
        <div class="label">Підтвердження</div>
      </div>
    </div>

    <form id="checkoutForm" method="POST" action="">
      <!-- КРОК 1: Доставка -->
      <div class="form-step active" data-step="1">
        <h3>Вкажіть дані для доставки</h3>
        <div class="form-group">
          <label for="recipientName">Отримувач (ПІБ):</label>
          <input type="text" id="recipientName" name="recipientName" required placeholder="Іван Іваненко">
        </div>
        <div class="form-group">
          <label for="phone">Телефон:</label>
          <input type="text" id="phone" name="phone" required placeholder="+380XXXXXXXXX">
        </div>
        <div class="form-group">
          <label for="city">Місто:</label>
          <input type="text" id="city" name="city" required placeholder="Київ">
        </div>
        <div class="form-group">
          <label for="address">Адреса (вулиця, будинок, квартира):</label>
          <textarea id="address" name="address" rows="2" required placeholder="вул. Прикладна, 12, кв. 34"></textarea>
        </div>
        <div class="buttons-group">
          <div></div>
          <button type="button" class="btn-next" data-next="2">Далі</button>
        </div>
      </div>

      <!-- КРОК 2: Оплата -->
      <div class="form-step" data-step="2">
        <h3>Введіть платіжні дані</h3>
        <div class="form-group">
          <label for="cardName">Ім'я на картці:</label>
          <input type="text" id="cardName" name="cardName" required placeholder="IVAN IVANENKO">
        </div>
        <div class="form-group">
          <label for="cardNumber">Номер картки:</label>
          <input type="text" id="cardNumber" name="cardNumber" required placeholder="0000 0000 0000 0000">
        </div>
        <div class="form-group" style="display:flex; gap:10px;">
          <div style="flex:1;">
            <label for="expDate">Термін дії (ММ/РР):</label>
            <input type="text" id="expDate" name="expDate" required placeholder="08/25">
          </div>
          <div style="flex:1;">
            <label for="cvv">CVV:</label>
            <input type="number" id="cvv" name="cvv" required placeholder="123" min="100" max="999">
          </div>
        </div>
        <div class="buttons-group">
          <button type="button" class="btn-prev" data-prev="1">Назад</button>
          <button type="button" class="btn-next" data-next="3">Далі</button>
        </div>
      </div>

      <!-- КРОК 3: Підтвердження -->
      <div class="form-step" data-step="3">
        <h3>Підтвердження замовлення</h3>
        <div class="payment-summary" style="text-align:center; margin-bottom:20px;">
          <p>Сума до оплати:</p>
          <p style="font-size:1.3rem; font-weight:600;"><?= number_format($total, 2, ',', ' ') ?> ₴</p>
        </div>
        <button type="submit" name="confirm_order" class="confirm-button">Підтвердити замовлення</button>
        <div class="back-link" style="margin-top:20px; text-align:center;">
          <button type="button" class="btn-prev" data-prev="2" style="background:none; border:none; color:#007bff; text-decoration:underline; cursor:pointer; font-size:0.95rem;">
            ← Повернутися назад
          </button>
        </div>
      </div>
    </form>
  </div>

  <!-- JS для навігації між кроками -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const steps = document.querySelectorAll('.step');
      const formSteps = document.querySelectorAll('.form-step');
      let currentStep = 1;

      function updateSteps() {
        formSteps.forEach((fs) => {
          fs.classList.remove('active');
          if (parseInt(fs.dataset.step) === currentStep) {
            fs.classList.add('active');
          }
        });
        steps.forEach((st) => {
          st.classList.remove('active', 'completed');
          const stepNum = parseInt(st.dataset.step);
          if (stepNum < currentStep) {
            st.classList.add('completed');
          } else if (stepNum === currentStep) {
            st.classList.add('active');
          }
        });
      }

      document.querySelectorAll('.btn-next').forEach((btn) => {
        btn.addEventListener('click', function() {
          const next = parseInt(this.dataset.next);
          // Перевіряємо валідність поточної форми перед переходом
          const currentForm = document.querySelector(`.form-step[data-step="${currentStep}"]`);
          const inputs = currentForm.querySelectorAll('input, textarea');
          let valid = true;
          inputs.forEach((inp) => {
            if (!inp.checkValidity()) {
              valid = false;
              inp.reportValidity();
            }
          });
          if (!valid) return;
          if (next <= formSteps.length) {
            currentStep = next;
            updateSteps();
            window.scrollTo({ top: 0, behavior: 'smooth' });
          }
        });
      });

      document.querySelectorAll('.btn-prev').forEach((btn) => {
        btn.addEventListener('click', function() {
          const prev = parseInt(this.dataset.prev);
          if (prev >= 1) {
            currentStep = prev;
            updateSteps();
            window.scrollTo({ top: 0, behavior: 'smooth' });
          }
        });
      });

      // Ініціалізація при завантаженні
      updateSteps();
    });
  </script>

</body>
</html>
