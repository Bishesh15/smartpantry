<?php
$page_title = 'Register';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/constants.php';

if (isLoggedIn()) redirect(BASE_URL . 'views/user/dashboard.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Register | SmartPantry</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= ASSETS_PATH ?>css/style.css?v=2" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card" style="max-width:500px;">
    <div class="text-center mb-4">
      <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-3"
           style="width:56px;height:56px;background:#16a34a;">
        <i class="bi bi-person-plus text-white" style="font-size:1.6rem;"></i>
      </div>
      <h2 class="fw-black">Create Account</h2>
      <p class="text-muted small">Join SmartPantry and start saving on food waste</p>
    </div>

    <?= renderFlash() ?>

    <form method="POST" action="<?= BASE_URL ?>controllers/AuthController.php" id="registerForm">
      <input type="hidden" name="action" value="register">
      <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

      <div class="row g-3">
        <div class="col-12">
          <label class="sp-label">Full Name</label>
          <input type="text" name="full_name" class="sp-form-control"
                 placeholder="e.g. Bishesh Shrestha" required
                 value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
        </div>
        <div class="col-sm-6">
          <label class="sp-label">Username</label>
          <input type="text" name="username" class="sp-form-control"
                 placeholder="e.g. bishesh15" pattern="[a-zA-Z0-9_]{3,30}" required
                 title="3–30 characters, letters/numbers/underscore"
                 value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
        </div>
        <div class="col-sm-6">
          <label class="sp-label">Email</label>
          <input type="email" name="email" class="sp-form-control"
                 placeholder="you@example.com" required
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="col-sm-6">
          <label class="sp-label">Password</label>
          <input type="password" name="password" class="sp-form-control"
                 placeholder="Min. 6 characters" minlength="6" required id="pwdInput">
        </div>
        <div class="col-sm-6">
          <label class="sp-label">Confirm Password</label>
          <input type="password" name="confirm_password" class="sp-form-control"
                 placeholder="Repeat password" minlength="6" required id="pwdConfirm">
          <div class="invalid-feedback" id="pwdError">Passwords do not match.</div>
        </div>
      </div>

      <button type="submit" class="btn-sp-primary w-100 justify-content-center py-3 mt-4" style="font-size:1rem;">
        <i class="bi bi-person-check-fill"></i> Create My Account
      </button>
    </form>

    <div class="my-4 d-flex align-items-center gap-3 text-muted">
        <hr class="flex-grow-1"> <span class="small fw-bold">OR</span> <hr class="flex-grow-1">
    </div>

    <!-- Google Sign-In -->
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <div id="g_id_onload"
         data-client_id="<?= GOOGLE_CLIENT_ID ?>"
         data-callback="handleGoogleResponse"
         data-auto_prompt="false">
    </div>
    <div class="g_id_signin"
         data-type="standard"
         data-size="large"
         data-theme="outline"
         data-text="signup_with"
         data-shape="rectangular"
         data-logo_alignment="left"
         data-width="100%">
    </div>

    <script>
    function handleGoogleResponse(response) {
        const formData = new FormData();
        formData.append('credential', response.credential);

        fetch('<?= BASE_URL ?>controllers/GoogleAuthController.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.href = '<?= BASE_URL ?>views/user/dashboard.php';
            } else {
                alert(data.message || 'Google Auth Failed');
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('An error occurred during Google Auth.');
        });
    }
    </script>

    <hr class="my-4">
    <p class="text-center text-muted small mb-0">
      Already have an account? <a href="<?= BASE_URL ?>views/user/login.php" class="fw-bold">Sign in</a>
    </p>
  </div>
</div>

<script>
document.getElementById('registerForm').addEventListener('submit', function(e) {
  const pwd     = document.getElementById('pwdInput').value;
  const confirm = document.getElementById('pwdConfirm').value;
  if (pwd !== confirm) {
    e.preventDefault();
    document.getElementById('pwdConfirm').classList.add('is-invalid');
    document.getElementById('pwdError').style.display = 'block';
  }
});
document.getElementById('pwdConfirm').addEventListener('input', function() {
  this.classList.remove('is-invalid');
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>document.documentElement.dataset.baseUrl='<?= BASE_URL ?>';</script>
<script src="<?= ASSETS_PATH ?>js/main.js"></script>
</body>
</html>
