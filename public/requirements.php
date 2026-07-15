<?php

function checkServerRequirements(): array {
    $requirements = [];

    // PHP version
    $requirements['PHP >= 8.3'] = version_compare(PHP_VERSION, '8.3.0', '>=');

    // MySQL version
    $mysqlVersion = null;
    if (function_exists('mysqli_get_client_version')) {
        $mysqlVersion = mysqli_get_client_version();
        if (strpos($mysqlVersion, '.') === false && is_numeric($mysqlVersion)) {
            // Convert raw int version to dotted version
            $mysqlVersion = sprintf(
                '%d.%d.%d',
                floor($mysqlVersion / 10000),
                floor(($mysqlVersion % 10000) / 100),
                $mysqlVersion % 100
            );
        }
    }
    $requirements['MySQL >= 5.6'] = $mysqlVersion ? version_compare($mysqlVersion, '5.6.0', '>=') : false;

    // PHP extensions (required)
    $extensions = [
        'OpenSSL'   => 'openssl',
        'PDO'       => 'pdo',
        'Mbstring'  => 'mbstring',
        'Tokenizer' => 'tokenizer',
        'XML'       => 'xml',
        'Ctype'     => 'ctype',
        'JSON'      => 'json',
        'BCMath'    => 'bcmath',
        'iconv'     => 'iconv',
        'ZIP'       => 'zip',
        'Fileinfo'  => 'fileinfo',
    ];

    foreach ($extensions as $label => $ext) {
        $requirements["PHP Extension: $label"] = extension_loaded($ext);
    }

    // Optional extensions (warnings, not blockers)
    $optional = [
        'IMAP (needed for IMAP engine)' => 'imap',
    ];

    foreach ($optional as $label => $ext) {
        $requirements["Optional: $label"] = extension_loaded($ext);
    }

    // allow_url_fopen
    $requirements['allow_url_fopen = ON'] = ini_get('allow_url_fopen') == '1';

    // symlink function
    $requirements['symlink() function enabled'] = function_exists('symlink') && is_callable('symlink');

    return $requirements;
}

$requirements = checkServerRequirements();
$passed = true;
$hasOptionalWarning = false;
foreach ($requirements as $requirement => $value) {
    if ($value == false) {
        if (strpos($requirement, 'Optional:') === 0) {
            $hasOptionalWarning = true;
        } else {
            $passed = false;
        }
    }
}
if ($passed) {
    return '';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TMail Installer</title>
    <link rel="shortcut icon" href="/images/icon.png" type="image/png" />
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net" />
    <link href="https://fonts.bunny.net/css?family=poppins:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://use.hugeicons.com/font/icons.css" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        html,
        body {
            font-family: "Poppins", sans-serif;
        }
    </style>
</head>

<body class="dark:bg-gray-900">
    <main class="py-12">
        <div class="max-w-lg mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 overflow-hidden rounded-2xl">
                <div class="pt-12">
                    <img class="m-auto max-w-48 dark:hidden" src="images/installer-logo-light.png" alt="logo" />
                    <img class="m-auto max-w-48 hidden dark:block" src="images/installer-logo-dark.png" alt="logo" />
                </div>
                <div class="p-10 flex flex-col gap-5">
                    <p class="text-center text-xl font-bold dark:text-white">Requirements</p>
                    <div>
                        <?php foreach ($requirements as $requirement => $value) { ?>
                            <div class="flex items-center justify-between py-2 px-4 rounded hover:bg-gray-200 dark:hover:bg-gray-700">
                                <div class="dark:text-gray-50"><?php echo $requirement ?></div>
                                <?php if ($value) { ?>
                                    <i class="bg-green-500 text-white rounded-full hgi hgi-stroke hgi-checkmark-circle-02"></i>
                                <?php } elseif (strpos($requirement, 'Optional:') === 0) { ?>
                                    <i class="bg-yellow-500 text-white rounded-full hgi hgi-stroke hgi-alert-circle"></i>
                                <?php } else { ?>
                                    <i class="bg-red-500 text-white rounded-full hgi hgi-stroke hgi-cancel-circle"></i>
                                <?php } ?>
                            </div>
                        <?php } ?>
                        </div>
                        <?php if ($hasOptionalWarning && $passed) { ?>
                            <p class="text-yellow-600 dark:text-yellow-400 text-sm text-center">⚠️ Some optional extensions are missing. You can continue — they are not required for basic functionality.</p>
                        <?php } ?>
                        <button onclick="location.reload()" class="bg-indigo-500 text-white rounded-lg p-2 cursor-pointer">Recheck Requirements</button>
                        <?php if ($passed) { ?>
                            <a href="/installer" class="block text-center bg-green-600 hover:bg-green-700 text-white rounded-lg p-2 font-medium transition">Continue to Installer →</a>
                        <?php } ?>
                </div>
            </div>
        </div>
    </main>
</body>

</html>
<?php
exit();
?>