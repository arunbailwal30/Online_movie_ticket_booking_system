<?php
// Demo mode configuration and allowlists
// Toggle this to false to restore full functionality
if (!defined('DEMO_MODE')) {
    define('DEMO_MODE', false);
}

// Public site allowed PHP entry points when DEMO_MODE is true
$DEMO_ALLOW_PUBLIC = array(
    'index.php',
    'movies_events.php',
    'about.php',
    'contact.php',
    // shared includes allowed so header/footer can render safely
    'header.php',
    'footer.php',
    'movie_sidebar.php',
    'searchbar.php'
);

// Admin area allowed PHP files when DEMO_MODE is true
$DEMO_ALLOW_ADMIN = array(
    'index.php',
    'logout.php'
);

// Theatre area allowed PHP files when DEMO_MODE is true
$DEMO_ALLOW_THEATRE = array(
    'index.php',
    'logout.php'
);
?>

