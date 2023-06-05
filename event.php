<?php
//spustíme session
session_start();

require 'db.php';

// vememe z url id eventu
$event_id = $_GET['id'];

//získáme id přihlášeného uživatele

$user_id = null;
if(isset($_SESSION['user_id'])){
    $user_id = $_SESSION['user_id'];
}

// vybrat event a získat to jako PDO object
$event = $db->prepare("SELECT * FROM event WHERE id = ? LIMIT 1;");
$event->execute([$event_id]);
$event = $event->fetch(PDO::FETCH_ASSOC); //

//všechny emaily pozvaných
$emailsOfInvited = $db->prepare("SELECT email FROM invited WHERE event_id = ?;");
$emailsOfInvited->execute([$event_id]);
$emailsOfInvited = $emailsOfInvited->fetchAll(PDO::FETCH_ASSOC);

//email současného uživatele
$emailOfCurrentUser = $db->prepare("SELECT email FROM users WHERE id = ? LIMIT 1;");
$emailOfCurrentUser->execute([$user_id]);
$emailOfCurrentUser = $emailOfCurrentUser->fetch(PDO::FETCH_ASSOC); //

//vybereme všechny účastníky dané události
$participants = $db->prepare("SELECT u.name, u.email, u.id
FROM users u
JOIN participants p ON u.id = p.user_id
WHERE p.event_id = ?;");
$participants->execute([$event_id]);
$participants = $participants->fetchAll(PDO::FETCH_ASSOC);



// získat všechny účastníky události
$particip = [];
$partip = [];
foreach($participants as $participant){
    $particip[] = $participant["name"];
    $partip[] = $participant["id"];
}
$particip = array_unique($particip);
$partip = array_unique($partip);

// if current user is participant
$currentUserIsParticipant = in_array($user_id, $partip);





//COMMENTS - získat všechny komentáře k danému příspěvku z databáze
$comments = $db->prepare("SELECT * FROM comment WHERE event_id = ?;");
$comments->execute([$event_id]);
$comments = $comments->fetchAll(PDO::FETCH_ASSOC); //


// získat separátně čas a datum z proměnné datetime v db
if($event){
    $datetime = new DateTime($event['datetime']);
    $date = $datetime->format('Y-m-d');
    $time = $datetime->format('H:i:s');
}


//pokud je mail přihlášeného na na guestlistu
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
if($event){
    if($event['organiser'] == $user_id){
        $currentUserIsOrganizer = true;
    }

    if($event['public'] == 1 or $emailOnGuestlist or $currentUserIsOrganizer){
        $contentVisibleToUser = true;
    }

}


//Pokud je Post metoda, znamená to, že byl buď přidán komentář, nebo se někdo připojil
if(!empty($_POST)){
    // to znamená že byl přidán komentář
    if(!isset($_POST['comment'])){

        //pokud je již uživatel jako účastník a klikne I ja
        if(isset($_POST["hide"])){
            $stmt = $db->prepare("DELETE FROM participants WHERE user_id = ? AND event_id = ?;");
            $stmt->execute([$user_id, $event_id]);
            $currentUserIsParticipant = false;
        } else {
            //uložíme současného uživatele jako účastníka události
            $stmt = $db->prepare("INSERT INTO participants(user_id, event_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $event_id]);
            $currentUserIsParticipant = true;
        }
        //vybereme všechny účastníky dané události
        $participants = $db->prepare("SELECT u.name, u.email
            FROM users u
            JOIN participants p ON u.id = p.user_id
            WHERE p.event_id = ?;");
        $participants->execute([$event_id]);
        $participants = $participants->fetchAll(PDO::FETCH_ASSOC);

        // získat všechny účastníky události
        $particip = [];
        foreach ($participants as $participant) {
            $particip[] = $participant["name"];
        }
        $particip = array_unique($particip);

    } else {
        $datetime = date('Y-m-d H:i:s');
        $text = $_POST['comment'];

        $stmt = $db->prepare("INSERT INTO comment(datetime, text, event_id, user_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$datetime, $text, $event_id, $user_id]);

        $comments = $db->prepare("SELECT * FROM comment WHERE event_id = ?;");
        $comments->execute([$event_id]);
        $comments = $comments->fetchAll(PDO::FETCH_ASSOC); //
    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Event </title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <style>
        .event-image {
            max-width: 100%;
            height: auto;
        }
        .event-details {
            margin-top: 20px;
        }

        .comment {
            background-color: #f7f7f7;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
        }

    </style>

</head>

<body>
<?php include 'navbar.php';

if($contentVisibleToUser and $event){ ?>
<div class="container" >
    <div class="row">
        <!--
        <div class="col-md-6">
            <img src="event-image.jpg" alt="Event Image" class="event-image">
        </div>
        -->
        <div class="col-md-6">
            <h1><?php echo htmlspecialchars($event['name']); ?></h1>
            <p><?php echo htmlspecialchars($event['description']); ?></p>
            <p><strong>Date:</strong> <?php echo $date; ?></p>
            <p><strong>Time:</strong> <?php echo $time; ?></p>
            <p><strong>Place:</strong> <?php echo htmlspecialchars($event['place']); ?></p>
            <p><strong>Participants:</strong>
                <?php
                foreach ($particip as $participant){
                    echo $participant . ", ";
                }
                ?>
            </p>
            <p><strong>Invited:</strong>
                <?php
                    foreach ($emailsOfInvited as $emailOfInvited){
                        echo $emailOfInvited["email"] . ", ";
                    }
                ?>

            </p>
        </div>
        <?php if($user_id != null){
            if ($currentUserIsOrganizer){ ?>
            <!-- Edit tlačítko a zrušit -->
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-auto">
                            <form action="delete.php" method="POST">
                                <input type="hidden" name="delete" id="delete" value="<?php echo $event_id; ?>">
                                <button type="submit" class="btn btn-primary" name="button1">Delete event</button>
                            </form>
                        </div>
                        <div class="col-auto">
                            <a href="edit.php?id=<?php echo $event_id ?>" class="btn btn-primary">Edit event</a>
                        </div>
                        <div class="col-auto">
                            <a href="invite.php?id=<?php echo $event_id ?>" class="btn btn-primary">Invite people</a>
                        </div>
                    </div>
                </div>
        <?php } else {
                if(!$currentUserIsParticipant){?>
        <form method="post">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-auto">
                        <input type="hidden" name="hidden" value="0">
                            <button type="submit" class="btn btn-primary">Join the event</button>
                    </div>
                </div>
            </div>
        </form>
        <?php } else { ?>
        <form method="post">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-auto">
                        <input type="hidden" id="hide" name="hide" value="no">
                        <button type="submit" class="btn btn-primary">I can't join.</button>
                    </div>
                </div>
            </div>
        </form>
        <?php } } }?>
    </div>

    <?php if($user_id != null){ ?>
    <div class="row" style="margin-top: 15px;">
        <div class="col-md-12">
            <h2>Comments</h2>
            <?php foreach ($comments as $comment){ ?>
            <div class="comment">
                <div class="comment-header">
                    <strong>
                        <?php
                        $idOfCommenter = $comment['user_id'];

                        $name = $db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1;");
                        $name->execute([$idOfCommenter]);
                        $name = $name->fetch(PDO::FETCH_ASSOC); //

                        echo $name['name'];
                        ?>
                    </strong>
                    <span class="comment-date"><?php echo $comment['datetime'] ?></span>
                </div>
                <div class="comment-body">
                    <p><?php echo htmlspecialchars($comment['text']); ?></p>
                </div>
            </div>
            <?php } ?>
            <form method="post">
                <div class="form-group">
                    <label for="comment">Leave a comment:</label>
                    <textarea class="form-control" id="comment" name="comment" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
    </div>
    <?php } else { ?>
        <div class="container">
            <div class="alert alert-warning" role="alert">
                You have to be logged in to join the event.
            </div>
            <div class="text-center">
                <a href="signin.php" class="btn btn-primary">Log In</a>
                <a href="signup.php" class="btn btn-success">Sign Up</a>
            </div>
        </div>
    <?php } ?>
</div>
<?php } elseif ($user_id == null) { ?>
    <div class="container">
        <div class="alert alert-warning" role="alert">
            You have to be logged in to see the content.
        </div>
        <div class="text-center">
            <a href="signin.php" class="btn btn-primary">Log In</a>
            <a href="signup.php" class="btn btn-success">Sign Up</a>
        </div>
    </div>

<?php } else { ?>
<div class="alert alert-danger">
    <strong>Sorry, you cannot see this content!</strong>
    <br>
    We apologize for the inconvenience, but it appears that you're currently unable to view this content.
    <br>
</div>
<?php } ?>

<footer class="footer">
    <div class="container">
        <span>&copy; 2023 Martin Aschermann.</span>
    </div>
</footer>


  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
