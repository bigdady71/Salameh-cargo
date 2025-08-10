<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$error = '';
$success = '';
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$phone = isset($_SESSION['login_phone']) ? $_SESSION['login_phone'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $error = 'Invalid security token. Please try again.';
    } else {
        if (isset($_POST['phone']) && $step === 1) {
            // Step 1: Request OTP
            $phone = trim($_POST['phone']);

            if (empty($phone)) {
                $error = 'Please enter your phone number.';
            } else {
                // Check if user exists
                $stmt = $pdo->prepare('SELECT user_id, full_name, phone FROM users WHERE phone = ?');
                $stmt->execute([$phone]);
                $user = $stmt->fetch();

                if ($user) {
                    // Store phone in session for step 2
                    $_SESSION['login_phone'] = $user['phone'];
                    $_SESSION['login_user_id'] = $user['user_id'];

                    // Generate a 6-digit OTP
                    $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                    $_SESSION['login_otp'] = password_hash($otp, PASSWORD_DEFAULT);
                    $_SESSION['login_otp_time'] = time();

                    require_once __DIR__ . '/../includes/twilio.php';
                    try {
                        if (sendWhatsAppOTP($user['phone'], $otp)) {
                            $success = 'Verification code sent to your WhatsApp. Please check your messages and enter the code below.';
                            $step = 2;
                        }
                    } catch (Exception $e) {
                        error_log('WhatsApp OTP Error: ' . $e->getMessage());
                        $error = 'Could not send verification code: ' . htmlspecialchars($e->getMessage());
                    }
                } else {
                    $error = 'Phone number not found. Please contact support to register your account.';
                }
            }
        } elseif (isset($_POST['otp']) && $step === 2) {
            // Step 2: Verify OTP
            $otp = trim($_POST['otp']);

            if (empty($otp)) {
                $error = 'Please enter the OTP code.';
            } elseif (!isset($_SESSION['login_phone']) || !isset($_SESSION['login_user_id']) || !isset($_SESSION['login_otp']) || !isset($_SESSION['login_otp_time'])) {
                $error = 'Session expired. Please start over.';
                $step = 1;
            } elseif (time() - $_SESSION['login_otp_time'] > 900) { // 15 minutes expiry
                $error = 'Verification code has expired. Please request a new one.';
                unset($_SESSION['login_otp']);
                unset($_SESSION['login_otp_time']);
                $step = 1;
            } else {
                // Verify submitted OTP
                if (password_verify($otp, $_SESSION['login_otp'])) {
                    $_SESSION['user_id'] = $_SESSION['login_user_id'];

                    // Log successful login
                    $stmt = $pdo->prepare('
                        INSERT INTO logs (action_type, actor_id, details) 
                        VALUES (:type, :actor_id, :details)
                    ');

                    $stmt->execute([
                        'type' => 'login_success',
                        'actor_id' => $_SESSION['login_user_id'],
                        'details' => 'Successful login via WhatsApp OTP'
                    ]);

                    // Clean up login session data
                    unset($_SESSION['login_phone']);
                    unset($_SESSION['login_user_id']);
                    unset($_SESSION['login_otp']);
                    unset($_SESSION['login_otp_time']);

                    header('Location: dashboard.php');
                    exit;
                } else {
                    // Log failed attempt
                    $stmt = $pdo->prepare('
                        INSERT INTO logs (action_type, actor_id, details) 
                        VALUES (:type, :actor_id, :details)
                    ');

                    $stmt->execute([
                        'type' => 'login_failed',
                        'actor_id' => $_SESSION['login_user_id'],
                        'details' => 'Invalid OTP attempt'
                    ]);

                    $error = 'Invalid verification code. Please try again.';
                }
            }
        }
    }
}

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

include __DIR__ . '/../includes/header.php';
?>

<main>
    <div class="container">
        <!-- Hero Section -->
        <section class="hero" style="min-height: 70vh;">
            <div class="hero__content">
                <h1 class="hero__title" style="font-size: 2.5rem;">Customer Login</h1>
                <p class="hero__subtitle">Access your shipment dashboard with our secure WhatsApp OTP verification.</p>

                <div style="max-width: 500px; margin: 3rem auto;">
                    <div class="card" style="padding: 3rem; text-align: left;">

                        <?php if ($error): ?>
                            <div class="alert error">
                                <i class="fas fa-exclamation-circle"></i>
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert success">
                                <i class="fas fa-check-circle"></i>
                                <?php echo htmlspecialchars($success); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($step === 1): ?>
                            <!-- Step 1: Phone Number Input -->
                            <div style="text-align: center; margin-bottom: 2rem;">
                                <div style="background: var(--card-hover); border-radius: 50%; width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                                    <i class="fas fa-mobile-alt" style="font-size: 2rem; color: var(--accent);"></i>
                                </div>
                                <h3 style="color: var(--text); margin-bottom: 0.5rem;">Enter Your Phone Number</h3>
                                <p style="color: var(--muted); font-size: 0.95rem;">We'll send you a verification code via WhatsApp</p>
                            </div>

                            <form method="post" action="?step=1">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="tel"
                                        id="phone"
                                        name="phone"
                                        required
                                        pattern="[0-9]{8,13}|\+961[0-9]{8,13}"
                                        placeholder="e.g. 71706478 or +96171706478"
                                        value="<?php echo htmlspecialchars($phone); ?>"
                                        style="text-align: center; font-size: 1.1rem;">
                                    <small>Enter your Lebanese mobile number (e.g. 71706478) or with country code (+96171706478). The system will auto-format for WhatsApp.</small>
                                </div>

                                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1.1rem; margin-top: 1rem;">
                                    <i class="fab fa-whatsapp"></i>
                                    Send OTP via WhatsApp
                                </button>
                            </form>

                        <?php else: ?>
                            <!-- Step 2: OTP Input -->
                            <div style="text-align: center; margin-bottom: 2rem;">
                                <div style="background: var(--card-hover); border-radius: 50%; width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                                    <i class="fas fa-key" style="font-size: 2rem; color: var(--accent);"></i>
                                </div>
                                <h3 style="color: var(--text); margin-bottom: 0.5rem;">Enter Verification Code</h3>
                                <p style="color: var(--muted); font-size: 0.95rem;">
                                    We sent a verification code to<br>
                                    <strong style="color: var(--accent);"><?php echo htmlspecialchars($phone); ?></strong>
                                </p>
                            </div>

                            <form method="post" action="?step=2">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

                                <div class="form-group">
                                    <label for="otp">Verification Code</label>
                                    <input type="text"
                                        id="otp"
                                        name="otp"
                                        required
                                        placeholder="Enter 4-6 digit code"
                                        maxlength="6"
                                        pattern="\d{4,6}"
                                        style="text-align: center; font-size: 1.5rem; letter-spacing: 0.5rem; font-weight: bold;">
                                    <small>Check your WhatsApp messages for the verification code</small>
                                </div>

                                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1.1rem; margin-top: 1rem;">
                                    <i class="fas fa-sign-in-alt"></i>
                                    Verify & Login
                                </button>

                                <div style="text-align: center; margin-top: 1.5rem;">
                                    <a href="?step=1" class="btn btn-secondary" style="padding: 0.75rem 1.5rem;">
                                        <i class="fas fa-arrow-left"></i>
                                        Use Different Number
                                    </a>
                                </div>
                            </form>

                            <div style="text-align: center; margin-top: 2rem; padding: 1rem; background: var(--card); border-radius: 8px; border-left: 4px solid var(--accent);">
                                <p style="color: var(--muted); font-size: 0.9rem; margin: 0;">
                                    <i class="fas fa-info-circle" style="color: var(--accent);"></i>
                                    Didn't receive the code? Please wait 60 seconds before requesting a new one.
                                </p>
                            </div>

                        <?php endif; ?>

                        <div style="text-align: center; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border);">
                            <p style="color: var(--muted); font-size: 0.9rem;">
                                Don't have an account?<br>
                                <a href="/public/contact.php" style="color: var(--accent);">
                                    Contact our team to register
                                </a>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Help Section -->
                <div style="max-width: 600px; margin: 3rem auto;">
                    <div class="help-section">
                        <h3><i class="fas fa-question-circle"></i> How does OTP login work?</h3>
                        <ol>
                            <li>Enter your registered phone number</li>
                            <li>We'll send you a verification code via WhatsApp</li>
                            <li>Enter the code to access your dashboard</li>
                            <li>View and track all your shipments securely</li>
                        </ol>
                        <p style="margin-top: 1.5rem;">
                            <strong>Need help?</strong>
                            <a href="https://wa.me/96171123456?text=I%20need%20help%20logging%20in%20to%20my%20account"
                                target="_blank" style="color: var(--accent);">
                                <i class="fab fa-whatsapp"></i>
                                Contact us on WhatsApp
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<script>
    // Auto-focus on input fields
    document.addEventListener('DOMContentLoaded', function() {
        const phoneInput = document.getElementById('phone');
        const otpInput = document.getElementById('otp');

        if (phoneInput) {
            phoneInput.focus();
        } else if (otpInput) {
            otpInput.focus();

            // Auto-submit when OTP is complete (optional enhancement)
            otpInput.addEventListener('input', function() {
                if (this.value.length === 6) {
                    // Small delay to let user see the complete code
                    setTimeout(() => {
                        this.form.submit();
                    }, 500);
                }
            });
        }
    });
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>