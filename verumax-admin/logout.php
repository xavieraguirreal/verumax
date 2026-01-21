<?php
/**
 * VERUMAX SUPER ADMIN - Logout
 */

require_once __DIR__ . '/config.php';
require_once VERUMAX_ADMIN_PATH . '/includes/auth.php';

use VERUMaxAdmin\Auth;

Auth::logout();
redirect('login.php');
