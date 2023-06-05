<?php

require 'db.php';

//pokud je uživatel již přihlášený
$user = NULL;
// pokud je dofeinované $_SESSION['user_id'], uživatel je přihlášený
if(isset($_SESSION['user_id'])){
    $user_id = $_SESSION['user_id'];
    $user = $db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1;");
    $user->execute([$user_id]);
    $user = $user->fetchAll(PDO::FETCH_ASSOC);

}

?>

<nav class="navbar navbar-expand-lg navbar-light bg-light" style="padding-bottom: 10px; margin-bottom: 10px;">
    <style>
    html {
    position: relative;
    min-height: 100%;
    }

    body {
    margin-bottom: 60px; /* Adjust this value to match the height of your footer */
    }

    .footer {
    position: absolute;
    bottom: 0;
    width: 100%;
    height: 60px; /* Adjust this value to match the desired height of your footer */
    background-color: #f8f9fa; /* Change the background color to your preferred color */
    padding: 20px;
    text-align: center;
    }
    </style>

    <a class="navbar-brand" href="index.php">Home</a>

    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="add_event.php">Create an event</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">My Events</a>
            </li>
            <?php if($user) {?>
            <li class="nav-item">
                <a class="nav-link" href="#"><?php echo $user[0]["name"];?></a>
            </li>
            <li class="nav-item">
                <a class="btn btn-primary" href="signout.php">Log Out</a>
            </li>
            <?php } else {?>
            <li class="nav-item">
                <a class="nav-link" href="signup.php">Sign Up</a>
            </li>
            <li class="nav-item">
                <a class="btn btn-primary" href="signin.php">Log In</a>
            </li>
            <?php }?>
        </ul>
    </div>
</nav>
