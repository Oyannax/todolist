<?php

/**
 * Generate a valid token in $_SESSION
 *
 * @return void
 */
function generateToken(): void
{
    if (!isset($_SESSION['token']) || time() > $_SESSION['tokenExpiry']) {
        $_SESSION['token'] = md5(uniqid(mt_rand(), true));
        $_SESSION['tokenExpiry'] = time() + 15 * 60;
    }
}

/**
 * Check for CSRF with referer and token
 * Redirect to the given page in case of error
 *
 * @param string $url - The page to redirect
 * @return void
 */
function checkCSRF(string $url): void
{
    if (!isset($_SERVER['HTTP_REFERER']) || !str_contains($_SERVER['HTTP_REFERER'], 'http://localhost/todolist')) {
        $_SESSION['error'] = 'error_referer';
    } else if (!isset($_SESSION['token']) || !isset($_REQUEST['token']) || $_SESSION['token'] !== $_REQUEST['token'] || time() > $_SESSION['tokenExpiry']) {
        $_SESSION['error'] = 'error_token';
    }

    if (!isset($_SESSION['error'])) return;

    header('Location: ' . $url);
    exit;
}

/**
 * Check for CSRF with referer and token
 *
 * @return void
 */
function checkCSRFAsync(): void
{
    if (!isset($_SERVER['HTTP_REFERER']) || !str_contains($_SERVER['HTTP_REFERER'], 'http://localhost/todolist')) {
        $error = 'error_referer';
    } else if (!isset($_SESSION['token']) || !isset($_REQUEST['token']) || $_SESSION['token'] !== $_REQUEST['token'] || time() > $_SESSION['tokenExpiry']) {
        $error = 'error_token';
    }

    if (!isset($error)) return;

    echo json_encode([
        'result' => false,
        'error' => $error
    ]);
    exit;
}

/**
 * Apply treatment on given array to prevent XSS fault
 *
 * @param array $array
 * @return void
 */
function checkXSS(array &$array): void
{
    $array = array_map('strip_tags', $array);
    // foreach ($array as $key => $value) {
    //     $array[$key] = strip_tags($value);
    // }
}


/**
 * Add an error to display and stop script
 *
 * @param string $error
 * @return void
 */
function addErrorAndExit(string $error): void
{
    $_SESSION['error'] = $error;

    header('Location: index.php');
    exit;
}

/**
 * Add a notification to display
 *
 * @param string $notif
 * @return void
 */
function addNotif(string $notif): void
{
    $_SESSION['notif'] = $notif;
}
