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
    $(".create-event-button").on("click", createEventPopup);
    $(".logout-button").on("click", logout);
    getUsername();
}, false);

function createEventPopup(){
    console.log("Creating event popup");
}

function getUsername(){
    request(function(r){
        try{
            user_id = parseInt(r.user_id);
            username = r.username;
        }catch(e){
            console.log(e);
            username = "";
            user_id = -1;
        }
    }, {"action":"get-username"});
}

function logout(){
    request(function(r){
        console.log(r);
    }, {"action":"logout"});
}

function loadContent(){

}

function request(callback, params={}){
    let xhr = new XMLHttpRequest();
    xhr.addEventListener("load", function(evt){
        console.log(evt.target.responseText);
        let r = JSON.parse(evt.target.responseText);
        if(r.status === "success"){
            callback(r);
        }else{
            console.log(r.type);
        }
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