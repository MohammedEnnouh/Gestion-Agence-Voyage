<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$isCli = (php_sapi_name() === 'cli');

function output($message, $type = 'info') {
    global $isCli;

    $colors = [
        'success' => '32',
        'error'   => '31',
        'warning' => '33',
        'info'    => '36',
        'default' => '0',
    ];

    $color = $colors[$type] ?? $colors['default'];

    if ($isCli) {
        echo "\033[{$color}m{$message}\033[0m\n";
    } else {
        $class = match($type) {
            'success' => 'text-success',
            'error'   => 'text-danger',
            'warning' => 'text-warning',
            'info'    => 'text-info',
            default   => 'text-muted',
        };
        echo "<div class='{$class}'>{$message}</div>\n";
    }
}

function headerOutput($title) {
    global $isCli;

    if ($isCli) {
        output("\n=== {$title} ===\n", 'info');
    } else {
        echo "<h2 class='mt-4 mb-3'>{$title}</h2>\n";
    }
}

if (!$isCli) {
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Vogie - Setup</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body { padding: 20px; }
            .container { max-width: 800px; margin: 0 auto; }
            pre { background:
            .step { margin-bottom: 30px; }
        </style>
    </head>
    <body>
    <div class="container">
        <h1 class="mb-4">Vogie Web Application Setup</h1>
    ';
}

headerOutput('1. Checking Requirements');
$phpVersion = phpversion();
$phpMinVersion = '7.4.0';

if (version_compare($phpVersion, $phpMinVersion, '>=')) {
    output("✓ PHP {$phpVersion} is installed (Minimum required: {$phpMinVersion})", 'success');
} else {
    output("✗ PHP version must be {$phpMinVersion} or higher. Current version: {$phpVersion}", 'error');
    exit(1);
}

$requiredExtensions = ['pdo_mysql', 'mbstring', 'json', 'openssl', 'gd', 'fileinfo'];
$missingExtensions = [];

foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        output("✓ {$ext} extension is installed", 'success');
    } else {
        $missingExtensions[] = $ext;
        output("✗ {$ext} extension is not installed", 'error');
    }
}

if (!empty($missingExtensions)) {
    output("\nPlease install the missing PHP extensions and try again.", 'error');
    if (!$isCli) {
        echo "</div></body></html>";
    }
    exit(1);
}

headerOutput('2. Configuration');
$envFile = __DIR__ . '/.env';
$envExampleFile = __DIR__ . '/.env.example';

if (file_exists($envFile)) {
    output("✓ .env file exists", 'success');

    if (is_writable($envFile)) {
        output("✓ .env file is writable", 'success');
    } else {
        output("✗ .env file is not writable. Please fix the permissions.", 'error');
        if (!$isCli) {
            echo "</div></body></html>";
        }
        exit(1);
    }
} else {

    if (file_exists($envExampleFile)) {
        if (copy($envExampleFile, $envFile)) {
            output("✓ Created .env file from .env.example", 'success');
        } else {
            output("✗ Failed to create .env file. Please create it manually from .env.example", 'error');
            if (!$isCli) {
                echo "</div></body></html>";
            }
            exit(1);
        }
    } else {
        output("✗ .env.example file not found. Please check your installation.", 'error');
        if (!$isCli) {
            echo "</div></body></html>";
        }
        exit(1);
    }
}

$envContent = file_get_contents($envFile);
$envVars = [];
$requiredVars = [
    'DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD',
    'STRIPE_KEY', 'STRIPE_SECRET', 'APP_URL', 'APP_KEY'
];

$lines = explode("\n", $envContent);
foreach ($lines as $line) {
    $line = trim($line);
    if (!empty($line) && strpos($line, '=') !== false && substr($line, 0, 1) !== '#') {
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        $envVars[$key] = $value;
    }
}

if (empty($envVars['APP_KEY']) || $envVars['APP_KEY'] === 'YOUR_APP_KEY_HERE') {
    $newKey = 'base64:' . base64_encode(random_bytes(32));
    $envContent = preg_replace(
        '/^APP_KEY=.*/m',
        'APP_KEY=' . $newKey,
        $envContent
    );
    file_put_contents($envFile, $envContent);
    output("✓ Generated new application key", 'success');
    $envVars['APP_KEY'] = $newKey;
}

headerOutput('3. Database Connection');
try {
    $dsn = "mysql:host={$envVars['DB_HOST']}";
    if (!empty($envVars['DB_PORT'])) {
        $dsn .= ";port={$envVars['DB_PORT']}";
    }

    $pdo = new PDO($dsn, $envVars['DB_USERNAME'], $envVars['DB_PASSWORD'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'
    ]);

    output("✓ Connected to MySQL server", 'success');

    $dbName = $envVars['DB_DATABASE'];
    $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$dbName}'");

    if ($stmt->rowCount() === 0) {
        $pdo->exec("CREATE DATABASE `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        output("✓ Created database: {$dbName}", 'success');
    } else {
        output("✓ Database exists: {$dbName}", 'info');
    }

    $schemaFile = __DIR__ . '/database/vogie_web.sql';
    if (file_exists($schemaFile)) {

        $sql = file_get_contents($schemaFile);

        $queries = array_filter(
            array_map('trim',
                preg_split("/;\s*\n|;\s*$/m", $sql)
            )
        );

        $pdo->exec("USE `{$dbName}`");
        $successCount = 0;
        $errorCount = 0;

        foreach ($queries as $query) {
            if (!empty($query)) {
                try {
                    $pdo->exec($query);
                    $successCount++;
                } catch (PDOException $e) {
                    $errorCount++;
                    output("✗ Error executing query: " . $e->getMessage(), 'error');
                }
            }
        }

        if ($errorCount === 0) {
            output("✓ Successfully imported database schema ({$successCount} queries executed)", 'success');
        } else {
            output("⚠ Imported database schema with {$errorCount} errors", 'warning');
        }
    } else {
        output("✗ Database schema file not found: {$schemaFile}", 'error');
        if (!$isCli) {
            echo "</div></body></html>";
        }
        exit(1);
    }

} catch (PDOException $e) {
    output("✗ Database connection failed: " . $e->getMessage(), 'error');
    if (!$isCli) {
        echo "</div></body></html>";
    }
    exit(1);
}

headerOutput('4. Database Seeding');
$seederFile = __DIR__ . '/database/seed_database.php';

if (file_exists($seederFile)) {

    try {
        include $seederFile;
        output("✓ Database seeded successfully", 'success');
    } catch (Exception $e) {
        output("✗ Error seeding database: " . $e->getMessage(), 'error');
    }
} else {
    output("✗ Database seeder file not found: {$seederFile}", 'error');
}

headerOutput('5. Directory Permissions');
$directories = [
    'storage' => 'writable',
    'uploads' => 'writable',
    'cache' => 'writable',
    'config' => 'readable',
    'vendor' => 'readable',
];

$allPermissionsOk = true;

foreach ($directories as $dir => $permission) {
    $path = __DIR__ . '/' . $dir;

    if (!file_exists($path)) {
        if (mkdir($path, 0755, true)) {
            output("✓ Created directory: {$dir}", 'success');
        } else {
            output("✗ Failed to create directory: {$dir}", 'error');
            $allPermissionsOk = false;
            continue;
        }
    }

    $isWritable = is_writable($path);
    $isReadable = is_readable($path);

    if ($permission === 'writable') {
        if ($isWritable) {
            output("✓ Directory is writable: {$dir}", 'success');
        } else {
            output("✗ Directory is not writable: {$dir}", 'error');
            $allPermissionsOk = false;
        }
    } else {
        if ($isReadable) {
            output("✓ Directory is readable: {$dir}", 'success');
        } else {
            output("✗ Directory is not readable: {$dir}", 'error');
            $allPermissionsOk = false;
        }
    }
}

headerOutput('6. Dependencies');
$composerFile = __DIR__ . '/composer.json';
$vendorDir = __DIR__ . '/vendor';

if (file_exists($composerFile)) {
    if (!file_exists($vendorDir . '/autoload.php')) {
        output("✓ Composer dependencies not installed. Please run 'composer install'.", 'warning');
    } else {
        output("✓ Composer dependencies are installed", 'success');
    }
} else {
    output("✗ composer.json not found", 'error');
}

$packageJsonFile = __DIR__ . '/package.json';
$nodeModulesDir = __DIR__ . '/node_modules';

if (file_exists($packageJsonFile)) {
    if (!file_exists($nodeModulesDir)) {
        output("✓ Node.js dependencies not installed. Please run 'npm install'.", 'warning');
    } else {
        output("✓ Node.js dependencies are installed", 'success');
    }
}

headerOutput('Setup Complete');

if ($allPermissionsOk) {
    output("\n🎉 Setup completed successfully!", 'success');
    output("You can now access the Vogie web application.", 'info');

    if (!$isCli) {
        $appUrl = rtrim($envVars['APP_URL'] ?? 'http://localhost', '/');
        echo "<div class='mt-4 p-3 bg-light rounded'>";
        echo "<h4>Next Steps:</h4>";
        echo "<ol>";
        echo "<li>Access the <a href='{$appUrl}' target='_blank'>Vogie Web Application</a></li>";
        echo "<li>Login with the following credentials:";
        echo "<ul>";
        echo "<li><strong>Email:</strong> admin@vogie.com</li>";
        echo "<li><strong>Password:</strong> admin123</li>";
        echo "</ul>";
        echo "</li>";
        echo "<li>Change the default admin password after login</li>";
        echo "<li>Configure your Stripe keys in the .env file for payment processing</li>";
        echo "</ol>";
        echo "</div>";
    } else {
        output("\nNext steps:", 'info');
        output("1. Access the Vogie Web Application: " . ($envVars['APP_URL'] ?? 'http://localhost'));
        output("2. Login with the following credentials:");
        output("   - Email: admin@vogie.com");
        output("   - Password: admin123");
        output("3. Change the default admin password after login");
        output("4. Configure your Stripe keys in the .env file for payment processing");
    }
} else {
    output("\n⚠ Setup completed with some issues. Please check the messages above.", 'warning');
    output("Some features may not work correctly until all issues are resolved.", 'warning');
}

if (!$isCli) {
    echo '</div></body></html>';
}