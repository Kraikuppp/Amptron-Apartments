<?php
// ทดสอบการ redirect
echo "Testing logout redirect...\n";
echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "dirname: " . dirname($_SERVER['SCRIPT_NAME']) . "\n";

$base_path = dirname($_SERVER['SCRIPT_NAME']);
if ($base_path === '/' || $base_path === '\\' || empty($base_path)) {
    $redirect_url = '/index.php';
} else {
    $redirect_url = $base_path . '/index.php';
}

echo "Redirect URL: " . $redirect_url . "\n";
echo "Test: <a href='" . $redirect_url . "'>Click here</a>\n";
?>

