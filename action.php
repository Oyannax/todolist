<?php
require_once 'vendor/autoload.php';
require_once 'includes/_functions.php';
include 'includes/_db.php';

if (!isset($_REQUEST['action'])) addErrorAndExit('No action.');

session_start();
checkCSRF('index.php');
checkXSS($_REQUEST);

// Add a task
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_REQUEST['action'] === 'add') {

    if (strlen($_REQUEST['task-title']) <= 0) addErrorAndExit('Name your task.');

    $today = (new DateTime())->setTimezone(new DateTimeZone('Europe/Paris'));

    $getOrder = $dbCo->prepare("SELECT COUNT(order_) FROM task WHERE done = 0;");
    $getOrder->execute();

    $addTask = $dbCo->prepare("INSERT INTO task (name, description, creation_date, done, order_) VALUES (:name, :description, :today, 0, :order);");
    $isAddOk = $addTask->execute([
        'name' => $_REQUEST['task-title'],
        'description' => $_REQUEST['description'],
        'today' => $today->format('Y-m-d H:i:s'),
        'order' => $getOrder->fetchColumn() + 1
    ]);

    if (!$isAddOk || $addTask->rowCount() !== 1) addErrorAndExit('Your task could not be created...');

    addNotif('Your task has been created!');
}

// Declare a task as done
else if ($_REQUEST['action'] === 'done' && isset($_REQUEST['id'])) {
        $id = intval($_REQUEST['id']);

        if (empty($id)) addErrorAndExit('Unable to target task.');
            $getId = $dbCo->prepare("UPDATE task SET done = 1 WHERE id_task = :id;");
            $isGetOk = $getId->execute([
                'id' => $id
            ]);

            if ($isGetOk && $getId->rowCount() === 1) {
                $_SESSION['notif'] = 'Your task is done, good job!';
            } else {
                $_SESSION['error'] = 'Your task could not be done.';
            }
        
    // } else {
    //     $_SESSION['error'] = 'Invalid token.';
    // }
    // Delete a task
} else if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {

    if (isset($_SESSION['token']) && isset($_GET['token']) && $_SESSION['token'] === $_GET['token']) {
        $id = intval(strip_tags($_GET['id']));

        if (!empty($id)) {
            $query1 = $dbCo->prepare("SELECT id_task FROM task WHERE done = 0 AND order_ > (SELECT order_ FROM task WHERE id_task = :id);");
            $query1->execute([
                'id' => $id
            ]);
            $tasks = $query1->fetchAll();

            foreach ($tasks as $task) {
                $query2 = $dbCo->prepare("UPDATE task SET order_ = order_ - 1 WHERE id_task = :task;");
                $query2->execute([
                    'task' => intval($task['id_task'])
                ]);
            }

            $getId = $dbCo->prepare("DELETE FROM task WHERE id_task = :id;");
            $isGetOk = $getId->execute([
                'id' => $id
            ]);

            if ($isGetOk && $getId->rowCount() === 1) {
                $_SESSION['notif'] = 'Your task has been deleted.';
            } else {
                $_SESSION['error'] = 'Your task could not be deleted.';
            }
        } else {
            $_SESSION['error'] = 'Unable to target task.';
        }
    } else {
        $_SESSION['error'] = 'Invalid token.';
    }
    // Edit a task
} else if (isset($_POST['action']) && $_POST['action'] === 'edit' && isset($_POST['task-title']) && isset($_POST['id'])) {

    if (isset($_SESSION['token']) && isset($_POST['token']) && $_SESSION['token'] === $_POST['token']) {
        $name = strip_tags($_POST['task-title']);
        $description = strip_tags($_POST['description']);
        $id = intval(strip_tags($_POST['id']));

        if (strlen($name) > 0) {

            if (!empty($id)) {
                $editTask = $dbCo->prepare("UPDATE task SET name = :name, description = :description WHERE id_task = :id;");
                $isEditOk = $editTask->execute([
                    'name' => $name,
                    'description' => $description,
                    'id' => $id
                ]);

                if ($isEditOk && $editTask->rowCount() === 1) {
                    $_SESSION['notif'] = 'Your task has been modified!';
                } else {
                    $_SESSION['error'] = 'Your task could not be modified...';
                }
            } else {
                $_SESSION['error'] = 'Unable to target task.';
            }
        } else {
            $_SESSION['error'] = 'Name your task.';
        }
    } else {
        $_SESSION['error'] = 'Invalid token.';
    }
    // Move a task up
} else if (isset($_GET['action']) && $_GET['action'] === 'up' && isset($_GET['id'])) {

    if (isset($_SESSION['token']) && isset($_GET['token']) && $_SESSION['token'] === $_GET['token']) {
        $id1 = intval(strip_tags($_GET['id']));

        if (!empty($id1)) {
            $query1 = $dbCo->prepare("SELECT order_ FROM task WHERE id_task = :id1 AND order_ <> (SELECT MAX(order_) FROM task);");
            $isQueryOk = $query1->execute([
                'id1' => $id1
            ]);

            if ($isQueryOk && $query1->rowCount() === 1) {
                $order1 = $query1->fetchColumn() + 1;

                $editOrder1 = $dbCo->prepare("UPDATE task SET order_ = :order1 WHERE id_task = :id1;");
                $isEditOk1 = $editOrder1->execute([
                    'order1' => $order1,
                    'id1' => $id1
                ]);

                if ($isEditOk1 && $editOrder1->rowCount() === 1) {
                    $query2 = $dbCo->prepare("SELECT id_task FROM task WHERE done = 0 AND id_task <> :id1 AND order_ = (SELECT order_ FROM task WHERE id_task = :id1);");
                    $query2->execute([
                        'id1' => $id1
                    ]);
                    $id2 = $query2->fetchColumn();

                    $editOrder2 = $dbCo->prepare("UPDATE task SET order_ = order_ - 1 WHERE id_task = :id2;");
                    $isEditOk2 = $editOrder2->execute([
                        'id2' => $id2
                    ]);

                    if ($isEditOk2 && $editOrder2->rowCount() === 1) {
                        $_SESSION['notif'] = 'Your task has been moved up!';
                    } else {
                        $_SESSION['error'] = 'The other tasks could not be moved.';
                    }
                } else {
                    $_SESSION['error'] = 'Your task could not be moved up...';
                }
            } else {
                $_SESSION['error'] = 'This task is already at the top!';
            }
        } else {
            $_SESSION['error'] = 'Unable to target task.';
        }
    } else {
        $_SESSION['error'] = 'Invalid token.';
    }
    // Move a task down
} else if (isset($_GET['action']) && $_GET['action'] === 'down' && isset($_GET['id'])) {

    if (isset($_SESSION['token']) && isset($_GET['token']) && $_SESSION['token'] === $_GET['token']) {
        $id1 = intval(strip_tags($_GET['id']));

        if (!empty($id1)) {
            $query1 = $dbCo->prepare("SELECT order_ FROM task WHERE id_task = :id1 AND order_ <> (SELECT MIN(order_) FROM task);");
            $isQueryOk = $query1->execute([
                'id1' => $id1
            ]);

            if ($isQueryOk && $query1->rowCount() === 1) {
                $order1 = $query1->fetchColumn() - 1;

                $editOrder1 = $dbCo->prepare("UPDATE task SET order_ = :order1 WHERE id_task = :id1;");
                $isEditOk1 = $editOrder1->execute([
                    'order1' => $order1,
                    'id1' => $id1
                ]);

                if ($isEditOk1 && $editOrder1->rowCount() === 1) {
                    $query2 = $dbCo->prepare("SELECT id_task FROM task WHERE done = 0 AND id_task <> :id1 AND order_ = (SELECT order_ FROM task WHERE id_task = :id1);");
                    $query2->execute([
                        'id1' => $id1
                    ]);
                    $id2 = $query2->fetchColumn();

                    $editOrder2 = $dbCo->prepare("UPDATE task SET order_ = order_ + 1 WHERE id_task = :id2;");
                    $isEditOk2 = $editOrder2->execute([
                        'id2' => $id2
                    ]);

                    if ($isEditOk2 && $editOrder2->rowCount() === 1) {
                        $_SESSION['notif'] = 'Your task has been moved down!';
                    } else {
                        $_SESSION['error'] = 'The other tasks could not be moved.';
                    }
                } else {
                    $_SESSION['error'] = 'Your task could not be moved down...';
                }
            } else {
                $_SESSION['error'] = 'This task is already at the bottom!';
            }
        } else {
            $_SESSION['error'] = 'Unable to target task.';
        }
    } else {
        $_SESSION['error'] = 'Invalid token.';
    }
    // Set a reminder
} else if (isset($_POST['action']) && $_POST['action'] === 'remind' && isset($_POST['reminder-date']) && isset($_POST['id'])) {

    if (isset($_SESSION['token']) && isset($_POST['token']) && $_SESSION['token'] === $_POST['token']) {
        $reminder = strip_tags($_POST['reminder-date']);
        $id = intval(strip_tags($_POST['id']));
        // var_dump($reminder);

        if ($reminder !== '') {

            if (!empty($id)) {
                $editTask = $dbCo->prepare("UPDATE task SET reminder = :reminder WHERE id_task = :id;");
                $isEditOk = $editTask->execute([
                    'reminder' => $reminder,
                    'id' => $id
                ]);

                if ($isEditOk && $editTask->rowCount() === 1) {
                    $_SESSION['notif'] = 'Your reminder date has been set.';
                } else {
                    $_SESSION['error'] = 'Your reminder date could not be set.';
                }
            } else {
                $_SESSION['error'] = 'Unable to target task.';
            }
        } else {
            $_SESSION['error'] = 'Choose a date.';
        }
    } else {
        $_SESSION['error'] = 'Invalid token.';
    }
}

header('Location: index.php');
