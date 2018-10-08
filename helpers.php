<?php
    session_start();
    if(!isset($_SESSION["csrf"])){
        $_SESSION["csrf"] = bin2hex(openssl_random_pseudo_bytes(32));
    }
    date_default_timezone_set("America/Chicago");

    //*********************Authentication**********************//
    // These methods are from our module 3 code

    if(isset($_SESSION["username"])){
        set_user_id($_SESSION["username"]);
    }

    /*
     * Set the session variable user-id so that it's easily accessible.
     *
     * @param string $username the username to get the id of.
     * @return string "success" if the id was successfully set. Else:
     *      "dup-username"  :   Multiple users with same name
     *      "not-user"      :   0 users with the requested username
     */
    function set_user_id($username){
        $sqli = connect();
        $stmt = prepare_query($sqli, "select COUNT(*), id from users where username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($cnt, $id);
        $stmt->fetch();
        if($cnt > 1){
            return "dup-username";
        }elseif($cnt <= 0){
            return "not-user";
        }else{
            $_SESSION["user-id"] = $id;
            return "success";
        }
    }

    /*
     * Register a new user identified by $username and $password.
     *
     * @param string $username the username of the new user
     * @param string $password the password the user entered
     * @param string $confirm_password a confirmation password
     *      that should match $password
     * @return string "success" if the user was successfully registered,
     *      otherwise an error code:
     *          "null-values"       :   One of the entered values was null
     *          "invalid-chars"     :   The username contains invalid characters (see `username_valid`)
     *          "password-mismatch" :   $password !== $confirm_password
     *          "username-taken"    :   the specified username has already been taken.
     */
    function register($username, $password, $confirm_password){
        $username = (string) $username;
        $password = (string) $password;
        $confirm_password = (string) $confirm_password;
        if($username === null || $password === null || $confirm_password === null){
            return "null-values";
        }
        if(!username_valid($username)){
            return "invalid-chars";
        }
        if($password !== $confirm_password){
            return "password-mismatch";
        }
        $sqli = connect();
        $stmt = prepare_query($sqli, "select COUNT(*) from users where username=?");
        $stmt->bind_param("s", htmlspecialchars($username));
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        if($count > 0){
            return "username-taken";
        }
        $stmt->close();
        $stmt = prepare_query($sqli, "insert into users (username, hash) values(?,?)");
        $stmt->bind_param("ss", htmlspecialchars($username), password_hash($password, PASSWORD_DEFAULT));
        $stmt->execute();
        $stmt->close();
        $_SESSION["username"] = $username;
        set_user_id($username);
        return("success");
    }

    /*
     * Check if the user should be logged in.
     *
     * @param AssociativeArray $post post data coming into the page.
     * @return string "success" if user is logged in, otherwise an error
     *      code:
     *          "null-username-or-password"     :   The username or password were null
     *          "invalid-chars"                 :   The username contained invalid characters (see `username_valid`);4
     *          "statement-prep-error"          :   The SQL query couldn't be prepared.
     *          "dup-username"                  :   The users table has two entries with that username.
     *          "invalid-password"              :   The user entered the wrong password.
     */
    function check_user($post){
        if(isset($_SESSION["username"])){
            set_user_id($_SESSION["username"]);
            return "success";
        }
        if(isset($post["username"]) && isset($post["password"])){
            if(!csrf_valid($post)){
                return "csrf-error";
            }
            $username = (string) $post["username"];
            $password = (string) $post["password"];
            if($username===null || $password === null){
                return "null-username-or-password";
            }
            if(!username_valid($username)){
                return "invalid-chars";
            }
            return verify_user($username, $password);
        }
    }

    /**
     * Determines if the user identified by $username is in
     * the table "users". If the username is in the table
     * of users and the password is correct, set the
     * $_SESSION["username"] variable to $username. Only to be called
     * by other `helpers.php` functions that have already filtered input.
     *
     * @param string $username the username to check for
     * @param string $password the password the user entered
     * @return string "success" if the user is verified, some error if the
     * user is not verified:
     *      "dup-username"          :   The users table has two entries with that username.
     *      "not-user"              :   The user hasn't registerd yet (they aren't a user).
     *      "invalid-password"      :   The user entered the wrong password.
     */
    function verify_user($username, $password){
        $sqli = connect();
        $stmt = prepare_query($sqli, "select COUNT(*), username, hash from users where username=?");
        $stmt->bind_param("s", htmlspecialchars($username));
        $stmt->execute();
        $stmt->bind_result($count, $result_username, $result_hash);
        $stmt->fetch();
        $rval = "";
        if($count == 1 && password_verify($password, $result_hash)){
            $_SESSION["username"] = $result_username;
            $rval = set_user_id($result_username);
        }elseif($count >= 1){
            printf("Duplicate username: %s\n", $result_username);
            $rval = "dup-username".$result_username;
        }elseif($count === 0){
            $rval = "not-user";
        }else{
            $rval = "invalid-password";
        }
        $stmt->close();
        return $rval;
    }

    /*
     * Returns if the passed csrf token is valid
     *
     * @param AssociativeArray $post post data coming into the page
     * @return boolean whether or not the csrf token is valid
     */
    function csrf_valid($post){
        if(!isset($post["csrf"])){
            return false;
        }
        $csrf = (string) $post["csrf"];
        return $csrf === $_SESSION["csrf"];
    }

    //******************Database**************************//
    // connect() and prepare_query() are also from Module 3 (connect() uses
    // a different path to get the database credentials.

    /*
     * Connect to the calendar database and output an error if there is one.
     *
     * @return mysqli connection to the news database.
     */
    function connect(){
        $uname_path = "/etc/330/calendar330/unrelated_info1";
        $pword_path = "/etc/330/calendar330/unrelated_info2";
        $uname = trim(file_get_contents($uname_path));
        $pword = trim(file_get_contents($pword_path));
        $mysqli = new mysqli('localhost', $uname, $pword, 'calendar');
        if($mysqli->connect_errno){
            printf("MySQL connection error: %s\n", $mysqli->connect_errno);
            exit;
        }
        return $mysqli;
    }

    /*
     * Use the $sqli object to prepare the query in $query_string. Return a
     * statement that contains the prepared query.
     *
     * @param sqli $sqli the sqli object
     * @param string $query_string the query to prepare
     * @return statement the statement object containing the prepared query.
     */
    function prepare_query($sqli, $query_string){
        $stmt = $sqli->prepare($query_string);
        if(!$stmt){
            printf("Query Prep Failed: %s\n", $sqli->error);
            exit;
        }
        return $stmt;
    }

    //********************Filtering*************************//
    // This is also from Module 3

    /**
     * Returns if the username is valid (nonzero number of alphanumeric characters)
     *
     * @param string $username the username to test
     * @return boolean whether or not the username is valid
     */
    function username_valid($username){
        return preg_match("/^\w+$/", $username);
    }

    /**
     * Check if $post contains a non null value for $key.
     * If it does, return this value, otherwise, exit immediately.
     *
     * @param AssociativeArray $post post data coming into the page.
     * @param string $key the key to check for in $post
     * @return unknown the value associated with $key in $post if there is one.
     */
    function validate($post, $key){
        if(isset($post[$key]) && $post[$key] !== null){
            return $post[$key];
        }
        printf( '{"status": "error", "type": "POST key", "message" "Missing key: %s"}', $key );
        exit;
    }
?>