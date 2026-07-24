<?php

/**
 * PolyCMS Shared Hosting Entry Point
 * 
 * This file allows PolyCMS to run on shared hosting environments (like cPanel/LiteSpeed)
 * where the document root cannot be changed to the public/ directory.
 */

require_once __DIR__.'/public/index.php';
