<?php
//spustíme session
session_start();

require "db.php";

require_once 'vendor/autoload.php';

//Kvůli mailu
use PHPMailer\PHPMailer\PHPMailer;

$user_id = $_SESSION['user_id'];

// získat mail současného uživatele
$user = $db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1;");
$user->execute([$user_id]);
$user = $user->fetch(PDO::FETCH_ASSOC); //

$emailofUser = $user['email'];


if(!empty($_POST)){

    if(!empty($_POST["message"])){
        $message = htmlspecialchars($_POST["message"]);
    } else {
        $message = "I invite you for an event. Click here for more.";
    }

    if(!empty($_POST["subject"])) {
        $subject = htmlspecialchars($_POST["subject"]);
    } else {
        $subject = "Invitation";
    }

    if(isset($_GET['id'])) {
        //get id of event from the url
        $event_id = $_GET['id'];

        $emailsOfInvited = $db->prepare("SELECT email FROM invited WHERE event_id = ?;");
        $emailsOfInvited->execute([$event_id]);
        $emailsOfInvited = $emailsOfInvited->fetchAll(PDO::FETCH_ASSOC);

    } else {
        echo "You don't have an event to invite people.";
    }

    // všechny emaily z inputu jsou dány do array a jsou
    $emails = htmlspecialchars($_POST['email']);
    $arrayOfEmails = explode(",", $emails);
    $arrayOfEmails = array_map('trim', $arrayOfEmails);

    foreach ($arrayOfEmails as $email){
        //uložíme všechny pozvané do DB

        //pokud je mail pozvaného uživatele již na guestlistu
        $emailAlreadyThere = false;
        foreach ($emailsOfInvited as $emailOfInvited){
            if($email == $emailOfInvited["email"]){
                $emailAlreadyThere = true;
                break;
            }
        }

        //nový pozvaný se zadává pouze pokud již na událost pozvaný nebyl
        if(!$emailAlreadyThere){
            $stmt = $db->prepare("INSERT INTO invited(email, event_id) VALUES (?, ?)");
            $stmt->execute([$email, $event_id]);


            $mailer=new PHPMailer(false); //parametrem kontruktoru můžeme volitelně zapnout/vypnout vyhazování odchytitelných výjimek - v tomto případě bez vyhazování výjimek (v příkladů s přílohou výjimky zapnuté jsou, můžete to tedy porovnat)
            $mailer->isSendmail();//nastavení, že se mail má odeslat přes sendmail

            //přidáme adresu příjemce a odesílatele (v našem případě je to jen jedna adresa, ale jinak mohou být samozřejmě jiné)
            $mailer->addAddress($email);
            $mailer->setFrom($emailofUser);
            //obdobně jdou použit metody addCC() a addBCC()

            //nastavíme kódování a předmět e-mailu
            $mailer->CharSet='utf-8';
            $mailer->Subject= $subject;

            $wholeMessage = '<html><head><meta charset="utf-8" /></head><body> ' . $message . " " . "<a href='https://esotemp.vse.cz/~ascm00/semestralka/event.php?id=3?id='" . $event_id . "\">" . '</a></body></html>';

            //přidáme HTML obsah (může jim být celý HTML dokument, nebo jen kousek body)
            $mailer->isHTML(true);
            $mailer->Body= $wholeMessage;

            //volitelně lze přidat alternativní obsah (pokud nemá být vytvořen z HTML obsahu)
            //$mailer->AltBody='alternativní obsah';

            if ($mailer->send()) {
                echo 'E-mail byl odeslán.';
            } else {
                echo "Vyskytla se chyba: " . $mailer->ErrorInfo;
            }


        }
    }


    header('Location: event.php?id=' . $event_id);


    //TODO poslat email všem kdo byli přidáni do seznamu
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Email Invitation Form</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .highlight-email {
            background-color: #f8f9fa;
            padding: 0 5px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container">
    <h1>Email Invitation</h1>
    <form method="post">
        <div class="form-group">
            <label for="email">Email addresses:</label>
            <input type="text" class="form-control" id="email" name="email" placeholder="Enter email addresses">
            <small class="form-text text-muted">Separate multiple addresses with commas (,)</small>
        </div>
        <div class="form-group">
            <label for="subject">Subject:</label>
            <input type="text" class="form-control" id="subject" placeholder="Enter email subject">
        </div>
        <div class="form-group">
            <label for="message">Message:</label>
            <textarea class="form-control" id="message" rows="5" placeholder="Enter email message"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Send Invitation</button>
    </form>
</div>

<footer class="footer">
    <div class="container">
        <span>&copy; 2023 Martin Aschermann. All rights reserved.</span>
    </div>
</footer>

<script>
    var emailInput = document.getElementById('email');

    emailInput.addEventListener('input', function(event) {
        var value = event.target.value;
        var emails = value.split(',');

        var highlightedEmails = emails.map(function(email) {
            return '<span class="highlight-email">' + email.trim() + '</span>';
        }).join(', ');

        emailInput.innerHTML = highlightedEmails;
    });
</script>

</body>
</html>



