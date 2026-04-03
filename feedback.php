<?php
session_start();
require_once 'login.php';

$active_page = 'feedback';
$success_msg = '';
$error_msg   = '';

// ── Handle form submission ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $is_anonymous = isset($_POST['is_anonymous']) ? 1 : 0;
    $name         = $is_anonymous ? null : trim($_POST['name']  ?? '');
    $email        = $is_anonymous ? null : trim($_POST['email'] ?? '');
    $rating       = trim($_POST['rating']   ?? '');
    $message      = trim($_POST['message']  ?? '');
    $page         = trim($_POST['page']     ?? '');

    // Sanitise
    $name    = $name    ? htmlspecialchars($name)    : null;
    $email   = $email   ? htmlspecialchars($email)   : null;
    $rating  = htmlspecialchars($rating);
    $message = htmlspecialchars($message);
    $page    = htmlspecialchars($page);

    if ($rating === '') {
        $error_msg = 'Please select a satisfaction rating before submitting.';
    } else {
        $pdo  = get_pdo();
        $stmt = $pdo->prepare(
            "INSERT INTO Feedback
             (is_anonymous, name, email, rating, message, page)
             VALUES (:anon, :name, :email, :rating, :message, :page)"
        );
        $stmt->execute([
            ':anon'    => $is_anonymous,
            ':name'    => $name,
            ':email'   => $email,
            ':rating'  => $rating,
            ':message' => $message,
            ':page'    => $page
        ]);
        $success_msg = 'Thank you for your feedback — it is greatly appreciated!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProtExplorer &mdash; feedback</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .rating-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        .rating-option {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.6rem 0.9rem;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            cursor: pointer;
            transition: background 0.15s, border-color 0.15s;
            font-size: 0.9rem;
        }
        .rating-option:hover {
            background: var(--primary-light);
            border-color: var(--primary);
        }
        .rating-option input[type="radio"] {
            accent-color: var(--primary);
            width: 16px;
            height: 16px;
            flex-shrink: 0;
        }
        .rating-option input[type="radio"]:checked + span {
            color: var(--primary-dark);
            font-weight: 700;
        }
        .rating-emoji {
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
        }
        .anonymous-toggle {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            background: var(--primary-light);
            border-radius: var(--radius);
            margin-bottom: 1rem;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--primary-dark);
        }
        .anonymous-toggle input[type="checkbox"] {
            accent-color: var(--primary);
            width: 18px;
            height: 18px;
        }
        #identity-fields {
            transition: opacity 0.2s;
        }
        #identity-fields.hidden {
            display: none;
        }
    </style>
    <script>
        function toggleAnonymous() {
            var cb     = document.getElementById('is_anonymous');
            var fields = document.getElementById('identity-fields');
            if (cb.checked) {
                fields.classList.add('hidden');
                document.getElementById('name').required  = false;
            } else {
                fields.classList.remove('hidden');
            }
        }

        function validateFeedback() {
            var rating = document.querySelector('input[name="rating"]:checked');
            if (!rating) {
                alert('Please select a satisfaction rating before submitting.');
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
        <h1>Feedback</h1>
        <p>Help us improve ProtExplorer — your feedback is anonymous by default</p>
    </div>

    <?php if ($success_msg !== ''): ?>
        <div class="alert alert-success">
            <?php echo $success_msg; ?>
        </div>
        <div class="card" style="text-align:center;">
            <h2>what would you like to do next?</h2>
            <div style="display:flex; gap:1rem; justify-content:center;
                        flex-wrap:wrap; margin-top:0.5rem;">
                <a href="search.php"   class="btn btn-primary">run a search</a>
                <a href="example.php"  class="btn btn-accent">view example</a>
                <a href="feedback.php" class="btn btn-outline">submit more feedback</a>
            </div>
        </div>

    <?php else: ?>

        <?php if ($error_msg !== ''): ?>
            <div class="alert alert-error"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <div class="card">
            <h2>about this feedback form</h2>
            <p style="font-size:0.9rem; line-height:1.75;">
                Your feedback helps improve ProtExplorer for all users.
                Submissions are stored securely and are only visible to
                the site developer. You can submit anonymously — no
                personal information is required. If you would like a
                response, you can optionally provide your name and
                email address.
            </p>
        </div>

        <div class="card">
            <h2>share your feedback</h2>

            <form action="feedback.php" method="post"
                  onsubmit="return validateFeedback()">

                <!-- Anonymous toggle -->
                <label class="anonymous-toggle">
                    <input type="checkbox" id="is_anonymous"
                           name="is_anonymous" checked
                           onchange="toggleAnonymous()">
                    Submit anonymously &mdash; no personal details required
                </label>

                <!-- Identity fields — hidden by default -->
                <div id="identity-fields" class="hidden">
                    <div class="form-group">
                        <label for="name">your name</label>
                        <input type="text" id="name" name="name"
                               placeholder="e.g. Jane Smith">
                    </div>
                    <div class="form-group">
                        <label for="email">
                            your email address
                            <span style="font-weight:400;
                                         color:var(--text-muted);">
                                (optional — only if you want a response)
                            </span>
                        </label>
                        <input type="text" id="email" name="email"
                               placeholder="e.g. j.smith@university.ac.uk">
                        <span class="hint">
                            Your email will never be shared or used for
                            any purpose other than responding to your feedback.
                        </span>
                    </div>
                </div>

                <!-- Which page -->
                <div class="form-group">
                    <label for="page">which part of ProtExplorer are you
                        giving feedback on?</label>
                    <select id="page" name="page">
                        <option value="">-- select a page or feature --</option>
                        <option value="general">general / overall experience</option>
                        <option value="search">search page</option>
                        <option value="results">results page</option>
                        <option value="conservation">conservation analysis</option>
                        <option value="motifs">motif scanning</option>
                        <option value="phylogeny">phylogenetic tree</option>
                        <option value="structures">structure links</option>
                        <option value="example">example dataset</option>
                        <option value="help">help page</option>
                        <option value="mobile">mobile experience</option>
                        <option value="other">other</option>
                    </select>
                </div>

                <!-- Satisfaction rating -->
                <div class="form-group">
                    <label>how satisfied are you with ProtExplorer?
                        <span style="color:var(--error);">*</span>
                    </label>
                    <div class="rating-group">
                        <label class="rating-option">
                            <input type="radio" name="rating"
                                   value="very satisfied">
                            <span class="rating-emoji">&#128513;</span>
                            <span>Very satisfied</span>
                        </label>
                        <label class="rating-option">
                            <input type="radio" name="rating"
                                   value="satisfied">
                            <span class="rating-emoji">&#128512;</span>
                            <span>Satisfied</span>
                        </label>
                        <label class="rating-option">
                            <input type="radio" name="rating"
                                   value="neutral">
                            <span class="rating-emoji">&#128528;</span>
                            <span>Neutral</span>
                        </label>
                        <label class="rating-option">
                            <input type="radio" name="rating"
                                   value="dissatisfied">
                            <span class="rating-emoji">&#128577;</span>
                            <span>Dissatisfied</span>
                        </label>
                        <label class="rating-option">
                            <input type="radio" name="rating"
                                   value="very dissatisfied">
                            <span class="rating-emoji">&#128545;</span>
                            <span>Very dissatisfied</span>
                        </label>
                    </div>
                </div>

                <!-- Optional message -->
                <div class="form-group">
                    <label for="message">
                        additional comments
                        <span style="font-weight:400;
                                     color:var(--text-muted);">
                            (optional)
                        </span>
                    </label>
                    <textarea id="message" name="message"
                              rows="5"
                              placeholder="Tell us what you liked, what could be improved, or any bugs you encountered..."
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
                                      this.style.boxShadow=''"></textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    submit feedback
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
