<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/icons.php';

// Detect whether to serve the public home or a specific storefront
if (isset($_GET['shop'])) {
    require __DIR__ . '/storefront.php';
} else {
    require __DIR__ . '/home.php';
}
