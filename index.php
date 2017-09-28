<?php
session_start();
$password = trim(fgets(fopen('pass.txt', 'r')));

$previousPage = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . "/parser/auth.php";

// update session if password is correct
if (isset($_POST['pass']) && ($_POST['pass'] == $password)) {
    $_SESSION["user"] = true;
} elseif ($_POST['pass'] != $password) {
    unset($_SESSION['user']);
    header("Location: $previousPage");
}

if (!isset($_SESSION['user'])) {
    header("Location: $previousPage");
}

?>
<html>
<head>
    <link href="http://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="css/materialize.min.css" media="screen,projection"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.98.1/css/materialize.min.css">
    <script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
    <script type="text/javascript"
            src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.97.5/js/materialize.min.js"></script>
    <script type="text/javascript" src="main.js"></script>
</head>

<body>
<div class="container">
    <div class="row">
        <div class="col s12 m6 offset-m3">
            <ul id="tabs-swipe-demo" class="tabs">
                <li class="tab col s4"><a class="active" href="#tab_1">Immobiliare.it</a></li>
                <li class="tab col s4"><a href="#tab_2">Idealista.it</a></li>
                <li class="tab col s4"><a href="#tab_3">Casa.it</a></li>
            </ul>
            <div id="tab_1" class="s12">
                <?php include "fragments/immobiliare.php" ?>
            </div>
            <div id="tab_2" class="s12">
                <?php include "fragments/idealista.php" ?>
            </div>
            <div id="tab_3" class="s12">
                <?php include "fragments/casa.php" ?>
            </div>
        </div>
        <br>
        <div class="col s12 m6 offset-m3">
            <div class="card">
                <div class="card-content">
                    <span class="card-title black-text">Change Password</span>
                    <form action="pass-change.php"
                          method="post">
                        <div class="row">
                            <div class="input-field col s12">
                                <input id="url" placeholder="SET PASSWORD" type="password" name="password"
                                       value="<?php echo $password ?>">
                                <label for="url">PASSWORD</label>
                            </div>
                        </div>
                        <div class="card-action">
                            <input type="submit" class="btn" value="CHANGE PASSWORD">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>