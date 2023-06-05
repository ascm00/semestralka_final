<?php
//spustíme session
session_start();

require 'db.php';

$currentUserIsOrganiser = false;

//získáme id uživatele a eventu
$user_id = $_SESSION['user_id'];
$event_id = $_POST["delete"];

$organiser = $db->prepare("SELECT * FROM event WHERE id = ? LIMIT 1;");
$organiser->execute([$event_id]);
$organiser = $organiser->fetch(PDO::FETCH_ASSOC); //
$organiser = $organiser["organiser"];
var_dump($organiser);
var_dump($user_id);


if($organiser == $user_id){
    $currentUserIsOrganiser = true;
}



if(!empty($_POST)){
    if($_POST["delete"]){
        if ($currentUserIsOrganiser) {
            $stmt = $db->prepare("DELETE FROM event WHERE id = ?;");
            $stmt->execute([$event_id]);
        }
        else {
            echo "This is not your event. You can't delete it.";
        }
    }

}

header('Location: index.php');
