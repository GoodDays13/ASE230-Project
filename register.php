<?php
require_once 'authentication.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $user = ['username' => $_POST['username'], 'email' => $_POST['email'], 'password' => $_POST['new-password']];
  $error = register($user);
  if ($error === true) {
    // Registration successful, redirect to login or another page
    header("Location: login.php");
    exit();
  }
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>User Registration</title>

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
          <h1 class="h3 mb-3 text-center">Create Your Account</h1>
          <p class="text-secondary text-center mb-4">It’s quick and easy.</p>

          <form id="registrationForm" class="needs-validation" enctype="multipart/form-data" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post" novalidate>
            <!-- Username / Email -->
            <div class="row g-3 mt-1">
              <div class="col-md-6">
                <label for="username" class="form-label">Username</label>
                <div class="input-group has-validation">
                  <span class="input-group-text">@</span>
                  <input type="text" class="form-control" id="username" name="username"
                    autocomplete="username" minlength="3" pattern="^[a-zA-Z0-9_]+$" required />
                  <div class="invalid-feedback">Username can only contain letters, numbers, and underscores.</div>
                </div>
              </div>
              <div class="col-md-6">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email"
                  autocomplete="email" required />
                <div class="invalid-feedback">Enter a valid email address.</div>
              </div>
            </div>

            <!-- Passwords -->
            <div class="row g-3 mt-1">
              <div class="col-md-6">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                  <input type="password" class="form-control" id="password" name="new-password"
                    autocomplete="new-password"
                    minlength="8"
                    aria-describedby="passwordHelp"
                    required />
                  <button class="btn btn-outline-secondary" type="button" id="togglePw">
                    <span class="visually-hidden">Toggle password visibility</span>👁️
                  </button>
                  <div class="invalid-feedback">Min. 8 chars with mixed complexity recommended.</div>
                </div>
                <div id="passwordHelp" class="form-text">
                  Use at least 8 characters. Mix letters, numbers, and symbols for a stronger password.
                </div>
                <div class="password-meter mt-2">
                  <div class="progress" role="progressbar" aria-label="Password strength"
                    aria-valuemin="0" aria-valuemax="100">
                    <div id="pwBar" class="progress-bar" style="width: 0%"></div>
                  </div>
                  <small id="pwLabel" class="text-muted">Strength: —</small>
                </div>
              </div>
              <div class="col-md-6">
                <label for="confirmPassword" class="form-label">Confirm password</label>
                <input type="password" class="form-control" id="confirmPassword" name="confirmPassword"
                  autocomplete="new-password" required />
                <div class="invalid-feedback">Passwords must match.</div>
              </div>
            </div>

            <!-- Terms -->
            <div class="form-check mt-4">
              <input class="form-check-input" type="checkbox" value="" id="terms" required />
              <label class="form-check-label" for="terms">
                I agree to the <a href="#" class="link-primary">Terms</a> and <a href="#" class="link-primary">Privacy Policy</a>.
              </label>
              <div class="invalid-feedback">You must agree before submitting.</div>
            </div>

            <!-- Submit -->
            <div class="d-grid mt-4">
              <button class="btn btn-primary btn-lg" type="submit">Create Account</button>
            </div>

            <!-- Optional: small print -->
            <p class="text-center text-muted mt-3 mb-0">
              Already have an account? <a href="login.php" class="link-primary">Sign in</a>
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
      const form = document.getElementById('registrationForm');
      const pw = document.getElementById('password');
      const cpw = document.getElementById('confirmPassword');
      const bar = document.getElementById('pwBar');
      const label = document.getElementById('pwLabel');
      const togglePw = document.getElementById('togglePw');

      // Strength checker (simple heuristic)
      function scorePassword(p) {
        let score = 0;
        if (!p) return 0;
        const variations = {
          digits: /\d/.test(p),
          lower: /[a-z]/.test(p),
          upper: /[A-Z]/.test(p),
          symbols: /[^A-Za-z0-9]/.test(p)
        };
        const variety = Object.values(variations).filter(Boolean).length;
        score += Math.min(6, Math.floor(p.length / 2)) * 10; // length bonus
        score += (variety - 1) * 15; // character variety
        if (/(.)\1{2,}/.test(p)) score -= 10; // penalty for repeats
        return Math.max(0, Math.min(100, score));
      }

      function updateMeter() {
        const s = scorePassword(pw.value);
        bar.style.width = s + '%';
        bar.classList.remove('bg-danger', 'bg-warning', 'bg-success');
        let text = '—';
        if (s < 40) {
          bar.classList.add('bg-danger');
          text = 'Weak';
        } else if (s < 70) {
          bar.classList.add('bg-warning');
          text = 'Fair';
        } else {
          bar.classList.add('bg-success');
          text = 'Strong';
        }
        label.textContent = 'Strength: ' + text;
      }

      function validateMatch() {
        if (cpw.value && pw.value !== cpw.value) {
          cpw.setCustomValidity('Passwords do not match');
        } else {
          cpw.setCustomValidity('');
        }
      }

      pw.addEventListener('input', () => {
        updateMeter();
        validateMatch();
      });
      cpw.addEventListener('input', validateMatch);
      updateMeter();

      // Toggle password visibility
      togglePw.addEventListener('click', () => {
        const type = pw.getAttribute('type') === 'password' ? 'text' : 'password';
        pw.setAttribute('type', type);
      });

      form.addEventListener('submit', function(event) {
        validateMatch();
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        } else {
          // Demo: prevent actual submission and show a toast/alert
          //   event.preventDefault();
          //   const alert = document.createElement('div');
          //   alert.className = 'alert alert-success mt-3';
          //   alert.role = 'alert';
          //   alert.textContent = 'Registration successful! (Demo only — no data sent)';
          //   form.appendChild(alert);
          //   form.classList.remove('was-validated');
          //   form.reset();
          //   updateMeter();
        }
        form.classList.add('was-validated');
      }, false);
    })();
  </script>
</body>

</html>
