<?php
  //spustíme session
  session_start();

  //připojení k databázi
  require 'db.php';
	
  if (!empty($_POST)){
      $email = @$_POST['email'];
      $password = @$_POST['password'];

      $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1"); //limit 1 je tu jen jako vykonnostní optimalizace, 2 stejné maily v DB nebudou
      $stmt->execute([$email]);

      $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);


      if ($existingUser && password_verify($password, @$existingUser['password'])){
        //povedlo se nám najít daného uživatele v DB a zároveň bylo zadáno platné heslo => uložíme si ID uživatele do SESSION a přesměrujeme ho na homepage
        $_SESSION['user_id'] = $existingUser['id'];
        header('Location: index.php');
      }else{
        //u přihlášení uživatele nezobrazujeme konkrétní chybu (je to jediná výjimka, kdy není vhodné mít u formuláře úplně konkrétní chybu)
        $formError="Invalid user or password!";
      }
  }?>



<!DOCTYPE html>
<html>
<head>
  <title>Sign In Form</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container">
  <h2>Sign In</h2>

    <?php
    if (!empty($formError)){
        echo '<p style="color:red;">'.$formError.'</p>';
    }
    ?>

  <form method="post">
    <div class="form-group">
      <label for="email">Email:</label>
      <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
    </div>
    <div class="form-group">
      <label for="password">Password:</label>
      <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
    </div>
    <button type="submit" class="btn btn-primary">Sign In</button>
  </form>
</div>

<footer class="footer">
    <div class="container">
        <span>&copy; 2023 Martin Aschermann.</span>
    </div>
</footer>

</body>
</html>
