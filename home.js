var lengthOfWeek = 7;
var csrf = "";

document.addEventListener("DOMContentLoaded", function(){
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
}, false);

/*
 * Set the csrf variable to the value inside the hidden div
 * with id "csrf" so that we can send requests with a csrf
 * token.
 */
function setCsrf(){
    csrf = document.getElementById("csrf").innerText;
}