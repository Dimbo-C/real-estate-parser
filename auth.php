<?php //$page = "/" . $_SERVER['HTTP_HOST'] . "/parser" ?>
<?php $page = "/parser/" ?>
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
            <div class="card">
                <div class="card-content">
                    <span class="card-title black-text">Protection:</span>
                    <form action="<?= $page ?>"
                          method="post">
                        <div class="row">
                            <div class="input-field col s12">
                                <input id="pass" placeholder="Password" type="password" name="pass">
                            </div>
                        </div>
                        <div class="card-action">
                            <input type="submit" class="btn" value="Enter">
                        </div>
                    </form>
                    <div class="card-action">
                        <input type="button" id="forgot" class="btn modal-trigger" data-target="modal1"
                               value="FORGOT PASSWORD">
                    </div>
                    <div id="modal1" class="modal">
                        <div class="modal-content">
                            <h4>RESET PASSWORD</h4>
                            <div class="row">
                                <form class="col s12" id="forgot-form">
                                    <div class="row modal-form-row">
                                        <div class="input-field col s12">
                                            <input id="mail" name="mail" type="email" class="validate">
                                            <label for="mail">EMAIL</label>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <a class=" modal-action modal-close waves-effect waves-green btn-flat"
                               id="reset">Submit</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>