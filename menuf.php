<?php
// menuf.php — shared navigation bar
if (!isset($active_page)) $active_page = '';

function nav_link($href, $label, $active_page) {
    $class = ($active_page === $label) ? ' class="active"' : '';
    echo "<a href='$href'$class>$label</a>";
}
?>
<nav>
    <a class="brand" href="index.php">ProtExplorer</a>
    <?php nav_link('search.php',   'Search',   $active_page); ?>
    <?php nav_link('example.php',  'Example',  $active_page); ?>
    <?php nav_link('history.php',  'History',  $active_page); ?>
    <?php nav_link('help.php',     'Help',     $active_page); ?>
    <?php nav_link('feedback.php', 'Feedback', $active_page); ?>
    <?php nav_link('contact.php',  'Contact',  $active_page); ?>
    <a href="https://github.com/B291900-2025/IWD2"
       target="_blank"
       style="margin-left:auto;">GitHub</a>
    <?php nav_link('about.php',    'About',    $active_page); ?>
    <?php nav_link('credits.php',  'Credits',  $active_page); ?>
</nav>

<!-- Loading overlay — available on all pages -->
<div class="loading-overlay" id="loading-overlay">
    <div class="loading-box">
        <h3 id="loading-title">Processing...</h3>
        <p id="loading-message">Please wait while your request is processed.</p>
        <div class="progress-bar-wrap">
            <div class="progress-bar-fill" id="progress-bar-fill"></div>
        </div>
        <div class="progress-label" id="progress-label">0%</div>
        <div class="loading-steps" id="loading-steps"></div>
    </div>
</div>
<?php
$base_url = 'https://bioinfmsc8.bio.ed.ac.uk/~s2793337/Website/';
?>
<script src="<?php echo $base_url; ?>loading.js"></script>
<script src="<?php echo $base_url; ?>animate.js"></script>
