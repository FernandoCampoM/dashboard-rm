<?php
require_once 'config.php';
$result = callAPI('Clients');
echo '<pre>';
print_r($result);
echo '</pre>';