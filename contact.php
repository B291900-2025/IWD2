<?php
session_start();
require_once 'login.php';

$active_page = 'contact';
$success_msg = '';
$error_msg   = '';

// ── Handle form submission ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name    = trim($_POST['name']    ?? '');
    $email   = trim($_POST['email']   ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Sanitise
    $name    = htmlspecialchars($name);
    $email   = htmlspecialchars($email);
    $subject = htmlspecialchars($subject);
    $message = htmlspecialchars($message);

    // Validate
    if ($name === '' || $message === '') {
        $error_msg = 'Please fill in your name and message before submitting.';
    } elseif ($email !== '' && !filter_var(
        filter_var($email, FILTER_SANITIZE_EMAIL),
        FILTER_VALIDATE_EMAIL
    )) {
        $error_msg = 'Please enter a valid email address, or leave it blank.';
    } else {
        // Store in database via PDO
        $pdo  = get_pdo();
        $stmt = $pdo->prepare(
            "INSERT INTO Feedback
             (is_anonymous, name, email, rating, message, page)
             VALUES (0, :name, :email, 'contact', :message, :subject)"
        );
        $stmt->execute([
            ':name'    => $name,
            ':email'   => $email,
            ':message' => "SUBJECT: $subject\n\nMESSAGE: $message"
        ]);
        $success_msg = 'Your message has been received. Thank you for getting in touch!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProtExplorer &mdash; contact</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .contact-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .contact-info-box {
            background: var(--primary-light);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.25rem;
        }
        .contact-info-box h4 {
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 0.4rem;
        }
        .contact-info-box p {
            font-size: 0.85rem;
            color: var(--text-muted);
            line-height: 1.6;
        }
        .contact-info-box a {
            color: var(--primary);
            text-decoration: none;
            border-bottom: 1px dotted var(--primary);
        }
        .contact-info-box a:hover { border-bottom-style: solid; }
        @media (max-width: 600px) {
            .contact-info-grid { grid-template-columns: 1fr; }
        }
    </style>
    <script>
        function validateContact() {
            var name    = document.getElementById('name').value.trim();
            var message = document.getElementById('message').value.trim();

            if (name === '') {
                alert('Please enter your name.');
                return false;
            }
            if (message === '') {
                alert('Please enter a message.');
                return false;
            }
            return true;
        }
    </script>
</head>
<body>

<?php require_once 'menuf.php'; ?>

<div class="page-wrap">

    <div class="page-header" style="margin-top:2rem;">
        <h1>Contact</h1>
        <p>Get in touch with the ProtExplorer developer</p>
    </div>

    <?php if ($success_msg !== ''): ?>
        <div class="alert alert-success">
            <?php echo $success_msg; ?>
        </div>
        <div class="card" style="text-align:center;">
            <h2>message received</h2>
            <p class="card-desc">
                We will get back to you as soon as possible if you
                provided an email address. In the meantime, feel free
                to continue using ProtExplorer.
            </p>
            <div style="display:flex; gap:1rem; justify-content:center;
                        flex-wrap:wrap; margin-top:0.75rem;">
                <a href="search.php"  class="btn btn-primary">run a search</a>
                <a href="contact.php" class="btn btn-outline">send another message</a>
            </div>
        </div>

    <?php else: ?>

        <?php if ($error_msg !== ''): ?>
            <div class="alert alert-error"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <!-- Contact info cards -->
        <div class="contact-info-grid">
            <div class="contact-info-box">
                <h4>general enquiries</h4>
                <p>
                    Use the form below to ask questions about ProtExplorer,
                    report a bug, or request a new feature. We aim to
                    respond within 2&ndash;3 working days.
                </p>
            </div>
            <div class="contact-info-box">
                <h4>technical issues</h4>
                <p>
                    If you encounter an error or unexpected behaviour,
                    please describe what you were doing and what you
                    expected to happen. Screenshots are helpful if
                    you can include them in your message.
                </p>
            </div>
            <div class="contact-info-box">
                <h4>source code</h4>
                <p>
                    The full source code for ProtExplorer is available
                    on GitHub:
                    <a href="https://github.com/B291900-2025/IWD2"
                       target="_blank">
                        github.com/B291900-2025/IWD2
                    </a>
                </p>
            </div>
            <div class="contact-info-box">
                <h4>give feedback</h4>
                <p>
                    Want to rate your experience or leave a comment?
                    Visit the dedicated
                    <a href="feedback.php">feedback page</a>
                    where you can submit anonymously.
                </p>
            </div>
        </div>

        <!-- Contact form -->
        <div class="card">
            <h2>send a message</h2>
            <p style="font-size:0.88rem; color:var(--text-muted);
                      margin-bottom:1rem; line-height:1.6;">
                Your message will be stored securely and reviewed by
                the ProtExplorer developer. Your contact details will
                never be shared with third parties.
            </p>

            <form action="contact.php" method="post"
                  onsubmit="return validateContact()">

                <div class="form-group">
                    <label for="name">
                        your name
                        <span style="color:var(--error);">*</span>
                    </label>
                    <input type="text" id="name" name="name"
                           placeholder="e.g. Jane Smith"
                           value="<?php echo isset($_POST['name'])
                               ? htmlspecialchars($_POST['name']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="email">
                        your email address
                        <span style="font-weight:400;
                                     color:var(--text-muted);">
                            (optional &mdash; required if you want a reply)
                        </span>
                    </label>
                    <input type="text" id="email" name="email"
                           placeholder="e.g. j.smith@university.ac.uk"
                           value="<?php echo isset($_POST['email'])
                               ? htmlspecialchars($_POST['email']) : ''; ?>">
                    <span class="hint">
                        Your email address will only be used to respond
                        to your message and will never be displayed
                        publicly or shared.
                    </span>
                </div>

                <div class="form-group">
                    <label for="subject">subject</label>
                    <select id="subject" name="subject">
                        <option value="">-- select a subject --</option>
                        <option value="Bug report">Bug report</option>
                        <option value="Feature request">Feature request</option>
                        <option value="Question about results">
                            Question about results
                        </option>
                        <option value="Question about the biology">
                            Question about the biology
                        </option>
                        <option value="Technical issue">Technical issue</option>
                        <option value="Collaboration">Collaboration</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="message">
                        message
                        <span style="color:var(--error);">*</span>
                    </label>
                    <textarea id="message" name="message"
                              rows="6"
                              placeholder="Describe your question, issue or suggestion in as much detail as possible..."
                              style="width:100%; padding:0.6rem 0.85rem;
                                     border:1px solid var(--border);
                                     border-radius:var(--radius);
                                     font-size:0.95rem;
                                     font-family:var(--font);
                                     color:var(--text);
                                     resize:vertical;
                                     transition:border-color 0.15s;"
                              onfocus="this.style.borderColor='var(--primary)';
                                       this.style.boxShadow='0 0 0 3px rgba(124,111,205,0.15)'"
                              onblur="this.style.borderColor='';
                                      this.style.boxShadow=''"
                              ><?php echo isset($_POST['message'])
                                  ? htmlspecialchars($_POST['message'])
                                  : ''; ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    send message
                </button>

            </form>
        </div>

    <?php endif; ?>

</div>

<footer>
    ProtExplorer &mdash; IWD2 assessed website &mdash;
    <a href="credits.php">credits &amp; AI usage</a>
</footer>

</body>
</html>
