<?php
include 'includes/_db.php';
require_once 'includes/_functions.php';

session_start();
generateToken();
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
        <!-- fixed -->
        <ul class="notif-wrapper"></ul>

        <?php
        if (isset($_SESSION['notif'])) {
            echo '<div class="notif"><p>🥳 ' . $_SESSION['notif'] . '</p></div>';
            unset($_SESSION['notif']);
        }
        if (isset($_SESSION['error'])) {
            echo '<div class="error"><p>😤 ' . $_SESSION['error'] . '</p></div>';
            unset($_SESSION['error']);
        }
        ?>

        <ul class="task-list">

            <?php
            $displayTasks = $dbCo->prepare("SELECT id_task, name, description, reminder FROM task WHERE done = 0 ORDER BY order_ DESC;");
            $displayTasks->execute();
            $tasks = $displayTasks->fetchAll();

            foreach ($tasks as $task) {
                $isEditOk = isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id']) && intval($_GET['id']) === intval($task['id_task']);
                $isRemindOk = isset($_GET['action']) && $_GET['action'] === 'remind' && isset($_GET['id']) && $_GET['id'] === $task['id_task'];
                // $task['creation_date'] = substr($task['creation_date'], 0, -9);
            ?>

                <li data-id-task="<?= $task['id_task'] ?>" class="task">
                    <div class="task-label">
                        <!-- <a data-id="<?= $task['id_task'] ?>" class="done-icon js-done-btn" href="api.php?token=<?= $_SESSION['token'] ?>&action=done&id=<?= $task['id_task'] ?>">✅</a> -->
                        <button class="done-icon js-done-btn" type="button">✅</button>

                        <?php if ($isEditOk) { ?>

                            <form action="action.php" method="POST">
                                <input class="text-input" type="text" name="task-title" value="<?= $task['name'] ?>">
                                <input class="text-input" type="textarea" name="description" value="<?= $task['description'] ?>">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
                                <input type="hidden" name="id" value="<?= $task['id_task'] ?>">
                                <input class="submit-input" type="submit" value="📝">
                            </form>

                        <?php } else { ?>

                            <h2 class="task-title"><?= $task['name'] ?></h2>

                            <?php if (!$isRemindOk) { ?>

                                <div class="reminder">
                                    <p class="reminder-date"><?= $task['reminder'] ?></p>
                                    <a class="reminder-icon" href="index.php?token=<?= $_SESSION['token'] ?>&action=remind&id=<?= $task['id_task'] ?>">⏰</a>
                                </div>

                            <?php } else { ?>

                                <form action="action.php" method="POST">
                                    <input class="date-input" type="date" name="reminder-date" value="<?= $task['reminder'] ?>">
                                    <input type="hidden" name="action" value="remind">
                                    <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
                                    <input type="hidden" name="id" value="<?= $task['id_task'] ?>">
                                    <input class="submit-input" type="submit" value="⏰">
                                </form>

                            <?php } ?>

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
                <label class="form-label">Wanna add a task?
                    <input class="text-input" type="text" name="task-title" placeholder="Your task name">
                    <input class="text-input" type="textarea" name="description" placeholder="Any details?">
                    <input type="hidden" name="action" value="add">
                    <input id="token-field" type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
                </label>
                <input class="submit-input" type="submit" value="👍">
            </form>
        </div>
    </div>
    <script src="script.js"></script>
</body>

</html>