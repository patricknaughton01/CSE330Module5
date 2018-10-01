<!DOCTYPE html>
<html lang="en">
    <head>
        <title>330 Calendar</title>
        <meta charset="utf-8"/>
        <link rel="stylesheet" href="home.css"/>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
        <script src="http://classes.engineering.wustl.edu/cse330/content/calendar.min.js"></script>
        <script src="home.js"></script>
    </head>
    <body>
        <?php
            require_once("helpers.php");
            printf("<div class='hidden' id='csrf'>%s</div>", $_SESSION["csrf"]);
        ?>
        <nav class="navbar navbar-default">
            <div class="container-fluid">
                <div class="navbar-header">
                    <a class="navbar-brand">330 Calendar</a>
                </div>
                <div class="collapse navbar-collapse">
                    <ul class="nav navbar-nav">
                        <li><button class="btn btn-default" id="today-button">Today</button></li>
                        <li><button class="btn btn-default create-event-button" id="create-event-button-header">Create an Event</button></li>
                    </ul>
                    <ul class="nav navbar-nav navbar-right">
                        <li class="dropdown">
                            <a href="#" id="dropdown-toggle" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu" id="dropdown-menu">
                                <?php
                                    if(!isset($_SESSION["username"])){
                                        printf('
                                            <p>Login:</p>
                                            <input type="text" name="username" id="username" placeholder="Username"/>
                                            <input type="password" name="password" id="password" placeholder="Password"/>
                                            <button class="btn btn-default" id="submit-login">Login</button>
                                            <button class="btn btn-default" id="register-button">Register</button>
                                        ');
                                    }else{
                                        printf('
                                            <button class="btn btn-default create-event-button" id="create-event-button-dropdown">Create Event</button>
                                            <button class="btn btn-default logout-button" id="logout-button-dropdown">Logout</button>
                                        ');
                                    }
                                ?>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="calendar-container container">
            <div class="table" id="calendar">
                <div class="table-row" id="calendar-header">
                    <div class="table-cell">Sunday</div>
                    <div class="table-cell">Monday</div>
                    <div class="table-cell">Tuesday</div>
                    <div class="table-cell">Wednesday</div>
                    <div class="table-cell">Thursday</div>
                    <div class="table-cell">Friday</div>
                    <div class="table-cell">Saturday</div>
                </div>
            </div>
        </div>
    </body>
</html>