<?php

require __DIR__ . '/html/config.php';

$dbUpdater = new DbUpdater();

class DbUpdater
{
    /** @var \mysqli A mysqli handle holding the database connection. */
    private $handle = null;

    public function __construct()
    {
        $this->handle = @new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
        if ($this->handle->connect_errno) {
            printf('ERROR: Failed to connect to MySQL: (%s) %s%s', $this->handle->connect_errno, $this->handle->connect_error, PHP_EOL);
            $this->handle = null;
        }
    }

    public function run($statement)
    {
        if ($this->handle === null) {
            printf('ERROR: Could not execute the statement: "%s"%s', $statement, PHP_EOL);
            return;
        }

        if (!$this->handle->query($statement)) {
            printf('ERROR: Could not execute the statement: "%s" %s%s', $statement, $this->handle->error, PHP_EOL);
            return;
        }

        if ($this->handle->affected_rows == 0) {
            printf('WARNING: This statement had no effect: "%s"%s', $statement, PHP_EOL);
            return;
        }

        printf('Successfully executed the statement: "%s"%s', $statement, PHP_EOL);
    }

    public function __destruct()
    {
        if ($this->handle !== null) {
            $this->handle->close();
        }
    }
}

function replaceFileContents($filepath, $replacements = array())
{
    if (empty($replacements)) {
        return;
    }

    $contents = array();
    if (!file_exists($filepath)) {
        printf('ERROR: The file "%s" does not exist.%s', $filepath, PHP_EOL);
        return;
    }

    $file = @fopen($filepath, 'r');
    if ($file === false) {
        printf('ERROR: Could not open the file "%s".%s', $filepath, PHP_EOL);
        return;
    }

    printf('Replacing contents of the file "%s":%s', $filepath, PHP_EOL);
    while (!feof($file)) {
        $line = fgets($file);
        foreach ($replacements as $needle => $replacement) {
            if (0 === strpos($line, $needle)) {
                $line = $replacement;
                printf('  %s', $replacement);
                break;
            }
        }
        array_push($contents, $line);
    }
    fclose($file);

    if (!file_put_contents($filepath, join('', $contents))) {
        printf('ERROR: Could not update the file "%s".%s', $filepath, PHP_EOL);
        return;
    }

    printf('Successfully updated the file "%s".%s', $filepath, PHP_EOL);
}

function updateHostname()
{
    $virtualHost = getenv('VIRTUAL_HOST');
    if ($virtualHost !== false && $virtualHost !== '127.0.0.1') {
        return $virtualHost;
    }
    return false;
}

$hostname = updateHostname();
if ($hostname !== false) {
    $dbUpdater->run(sprintf("UPDATE %ssetting SET `value`='http://%s/' WHERE `key`='config_url';", DB_PREFIX, $hostname));

    $frontendReplacements = array(
        "define('HTTP_SERVER', " => sprintf("define('HTTP_SERVER', 'http://%s/');%s", $hostname, PHP_EOL),
        "define('HTTP_ADMIN', " => sprintf("define('HTTP_ADMIN', 'http://%s/admin/');%s", $hostname, PHP_EOL),
        "define('HTTPS_SERVER', " => sprintf("define('HTTPS_SERVER', 'https://%s/');%s", $hostname, PHP_EOL),
    );
    replaceFileContents(__DIR__ . '/html/config.php', $frontendReplacements);

    $backendReplacements = array(
        "define('HTTP_SERVER', " => sprintf("define('HTTP_SERVER', 'http://%s/admin/');%s", $hostname, PHP_EOL),
        "define('HTTP_CATALOG', " => sprintf("define('HTTP_CATALOG', 'http://%s/');%s", $hostname, PHP_EOL),
        "define('HTTPS_SERVER', " => sprintf("define('HTTPS_SERVER', 'https://%s/admin/');%s", $hostname, PHP_EOL),
        "define('HTTPS_CATALOG', " => sprintf("define('HTTPS_CATALOG', 'https://%s/');%s", $hostname, PHP_EOL),
    );
    replaceFileContents(__DIR__ . '/html/admin/config.php', $backendReplacements);
}

$adminUser = getenv('SHOP_ADMIN_USER');
if ($adminUser !== false) {
    $dbUpdater->run(sprintf("UPDATE %suser SET `username`='%s' WHERE `user_id`='1';",
        DB_PREFIX, $adminUser));
}

$adminPassword = getenv('SHOP_ADMIN_PASSWORD');
if ($adminPassword !== false) {
    $salt = substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 9);
    $dbUpdater->run(sprintf("UPDATE %suser SET `salt`='%s', `password`='%s' WHERE `user_id`='1';",
        DB_PREFIX, $salt, sha1($salt . sha1($salt . sha1($adminPassword)))));
}

$useSsl = getenv('SHOP_USE_SSL');
if ($useSsl !== false) {
    $dbUpdater->run(sprintf("UPDATE %ssetting SET `value`='%s' WHERE `key`='config_secure';",
        DB_PREFIX, $useSsl));

    $siteSsl = ($useSsl == 1) ? 'true' : 'false';
    $sslReplacements = array(
        "\$_['site_ssl']" => sprintf("\$_['site_ssl']          = %s;%s", $siteSsl, PHP_EOL),
    );
    replaceFileContents(__DIR__ . '/html/system/config/admin.php', $sslReplacements);
    replaceFileContents(__DIR__ . '/html/system/config/catalog.php', $sslReplacements);
}
