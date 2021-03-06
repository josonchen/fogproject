<?php
class System {
    public function __construct() {
        define('FOG_VERSION', '1.3.0-RC-1');
        define('FOG_SCHEMA', 232);
        define('FOG_BCACHE_VER',91);
        define('FOG_SVN_REVISION',5936);
        define('FOG_SVN_LAST_UPDATE', '$LastChangedDate: 2015-01-01 14:16:56 -0500 (Thu, 01 Jan 2015) $');
        define('FOG_CLIENT_VERSION', '0.11.4');
        define('PHP_VERSION_REQUIRED', '5.3.0');
        define('PHP_COMPATIBLE', version_compare(PHP_VERSION, PHP_VERSION_REQUIRED, '>='));
        define('SPACE_DEFAULT_STORAGE', '/images');
        if (PHP_COMPATIBLE === false) {
            die(sprintf(_('Your systems PHP version is not sufficient. You have version %s, version %s is required.'), PHP_VERSION, PHP_VERSION_REQUIRED));
            exit;
        }
    }
}
