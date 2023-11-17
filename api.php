<?php
header('content-type:application/json');

if (!isset($_GET['action'])) {
    echo json_encode([
        'result' => false,
        'error' => 'No action.'
    ]);
    exit;
}

include 'includes/_db.php';
require_once 'includes/_functions.php';
session_start();

// checkCSRFAsync()

if (isset($_GET['action']) && $_GET['action'] === 'done' && isset($_GET['id'])) {

    if (isset($_SESSION['token']) && isset($_GET['token']) && $_SESSION['token'] === $_GET['token']) {
        $id = intval(strip_tags($_GET['id']));

        if (!empty($id)) {
            $getId = $dbCo->prepare("UPDATE task SET done = 1 WHERE id_task = :id;");
            $isGetOk = $getId->execute([
                'id' => $id
            ]);

            if ($isGetOk && $getId->rowCount() === 1) {
                $_SESSION['notif'] = 'Your task is done, good job!';
            } else {
                $_SESSION['error'] = 'Your task could not be done.';
            }
        } else {
            $_SESSION['error'] = 'Unable to target task.';
        }
    } else {
        $_SESSION['error'] = 'Invalid token.';
    }
}
