<?php
// login.php — database credentials

$db_host     = '127.0.0.1';
$db_name     = 's2793337_website';
$db_user     = 's2793337';
$db_password = 'Rahuldey@11112222';

function get_pdo() {
    global $db_host, $db_name, $db_user, $db_password;
    try {
        $pdo = new PDO(
            "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
            $db_user,
            $db_password
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        die("<p style='color:red'>Database connection failed: " .
            htmlspecialchars($e->getMessage()) . "</p>");
    }
}
?>
