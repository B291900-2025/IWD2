<?php
$output = shell_exec('which python3 2>&1');
echo "python3 path: " . $output . "<br>";

$output2 = shell_exec('python3 --version 2>&1');
echo "python3 version: " . $output2 . "<br>";

$output3 = shell_exec('pwd 2>&1');
echo "working directory: " . $output3 . "<br>";
?>
