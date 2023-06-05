<?php
//spustíme session
session_start();

require 'db.php';

$user_id = null;
if(isset($_SESSION['user_id'])){
    $user_id = $_SESSION['user_id'];
}


$emailOfCurrentUser = $db->prepare("SELECT email FROM users WHERE id = ? LIMIT 1;");
$emailOfCurrentUser->execute([$user_id]);
$emailOfCurrentUser = $emailOfCurrentUser->fetch(PDO::FETCH_ASSOC); //


//získám všechny eventy z databáze
$events = $db->prepare("SELECT * FROM event;");
$events->execute();
$events = $events->fetchAll(PDO::FETCH_ASSOC); //

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

          <?php foreach ($events as $event){

              $event_id = $event['id'];

              $emailsOfInvited = $db->prepare("SELECT email FROM invited WHERE event_id = ?;");
              $emailsOfInvited->execute([$event_id]);
              $emailsOfInvited = $emailsOfInvited->fetchAll(PDO::FETCH_ASSOC);

              $emailOnGuestlist = false;
              foreach ($emailsOfInvited as $emailOfInvited){
                  if($emailOfCurrentUser == $emailOfInvited){
                      $emailOnGuestlist = true;
                      break;
                  }
              }

              //může vidět událost a přihlásit se na ní
              $contentVisibleToUser = false;

                // pokud je současný uživatel organizátor
              $currentUserIsOrganizer = false;
              if($event['organiser'] == $user_id){
                  $currentUserIsOrganizer = true;
              }

              if($event['public'] == 1 or $emailOnGuestlist or $currentUserIsOrganizer){
                  $contentVisibleToUser = true;
              }


              if($contentVisibleToUser){
              ?>
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
          <?php } }?>
        </div>
    </div>


  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<footer class="footer">
    <div class="container">
        <span>&copy; 2023 Martin Aschermann.</span>
    </div>
</footer>

</body>

</html>



