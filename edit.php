<?php

session_start();

require 'db.php';

$currentUserIsOrganiser = false;

//získáme id uživatele a eventu
$user_id = $_SESSION['user_id'];
$event_id = $_GET["id"];

$event = $db->prepare("SELECT * FROM event WHERE id = ? LIMIT 1;");
$event->execute([$event_id]);
$event = $event->fetch(PDO::FETCH_ASSOC); //
$organiser = $event["organiser"];

//kontrola zda uživatel, který přichází je organizátorem události
if($organiser == $user_id){
    $currentUserIsOrganiser = true;
}

if(!empty($_POST)){
    $name = htmlspecialchars($_POST['name']);

    $date = htmlspecialchars($_POST['date']);
    $time = htmlspecialchars($_POST['time']);
    $datetime = DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $time);
    $combinedDateTime = $datetime->format('Y-m-d H:i:s');

    $place = htmlspecialchars($_POST['place']);
    $description = htmlspecialchars($_POST['description']);
    $image = htmlspecialchars($_POST['image']);
    $user_id = htmlspecialchars($_SESSION['user_id']);

    // defaultně je akce nastavená jako soukromá, pokud uživatel označí checkbox, stane se veřejnou
    $public = 0;
    if(isset($_POST['public'])){
        $public = 1;
    }

    //uložím event do DB
    $stmt = $db->prepare("UPDATE event SET name = ?, datetime = ?, place = ?, description = ?, public = ?, organiser = ? WHERE id = ?;");
    $stmt->execute([$name, $combinedDateTime, $place, $description, $public, $user_id, $event_id]);

    header('Location: event.php?id=' . $event_id);
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Event Form</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<?php include 'navbar.php';

if($currentUserIsOrganiser){
    ?>

    <div class="container">
        <h2>Edit Event</h2>
        <form method="post">
            <div class="form-group">
                <label for="name">Event Name:</label>
                <input type="text" name="name" id="name" class="form-control" placeholder="Enter event name" value="<?php echo $event["name"]?>" required>
            </div>
            <div class="form-group">
                <label for="date">Event Date: </label>
                <input type="date" name="date" id="date" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="time">Event Time:</label>
                <input type="time" name="time" id="time" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="place">Event Location:</label>
                <input type="text" name="place" id="place" class="form-control" placeholder="Enter event location" value="<?php echo $event["place"]?>" required>
            </div>
            <div class="form-group">
                <label for="description">Event Description:</label>
                <textarea class="form-control" name="description" id="description" placeholder="Enter event description" rows="5" value="<?php echo $event["description"]?>" required></textarea>
            </div>
            <div class="form-group form-check">
                <input type="checkbox" class="form-check-input" name="public" id="public">
                <label class="form-check-label" for="public">Public Event</label>
            </div>
            <!--
            <div class="form-group">
                <label for="image">Event Image:</label>
                <input type="file" class="form-control-file" id="image" name="image">
            </div>
            <button type="submit" class="btn btn-primary">Create Event</button>
            -->
        </form>
    </div>

<?php } else { ?>
    <div class="container">
        <div class="alert alert-warning" role="alert">
            You have to be logged and be the organiser of the event to edit it.
        </div>
    </div>
<?php } ?>

<footer class="footer">
    <div class="container">
        <span>&copy; 2023 Martin Aschermann.</span>
    </div>
</footer>

</body>
</html>




