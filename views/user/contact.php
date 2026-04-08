<?php
$page_title = 'Contact Us';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/Feedback.php';

$feedback = new Feedback();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $_SESSION['error'] = 'Invalid security token. Please try again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if (empty($name) || empty($email) || empty($message)) {
            $_SESSION['error'] = 'All fields are required';
        } elseif (!validateEmail($email)) {
            $_SESSION['error'] = 'Invalid email format';
        } elseif (strlen($message) < 10) {
            $_SESSION['error'] = 'Message must be at least 10 characters';
        } else {
            $name = sanitize($name);
            $email = sanitize($email);
            $message = sanitize($message);

            $success = $feedback->create([
                'user_id' => $_SESSION['user_id'] ?? null,
                'name' => $name,
                'email' => $email,
                'message' => $message
            ]);

            if ($success) {
                flashSuccess('Thank you! Your message has been sent. We will get back to you soon.');
                redirect(BASE_URL . 'views/user/contact.php');
            } else {
                flashError('Sorry, there was a problem saving your message. Please try again later.');
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-xl py-5">
    
    <!-- Hero Header -->
    <div class="row justify-content-center text-center mb-5 animate-fade-up">
        <div class="col-lg-7">
            <span class="badge bg-success mb-3 px-3 py-2" style="letter-spacing:1px;">GET IN TOUCH</span>
            <h1 class="fw-black mb-4 fs-1">We'd Love to Hear From You</h1>
            <p class="text-muted fs-5">
                Have a suggestion, question, or just want to say hello? 
                The Smart Pantry team is here to help you reduce food waste.
            </p>
        </div>
    </div>

    <div class="row g-5">
        <!-- Contact Form -->
        <div class="col-lg-7">
            <div class="sp-card p-5 animate-fade-up">
                <h3 class="fw-black mb-4">Send Us a Message</h3>
                
                <form method="POST" action="<?= BASE_URL ?>views/user/contact.php" class="needs-validation">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken(); ?>">

                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase">Full Name</label>
                            <input type="text" name="name" required maxlength="100" 
                                   class="form-control form-control-lg border-0 bg-light" 
                                   style="border-radius:12px;"
                                   placeholder="Your Name"
                                   value="<?= htmlspecialchars($_SESSION['full_name'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase">Email Address</label>
                            <input type="email" name="email" required 
                                   class="form-control form-control-lg border-0 bg-light" 
                                   style="border-radius:12px;"
                                   placeholder="you@example.com"
                                   value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small text-muted text-uppercase">Your Message</label>
                        <textarea name="message" rows="6" required minlength="10" maxlength="2000"
                                  class="form-control border-0 bg-light"
                                  style="border-radius:15px;"
                                  placeholder="Tell us what's on your mind..."></textarea>
                        <div class="form-text small">Minimum 10 characters</div>
                    </div>

                    <button type="submit" class="btn-sp-primary w-100 py-3 fw-bold fs-5">
                        <i class="bi bi-send-fill me-2"></i> Send Message
                    </button>
                </form>
            </div>
        </div>

        <!-- Info Sidebar -->
        <div class="col-lg-5">
            <div class="row g-4 animate-fade-up" style="animation-delay: 0.1s;">
                <div class="col-12">
                    <div class="sp-card p-4 d-flex gap-4 align-items-center">
                        <div class="rounded-4 d-flex align-items-center justify-content-center" 
                             style="width:60px; height:60px; background:#dcfce7; color:#16a34a; font-size:1.5rem;">
                            <i class="bi bi-envelope-heart-fill"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">Email Support</h5>
                            <p class="text-muted small mb-0">hello@smartpantry.com</p>
                            <p class="text-muted small mb-0">support@smartpantry.com</p>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="sp-card p-4 d-flex gap-4 align-items-center">
                        <div class="rounded-4 d-flex align-items-center justify-content-center" 
                             style="width:60px; height:60px; background:#dbeafe; color:#2563eb; font-size:1.5rem;">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">Our Location</h5>
                            <p class="text-muted small mb-0">Pashupatinath Road 44600</p>
                            <p class="text-muted small mb-0">Kathmandu, Nepal</p>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="sp-card p-4">
                        <h5 class="fw-bold mb-3"><i class="bi bi-question-circle-fill text-success me-2"></i>FAQ Highlights</h5>
                        
                        <div class="accordion accordion-flush" id="faqAccordion">
                            <div class="accordion-item bg-transparent">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed bg-transparent fw-bold small p-2" type="button" data-bs-toggle="collapse" data-bs-target="#f1">
                                        How accurate is the matching?
                                    </button>
                                </h2>
                                <div id="f1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body small text-muted">
                                        We rank recipes based on core ingredients. The more you add, the higher the accuracy!
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item bg-transparent">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed bg-transparent fw-bold small p-2" type="button" data-bs-toggle="collapse" data-bs-target="#f2">
                                        Can I request new recipes?
                                    </button>
                                </h2>
                                <div id="f2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body small text-muted">
                                        Absolutely! Drop us a message here with your favorite dish names.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Message History (for logged in users) -->
    <?php if (isLoggedIn()): ?>
        <?php $myMessages = $feedback->getUserFeedback($_SESSION['user_id']); ?>
        <?php if (!empty($myMessages)): ?>
            <div class="row mt-5 animate-fade-up" style="animation-delay:0.2s;">
                <div class="col-12">
                    <div class="sp-card p-5">
                        <h3 class="fw-black mb-4"><i class="bi bi-chat-left-text-fill text-primary me-2"></i>My Message History</h3>
                        <div class="message-timeline">
                            <?php foreach ($myMessages as $msg): ?>
                                <div class="mb-4 pb-4 border-bottom last-child-border-0">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="small text-muted fw-bold"><?= formatDate($msg['created_at']) ?></div>
                                        <span class="badge <?= $msg['status'] === 'pending' ? 'bg-warning text-dark' : 'bg-success text-white' ?> text-uppercase" style="font-size:0.65rem; padding: 0.4em 0.8em;">
                                            <?= $msg['status'] ?>
                                        </span>
                                    </div>
                                    <div class="p-3 bg-light rounded-3 mb-3 border-start border-4 border-success">
                                        <div class="small fw-bold mb-1">Your Message:</div>
                                        <div class="text-dark"><?= nl2br(htmlspecialchars($msg['message'])) ?></div>
                                    </div>
                                    
                                    <?php if ($msg['admin_response']): ?>
                                        <div class="p-3 rounded-3 ms-4" style="background: #eff6ff; border-left: 4px solid #3b82f6;">
                                            <div class="d-flex align-items-center gap-2 mb-2 text-primary fw-bold small">
                                                <i class="bi bi-person-check-fill"></i> SmartPantry Admin Response
                                            </div>
                                            <div class="text-dark small" style="line-height:1.6;">
                                                <?= nl2br(htmlspecialchars($msg['admin_response'])) ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
