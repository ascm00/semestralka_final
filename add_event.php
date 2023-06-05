<?php
//spustíme session
session_start();

require "db.php";

//pokud uživatel není přihlášen, nemůže vytvářet události
$loggedIn = false;
if(isset($_SESSION['user_id'])){
    $loggedIn = true;
}

if(!empty($_POST)){

    $errorMesage = "";


    $name = trim($_POST['name']??"");
    $date = $_POST['date'];
    $time = $_POST['time'];
    $datetime = DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $time);
    $combinedDateTime = $datetime->format('Y-m-d H:i:s');

    $place = trim($_POST['place']??"");
    $description = trim($_POST['description']??"");
    $user_id = trim($_SESSION['user_id']??"");

    // defaultně je akce nastavená jako soukromá, pokud uživatel označí checkbox, stane se veřejnou
    $public = 0;
    if(isset($_POST['public'])){
        $public = 1;
    }

    if (empty($name) || empty($date) || empty($time) || empty($place)) {

        $errorMessage = "Your form is not valid. Something is missing -";

        if(empty($name)){
            $errorMessage .= " name";
        }
        if(empty($date)){
            $errorMessage .= " date";
        }
        if(empty($time)){
            $errorMessage .= " time";
        }
        if(empty($time)){
            $errorMessage .= " place";
        }


        $somethingWrong = true;

    } else {
        //uložím event do DB
        $stmt = $db->prepare("INSERT INTO event(name, datetime, place, description, public, organiser) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $combinedDateTime, $place, $description, $public, $user_id]);


        $idOfInsertedRow = $db->lastInsertId();
        header('Location: invite.php?id=' . $idOfInsertedRow);
    }

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

if($loggedIn){?>

        <?php if(isset($somethingWrong)){ ?>

        <div class="container">
            <div class="alert alert-warning" role="alert">
                <?php echo $errorMessage;?>
            </div>
        </div>

            <div class="container">
                <h2>Create Event</h2>
                <form method="post">
                    <div class="form-group">
                        <label for="name">Event Name:</label>
                        <input type="text" name="name" id="name" class="form-control" value="<?php echo $name; ?>"  placeholder="Enter event name" required>
                    </div>
                    <div class="form-group">
                        <label for="date">Event Date: </label>
                        <input type="date" name="date" id="date" class="form-control" value="<?php echo $date; ?>"  required>
                    </div>
                    <div class="form-group">
                        <label for="time">Event Time:</label>
                        <input type="time" name="time" id="time" value="<?php echo $time; ?>" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="place">Event Location:</label>
                        <input type="text" name="place" id="place" class="form-control" placeholder="Enter event location" value="<?php echo $place; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Event Description:</label>
                        <textarea class="form-control" name="description" id="description" placeholder="Enter event description" value="<?php echo $description; ?>" rows="5" required></textarea>
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
                    -->
                    <button type="submit" class="btn btn-primary">Create Event</button>
                </form>
            </div>
            <?php } else { ?>

<div class="container">
  <h2>Create Event</h2>
  <form method="post">
    <div class="form-group">
      <label for="name">Event Name:</label>
      <input type="text" name="name" id="name" class="form-control"  placeholder="Enter event name">
    </div>
    <div class="form-group">
      <label for="date">Event Date: </label>
      <input type="date" name="date" id="date" class="form-control"  required>
    </div>
    <div class="form-group">
      <label for="time">Event Time:</label>
      <input type="time" name="time" id="time" class="form-control" required>
    </div>
    <div class="form-group">
      <label for="place">Event Location:</label>
      <input type="text" name="place" id="place" class="form-control" placeholder="Enter event location" required>
    </div>
    <div class="form-group">
      <label for="description">Event Description:</label>
      <textarea class="form-control" name="description" id="description" placeholder="Enter event description" rows="5" required></textarea>
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
      -->
    <button type="submit" class="btn btn-primary">Create Event</button>
  </form>
</div>

<?php } } else { ?>
<div class="container">
    <div class="alert alert-warning" role="alert">
        You have to be logged in to create an event.
    </div>
    <div class="text-center">
        <a href="signin.php" class="btn btn-primary">Log In</a>
        <a href="signup.php" class="btn btn-success">Sign Up</a>
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

