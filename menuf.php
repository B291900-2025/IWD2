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
    <?php nav_link('search.php',  'search',  $active_page); ?>
    <?php nav_link('example.php', 'example', $active_page); ?>
    <?php nav_link('history.php', 'history', $active_page); ?>
    <?php nav_link('help.php',    'help',    $active_page); ?>
    <?php nav_link('about.php',   'about',   $active_page); ?>
    <?php nav_link('credits.php', 'credits', $active_page); ?>
</nav>
