<?php
session_start();
include 'includes/_db.php';

// function checkCSRF(string $url): void {
//     if (!isset($_SERVER['HTTP_REFERER']) || !str_contains($_SERVER['HTTP_REFERER'], 'http://localhost/todolist/')) {

//     }
// }

if (!isset($_SESSION['token']) || time() > $_SESSION['tokenExpiry']) {
    $_SESSION['token'] = md5(uniqid(mt_rand(), true));
    $_SESSION['tokenExpiry'] = time() + 15 * 60;
}
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
        if (isset($_SESSION['notif'])) {
            echo '<div class="notif"><p class="notif-msg">🥳 '.$_SESSION['notif'].'</p></div>';
            unset($_SESSION['notif']);
        }
        if (isset($_SESSION['error'])) {
            echo '<div class="error"><p class="error-msg">😤 '.$_SESSION['error'].'</p></div>';
            unset($_SESSION['error']);
        }
        ?>

        <ul class="task-list">

            <?php
            $displayTasks = $dbCo->prepare("SELECT id_task, name, description, creation_date FROM task WHERE done = 0 ORDER BY order_ DESC;");
            $displayTasks->execute();
            $tasks = $displayTasks->fetchAll();

            foreach ($tasks as $task) {
                $isEditOk = isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id']) && $_GET['id'] === $task['id_task'];
                $task['creation_date'] = substr($task['creation_date'], 0, -9);
            ?>

            <li class="task">
                <div class="task-label">
                    <a class="done-icon" href="action.php?token=<?= $_SESSION['token'] ?>&action=done&id=<?= $task['id_task'] ?>">✅</a>

                <?php if ($isEditOk) { ?>

                    <form action="action.php" method="POST">
                        <input type="text" name="task-title" value="<?= $task['name'] ?>">
                        <input type="textarea" name="description" value="<?= $task['description'] ?>">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
                        <input type="hidden" name="id" value="<?= $task['id_task'] ?>">
                        <input type="submit" value="📝">
                    </form>

                <?php } else { ?>

                    <h2 class="task-title"><?= $task['name'] ?></h2>
                    <p class="creation-date"><?= $task['creation_date'] ?></p>
                </div>
                <div class="task-details">
                    <p class="description"><?= $task['description'] ?></p>

                <?php } ?>

                    <div class="sub-icons">

                <?php if (!$isEditOk) { ?>

                    <a class="sub-icon" href="index.php?token=<?= $_SESSION['token'] ?>&action=edit&id=<?= $task['id_task'] ?>">📝</a>

                <?php } ?>

                        <a class="sub-icon" href="action.php?token=<?= $_SESSION['token'] ?>&action=delete&id=<?= $task['id_task'] ?>">❌</a>
                        <a class="sub-icon" href="action.php?token=<?= $_SESSION['token'] ?>&action=up&id=<?= $task['id_task'] ?>">👆</a>
                        <a class="sub-icon" href="action.php?token=<?= $_SESSION['token'] ?>&action=down&id=<?= $task['id_task'] ?>">👇</a>
                    </div>
                </div>
            </li>

            <?php } ?>

        </ul>

        <div class="form">
            <form action="action.php" method="POST">
                <label>Wanna add a task?
                    <input type="text" name="task-title" placeholder="Your task name">
                    <input type="textarea" name="description" placeholder="Any details?">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
                </label>
                <input type="submit" value="👍">
            </form>
        </div>
    </div>
</body>
</html>