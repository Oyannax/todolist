<?php
session_start();
include 'includes/_db.php';

if (!isset($_SESSION['token']) || time() > $_SESSION['tokenExpiry']) {
    $_SESSION['token'] = md5(uniqid(mt_rand(), true));
    $_SESSION['tokenExpiry'] = time() + 15 * 60;
}
// var_dump($_SESSION);
// var_dump($_POST['token']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>My To-Do List</title>
</head>

<body class="light-template">
    <div class="container">
        <header class="header">
            <h1 class="main-title">My To-Do List</h1>
        </header>

        <?php
        if (isset($_GET['notif'])) {
            echo '<div class="notif"><p class="notif-msg">'.$_GET['notif'].' 🥳</p></div>';
        }
        if (isset($_GET['error'])) {
            echo '<div class="error"><p class="error-msg">'.$_GET['error'].' 😤</p></div>';
        }
        ?>

        <ul class="task-list">
            <?php
            $displayTasks = $dbCo->prepare("SELECT id_task, name, description, creation_date FROM task WHERE done = 0 ORDER BY creation_date DESC;");
            $displayTasks->execute();
            $tasks = $displayTasks->fetchAll();

            foreach ($tasks as $task) {
                $task['creation_date'] = substr($task['creation_date'], 0, -9);
                echo '
            <li class="task">
                <div class="task-label">
                    <a class="done-icon" href="action.php?token='.$_SESSION['token'].'&action=done&id='.$task['id_task'].'">✅</a>
                    <h2 class="task-title">'.$task['name'].'</h2>
                    <p class="creation-date">'.$task['creation_date'].'</p>
                </div>
                <div class="task-details">
                    <p class="description">'.$task['description'].'</p>
                    <a class="delete-icon" href="action.php?token='.$_SESSION['token'].'&action=delete&id='.$task['id_task'].'">❌</a>
                    <a class="modify-icon" href="action.php?token='.$_SESSION['token'].'&action=modify&id='.$task['id_task'].'">📝</a>
                    <a class="up-icon" href="action.php?token='.$_SESSION['token'].'&action=up&id='.$task['id_task'].'">👆</a>
                    <a class="down-icon" href="action.php?token='.$_SESSION['token'].'&action=down&id='.$task['id_task'].'">👇</a>
                </div>
            </li>';
            }
            ?>
        </ul>

        <div class="form">
            <form action="action.php?action=add" method="POST">
                <label>Wanna add a task?
                    <input type="text" name="task-title" placeholder="Your task name">
                    <input type="textarea" name="description" placeholder="Any details?">
                    <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
                </label>
                <input type="submit" value="Add">
            </form>
        </div>
    </div>
</body>
</html>