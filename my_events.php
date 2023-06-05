<?php
//spustíme session
session_start();

require 'db.php';

$user_id = $_SESSION['user_id'];

$events = null;
if($user_id){
    //získám všechny eventy z databáze
    $events = $db->prepare("SELECT * FROM event WHERE organiser = ?;");
    $events->execute([$user_id]);
    $events = $events->fetchAll(PDO::FETCH_ASSOC); //
}


?>

<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events Page</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <style>
        .card {
            margin-bottom: 20px;
        }
    </style>

</head>

<body>
<?php include 'navbar.php'; ?>

<div class="container">
    <h1>Upcoming Events</h1>
    <div class="row">

        <?php
        if($events != null){
            foreach ($events as $event){ ?>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $event['name']; ?></h5>
                        <p class="card-text">Date: <?php echo $event['datetime']; ?></p>
                        <p class="card-text"><?php echo $event['description']; ?></p>
                        <a href="event.php?id=<?php echo $event['id'] ?>" class="btn btn-primary">Learn More</a>
                    </div>
                </div>
            </div>
        <?php } } else { ?>
        <div class="container">
            <div class="alert alert-warning" role="alert">
                You didn't create any events.
            </div>
        </div>
        <?php } ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <span>&copy; 2023 Martin Aschermann. All rights reserved.</span>
    </div>
</footer>


<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
