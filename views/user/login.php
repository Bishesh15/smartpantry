<?php
$page_title = 'Login';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/constants.php';

if (isLoggedIn()) redirect(BASE_URL . 'views/user/dashboard.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | SmartPantry</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= ASSETS_PATH ?>css/style.css?v=2" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card">
    <div class="text-center mb-4">
      <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-3"
           style="width:56px;height:56px;background:#16a34a;">
        <i class="bi bi-egg-fried text-white" style="font-size:1.6rem;"></i>
      </div>
      <h2 class="fw-black">Welcome Back</h2>
      <p class="text-muted small">Sign in to access your pantry &amp; recipes</p>
    </div>

    <?= renderFlash() ?>

    <form method="POST" action="<?= BASE_URL ?>controllers/AuthController.php">
      <input type="hidden" name="action" value="login">
      <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

      <div class="mb-3">
        <label class="sp-label">Username</label>
        <input type="text" name="username" class="sp-form-control"
               placeholder="Your username" autocomplete="username" required>
      </div>
      <div class="mb-3">
        <label class="sp-label">Password</label>
        <input type="password" name="password" class="sp-form-control"
               placeholder="Your password" autocomplete="current-password" required>
      </div>

    <button type="submit" class="btn-sp-primary w-100 justify-content-center py-3 mt-2" style="font-size:1rem;">
        <i class="bi bi-box-arrow-in-right"></i> Sign In
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
         data-text="sign_in_with"
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
                alert(data.message || 'Google Login Failed');
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('An error occurred during Google Login.');
        });
    }
    </script>

    <hr class="my-4">
    <p class="text-center text-muted small mb-0">
      Don't have an account?
      <a href="<?= BASE_URL ?>views/user/register.php" class="fw-bold">Register here</a>
    </p>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>document.documentElement.dataset.baseUrl='<?= BASE_URL ?>';</script>
<script src="<?= ASSETS_PATH ?>js/main.js"></script>
</body>
</html>
