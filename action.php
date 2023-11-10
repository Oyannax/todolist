<?php
session_start();
include_once 'db.php';

// Create a task
if (isset($_GET['action'])) {

    if ($_GET['action'] === 'add') {
        if (isset($_POST['task-title'])) {
        
            // Check if the session token is the same as the form token
            if (isset($_SESSION['token']) && isset($_POST['token']) && $_SESSION['token'] === $_POST['token']) {
                $name = strip_tags($_POST['task-title']);
                $description = strip_tags($_POST['description']);
                $today = new DateTime();
                $today->setTimezone(new DateTimeZone('Europe/Paris'));
                $todayDate = $today->format('Y-m-d H:i:s');
        
                if (strlen($name) > 0) {
                    $addTask = $dbCo->prepare("INSERT INTO task (name, description, creation_date, done) VALUES (:name, :description, :todayDate, '0');");
                    $IsAddOk = $addTask->execute([
                        'name' => $name,
                        'description' => $description,
                        'todayDate' => $todayDate
                    ]);
        
                    if ($IsAddOk && $addTask->rowCount() === 1) {
                        $msg['notif'] = 'Your task has been created!';
                    } else {
                        $msg['error'] = 'Your task could not be created...';
                    }
                } else {
                    $msg['error'] = 'Name your task.';
                }
            } else {
                $msh['error'] = 'Invalid token.';
            }
        }
    }
}

// Declare a task as done
if (isset($_GET['action'])) {

    if ($_GET['action'] === 'done') {

        if (isset($_GET['id'])) {

            if (isset($_SESSION['token']) && isset($_GET['token']) && $_SESSION['token'] === $_GET['token']) {
                $id = intval(strip_tags($_GET['id']));

                if (!empty($id)) {
                    $getId = $dbCo->prepare("UPDATE task SET done = 1 WHERE id_task = :id;");
                    $IsGetOk = $getId->execute([
                        'id' => $id
                    ]);

                    if ($IsGetOk && $getId->rowCount() === 1) {
                        $msg['notif'] = 'Your task is done, good job!';
                    } else {
                        $msg['error'] = 'Your task could not be done.';
                    }
                } else {
                    $msg['error'] = 'Unable to target task.';
                }
            } else {
                $msg['error'] = 'Invalid token.';
            }
        }
    }
}

header('Location: index.php?'.http_build_query($msg));