<?php
  //spustíme session
  session_start();

  //připojení k databázi
  require 'db.php';
	
  if (!empty($_POST)) {
	
    $email = htmlspecialchars(trim(@$_POST['email']));
    $password = @$_POST['password'];
    $confirmPassowrd = @$_POST['confirmPassword'];
    $name = htmlspecialchars(trim(@$_POST['name']));

    $correctPassword = false;
    if($confirmPassowrd == $password){
        $correctPassword = true;

        $passwordHash = password_hash($password, PASSWORD_DEFAULT); //pokud nemáte důvod to měnit, nechte heslo zahashovat výchozí funkcí; další možnosti viz manuál

        //uložíme uživatele do DB
        $stmt = $db->prepare("INSERT INTO users(email, password, name) VALUES (?, ?, ?)");
        $stmt->execute([$email, $passwordHash, $name]);

        //teď je uživatel uložen v DB - potřebujeme jeho id
        //buď můžeme vzit id posledního záznamu přes last insert id, ale co když se to potká s více requesty? = ne zcela bezpečné
        //lepší je načíst uživatele podle e-mailu = OK :)

        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1"); //limit 1 jen tu jen jako výkonnostní optimalizace
        $stmt->execute([$email]);
        //uživatele rovnou přihlásíme
        $_SESSION['user_id'] = (int)$stmt->fetchColumn();

        //přesměrujeme uživatele na homepage
        header('Location: index.php');

    }


  }

?><!DOCTYPE html>
<html>
<head>
    <title>Sign Up Form</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container">
    <h2>Sign Up</h2>
    <?php if(isset($correctPassword)){
        if(!$correctPassword){ ?>
    <div class="container">
        <div class="alert alert-warning" role="alert">
            Passwords are not same.
        </div>
    </div>
    <?php } }?>
    <form method="post">
        <div class="form-group">
            <label for="name">Name:</label>
            <input type="text" class="form-control" name="name" id="name" placeholder="Enter your name" required>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Enter a password" required>
        </div>
        <div class="form-group">
            <label for="confirmPassword">Confirm Password:</label>
            <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" placeholder="Confirm your password" required>
        </div>
        <button type="submit" class="btn btn-primary">Sign Up</button>
    </form>
</div>

<footer class="footer">
    <div class="container">
        <span>&copy; 2023 Martin Aschermann.</span>
    </div>
</footer>

</body>
</html>

