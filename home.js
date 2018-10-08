var csrf = "";
var username = "";
var user_id = -1;
var currentDate = new Date();
var currentMonth = null;
var calendar = null;

document.addEventListener("DOMContentLoaded", function(){
    calendar = document.getElementById("calendar");
    currentMonth = new Month(currentDate.getFullYear(), currentDate.getMonth());
    setCsrf();
    getUsername();
    $(".previous-month-button").on("click", function(){
        currentMonth = currentMonth.prevMonth();
        updateCalendar(currentMonth);
    });
    $(".next-month-button").on("click", function(){
        currentMonth = currentMonth.nextMonth();
        updateCalendar(currentMonth);
    });
}, false);

function updateCalendar(month){
    document.getElementById("year").innerText = "Year: " + month.year;
    document.getElementById("disp-month").innerText = "Month: " + (month.month + 1);
    calendar.innerHTML = `
        <div class="table-row" id="calendar-header">
            <div class="table-cell">Sunday</div>
            <div class="table-cell">Monday</div>
            <div class="table-cell">Tuesday</div>
            <div class="table-cell">Wednesday</div>
            <div class="table-cell">Thursday</div>
            <div class="table-cell">Friday</div>
            <div class="table-cell">Saturday</div>
        </div>
    `;
    let weeks = month.getWeeks();
    for(let w = 0; w<weeks.length; w++){
        let row = document.createElement("DIV");
        row.classList.add("table-row");
        let days = weeks[w].getDates();
        for(let d = 0; d<days.length; d++){
            let dispMonth = days[d].getMonth() + 1;
            let dispDate = days[d].getDate();
            let dispYear = days[d].getFullYear();
            row.innerHTML +=
            '<div class="table-cell" id="' + dispYear + "-" + dispMonth + "-" + dispDate + '">' +
                '<div class="date-field">' +
                    dispMonth +
                    '/' +
                    dispDate +
                '</div>' +
            '</div>';
        }
        calendar.appendChild(row.cloneNode(true));
    }
    request(function(r){
        if(r.status === "success"){
            for(let evt in r.events){
                let start_date = new Date(r.events[evt].start_time);
                let end_date = new Date(r.events[evt].end_time);
                for(let d = start_date; d<=end_date; d.setDate(d.getDate()+1)){
                    let dayElem = document.getElementById(
                        d.getFullYear() + "-" + (d.getMonth()+1) + "-" + d.getDate());
                    if(dayElem !== null){
                        let eventElem = document.createElement("DIV");
                        let eventText = document.createTextNode(r.events[evt].title);
                        eventElem.appendChild(eventText);
                        eventElem.classList.add("event");
                        eventElem.id = "event-" + evt;
                        dayElem.appendChild(eventElem);
                    }
                }
            }
        }
    },
    {"action": "get-events", "month": month.month, "year": month.year});
}

function createEventPopup(month=-1, year=-1, day=1){
    if(month === -1){
        month = currentMonth.month;
    }
    if(year === -1){
        year = currentMonth.year;
    }
    // if user is logged in.
    if(username !== ""){
        createModal(`<h1>New Event</h1>`, closeEventPopup);
    }
}

function createRegisterPopup(){
    if (username === "") {
        createModal(`
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
        `, closeRegistrationModal);
        document.getElementById("submit-registration").addEventListener("click", registerUser, false);
        $(".registration-input").on("change", verifyRegistrationInputs);
    }

}

function createModal(contentHTML, closeCallback){
    let modalBox = document.createElement("DIV");
    modalBox.classList.add("modal-box");
    let modal = document.createElement("DIV");
    modal.classList.add("card");
    let content = document.createElement("DIV");
    content.classList.add("card-body");
    content.innerHTML = contentHTML;
    modal.appendChild(content);
    modalBox.appendChild(modal);
    document.getElementsByTagName("body")[0].appendChild(modalBox);
    modalBox.addEventListener("click", closeCallback, false);
    return modalBox;
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

function saveEvent(){
    console.log("Saving event");
}

function registerUser(){
    let username = document.getElementById("register-username");
    if(username !== null){ username = username.value; }else{ return; }
    let password = document.getElementById("register-password");
    if(password !== null){ password = password.value; }else{ return; }
    let cpassword = document.getElementById("register-cpassword");
    if(cpassword !== null){ cpassword = cpassword.value; }else{ return; }
    console.log("Registering");
    request(function(r){
        if(r.status === "success"){
            console.log("SUCCESSFUL REGISTRATION");
            closeRegistrationModal({"target":"manual"});
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

function closeEventPopup(evt){
    evt.type = "event";
    closeModal(evt);
}

function closeRegistrationModal(evt){
    evt.type = "registration";
    closeModal(evt);
}

function closeModal(evt){
    if(evt.target === document.getElementsByClassName("modal-box")[0] || evt.target==="manual"){
        switch(evt.type){
            case "registration":
                $(".registration-input").off("change", verifyRegistrationInputs);
                document.getElementById("submit-registration").removeEventListener("click", registerUser);
                break;
            case "event":
                $(".event-input").off();
                document.getElementById("save-event").removeEventListener("click", saveEvent);
                break;
        }
        let obj = document.getElementsByClassName("modal-box")[0];
        console.log(obj);
        $(".modal-box").off();
        obj.parentNode.removeChild(obj);
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
    updateCalendar(currentMonth);
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