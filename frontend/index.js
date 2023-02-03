"use strict";

function sendLoginRequest(form) {
  const formData = Object.fromEntries(new FormData(form).entries());
  const { type, username, password } = formData;
  const request = new XMLHttpRequest();
  request.open("POST", "login.php", true);
  request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

  request.onreadystatechange =  (res,_) => {
    const response = res.target;
    if (response.readyState == 4 && response.status == 200) {
       handleLoginResponse(JSON.parse(response.responseText));
    }
  };
  request.send(`type=${type}&username=${username}&password=${password}`);
  return true;
}

function handleLoginResponse(response) {
  const text = response;
  document.getElementById(
    "textResponse"
  ).innerHTML = `<h1>response: ${text}</h1>`;
}
