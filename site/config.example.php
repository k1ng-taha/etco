<?php
// Copy this file to config.php and fill in your own values.
// config.php is kept out of Git because it holds the private setup code.
return [
    // One-time code needed to create the FIRST admin account (on your first visit to /admin/).
    // Pick your own random code. After your account exists, it is no longer used.
    'setup_code' => 'CHANGE-ME-NOW',

    // true  = nice addresses like /en/projects/  (needs Apache mod_rewrite – normal on cPanel hosting)
    // false = addresses like /index.php?lang=en&p=projects  (use only if pages show "Not Found")
    'pretty_urls' => true,

    // set to true only while troubleshooting – shows PHP errors on screen
    'debug' => false,
];
