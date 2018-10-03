var lengthOfWeek = 7;
var csrf = "";
var username = "";
var user_id = -1;

document.addEventListener("DOMContentLoaded", function(){
    // Add rows to the calendar
    let row = document.createElement("DIV");
    row.classList.add("table-row");
    for(let i = 0; i<lengthOfWeek; i++){
        row.innerHTML += '<div class="table-cell"></div>';
    }
    let calendar = document.getElementById("calendar");
    for(let i = 0; i<5; i++){
        calendar.appendChild(row.cloneNode(true));
    }
    setCsrf();
    getUsername();
}, false);

function createEventPopup(){
    console.log("Creating event popup");
}

function createRegisterPopup(){
    let modalBox = document.createElement("DIV");
    modalBox.classList.add("modal-box");
    let modal = document.createElement("DIV");
    modal.classList.add("card");
    modal.id = "registration-modal";
    let content = document.createElement("DIV");
    content.classList.add("card-body");
    content.innerHTML = `
        <h1>Register</h1>
        <div id="registration-form-group">
            <input class="registration-input" type="text" name="username" id="register-username" placeholder="Username"/>
            <input class="registration-input" type="password" name="password" id="register-password" placeholder="Password"/>
            <input class="registration-input" type="password" name="cpassword" id="register-cpassword" placeholder="Confirm Password"/>
            <div id="registration-alerts"></div>
            <div>
                <button class="btn btn-default" id="submit-registration">Submit</button>
            </div>
        </div>
    `;
    modal.appendChild(content);
    modalBox.appendChild(modal);
    document.getElementsByTagName("body")[0].appendChild(modalBox);
    modalBox.addEventListener("click", closeRegistrationModal, false);
    document.getElementById("submit-registration").addEventListener("click", registerUser, false);
    $(".registration-input").on("change", verifyRegistrationInputs);
}

function verifyRegistrationInputs(){
    let usernameField = document.getElementById("register-username");
    let passwordField = document.getElementById("register-password");
    let cpasswordField = document.getElementById("register-cpassword");
    let alertField = document.getElementById("registration-alerts");
    if(alertField === null){
        console.log("No alert field");
        return;
    }
    alertField.innerText = "";
    if(usernameField === null || passwordField === null || cpasswordField === null){
        alertField.innerText = "No username or password field";
    }else{
        if(!usernameValid(usernameField.value)){
            alertField.innerText = "Invalid username";
        }else if(passwordField.value !== cpasswordField.value){
            alertField.innerText = "Passwords do not match";
        }
    }
}

function registerUser(){
    let username = document.getElementById("register-username");
    if(username !== null){ username = username.value; }else{ return; }
    let password = document.getElementById("register-password");
    if(password !== null){ password = password.value; }else{ return; }
    let cpassword = document.getElementById("register-cpassword");
    if(cpassword !== null){ cpassword = cpassword.value; }else{ return; }
    request(function(r){
        if(r.status === "success"){
            closeRegistrationModal("manual");
            getUsername();
        }else{
            console.log(r);
        }
    },
    {"action": "register-user", "username":username, "password": password, "cpassword": cpassword});
}

function logUserIn(){
    let username = document.getElementById("username");
    let password = document.getElementById("password");
    if(username !== null && password !== null){
        username = username.value;
        password = password.value;
    }else{return;}
    request(function(r){
        console.log(r);
        getUsername();
    }, {"action": "log-user-in", "username": username, "password": password});
}

function closeRegistrationModal(evt){
    let obj = null;
    if(evt.target === this){
        obj = event.target;
    }else if(evt==="manual"){
        obj = document.getElementsByClassName("modal-box")[0];
    }
    try{
        console.log(obj);
        obj.removeEventListener("click", closeRegistrationModal);
        $(".registration-input").off("change", verifyRegistrationInputs);
        document.getElementById("submit-registration").removeEventListener("click", registerUser);
        obj.parentNode.removeChild(obj);
    }catch(e){
        console.log(e);
    }
}

function getUsername(){
    request(function(r){
        try{
            if(r.username !== null){
                user_id = parseInt(r.user_id);
                username = r.username;
            }else{
                username = "";
                user_id = -1;
            }
        }catch(e){
            console.log(e);
            username = "";
            user_id = -1;
        }
        loadContent();
    }, {"action":"get-username"});
}

function logout(){
    request(function(r){
        console.log(r);
        csrf = r["new-csrf"];
        getUsername();
    }, {"action":"logout"});
}

function loadContent(){
    let dropdown = document.getElementById("dropdown-toggle");
    dropdown.innerHTML = "<span class='caret'></span>";
    let menu = document.getElementById("dropdown-menu");
    if(username === ""){
        dropdown.insertBefore(document.createTextNode("Guest"), dropdown.childNodes[0]);
        menu.innerHTML = `
            <li>
                <p>Login:</p>
                <input type="text" name="username" id="username" placeholder="Username"/>
                <input type="password" name="password" id="password" placeholder="Password"/>
                <button class="btn btn-default" id="submit-login">Login</button>
                <button class="btn btn-default" id="register-button">Register</button>
            </li>
        `;
    }else{
        dropdown.insertBefore(document.createTextNode(username), dropdown.childNodes[0]);
        menu.innerHTML = `
            <li>
                <button class="btn btn-default create-event-button" id="create-event-button-dropdown">Create Event</button>
                <button class="btn btn-default logout-button" id="logout-button-dropdown">Logout</button>
            </li>
        `;
    }
    $("#register-button").on("click", createRegisterPopup);
    $("#submit-login").on("click", logUserIn);
    $(".create-event-button").on("click", createEventPopup);
    $(".logout-button").on("click", logout);
}

function request(callback, params={}){
    let xhr = new XMLHttpRequest();
    xhr.addEventListener("load", function(evt){
        console.log(evt.target.responseText);
        let r = JSON.parse(evt.target.responseText);
        callback(r);
    }, false);
    params.csrf = csrf;
    xhr.open("POST", "javascriptrequests.php");
    xhr.setRequestHeader("Content-Type", "text/json");
    xhr.send(JSON.stringify(params));
}

/*
 * Set the csrf variable to the value inside the hidden div
 * with id "csrf" so that we can send requests with a csrf
 * token.
 */
function setCsrf(){
    csrf = document.getElementById("csrf").innerText;
}

function usernameValid(username){
    return /^[a-zA-Z]+$/.test(username);
}