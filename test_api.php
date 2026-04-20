<?php
// Debug API path parsing
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "PATH_INFO: " . ($_SERVER['PATH_INFO'] ?? 'N/A') . "\n";
echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "PHP_SELF: " . $_SERVER['PHP_SELF'] . "\n";

require 'config.php';

$path = ltrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
if ($path === 'test_api.php/test') $path = 'api/test';

echo "Parsed path: '$path'\n";

if ($path === 'api/test') {
    echo json_encode(['status' => 'API ready']);
} else {
    echo json_encode(['error' => 'Not matched']);
}
?>

