<?php
require_once __DIR__ . '/database.php';

session_start();
if (isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $userID = find('users', 'username', $_POST['username']);
  if ($userID !== false) {
    $storedUser = read('users', $userID);
    if (password_verify($_POST['password'], $storedUser['password'])) {
      // Password is correct, start a session
      $_SESSION['user_id'] = $userID;
      $_SESSION['username'] = $storedUser['username'];
      header("Location: index.php");
      exit();
    } else {
      $error = "Invalid username or password.";
    }
  } else {
    $error = "Invalid username or password.";
  }
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>User Login</title>

  <!-- Bootstrap 5 CSS -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
    crossorigin="anonymous" />

  <style>
    body {
      background: #f8f9fa;
    }

    .card {
      border: 0;
      border-radius: 1rem;
      box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, .05);
    }

    .password-meter .progress {
      height: .5rem;
    }

    .form-check-input:focus {
      box-shadow: none;
    }
  </style>
</head>

<body>
  <main class="container py-5">
    <div class="row justify-content-center">
      <div class="col-12 col-lg-8 col-xl-7">
        <div class="card p-4 p-md-5">
          <h1 class="h3 mb-3 text-center">Login</h1>

          <form id="loginForm" class="needs-validation" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post" novalidate>
            <!-- Username -->
            <div class="row g-3 mt-1">
              <div class="col-12">
                <label for="username" class="form-label">Username</label>
                <div class="input-group has-validation">
                  <span class="input-group-text">@</span>
                  <input type="text" class="form-control" id="username" name="username"
                    autocomplete="username" minlength="3" required value="<?= $_POST['username'] ?? '' ?>" />
                  <div class="invalid-feedback">Enter your username.</div>
                </div>
              </div>
            </div>

            <!-- Password -->
            <div class="row g-3 mt-1">
              <div class="col-12">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                  <input type="password" class="form-control" id="password" name="password"
                    autocomplete="current-password"
                    minlength="8"
                    required value="<?= $_POST['password'] ?? '' ?>" />
                  <button class="btn btn-outline-secondary" type="button" id="togglePw">
                    <span class="visually-hidden">Toggle password visibility</span>👁️
                  </button>
                  <div class="invalid-feedback">Enter your password.</div>
                </div>
              </div>
            </div>

            <!-- Submit -->
            <div class="d-grid mt-4">
              <button class="btn btn-primary btn-lg" type="submit">Login</button>
            </div>

            <?php if (isset($error)): ?>
              <div class="alert alert-danger mt-3" role="alert">
                <?= htmlspecialchars($error) ?>
              </div>
            <?php endif; ?>

            <!-- Small print -->
            <p class="text-center text-muted mt-3 mb-0">
              Don't have an account? <a href="register.php" class="link-primary">Register</a>
            </p>
          </form>
        </div>
      </div>
    </div>
  </main>

  <!-- Bootstrap JS (for validation styles & components) -->
  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
    crossorigin="anonymous">
  </script>

  <script>
    // Bootstrap custom validation
    (function() {
      'use strict';
      const form = document.getElementById('loginForm');
      const pw = document.getElementById('password');
      const togglePw = document.getElementById('togglePw');

      // Toggle password visibility
      togglePw.addEventListener('click', () => {
        const type = pw.getAttribute('type') === 'password' ? 'text' : 'password';
        pw.setAttribute('type', type);
      });

      form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        }
        form.classList.add('was-validated');
      }, false);
    })();
  </script>
