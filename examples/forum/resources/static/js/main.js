const logoutBtn = document.querySelector("#logoutBtn");
function showStatusMessage(messages) {
    const message = messages[new URLSearchParams(window.location.search).get("status")];
    if(message != null)
        alert(message);
}

if(logoutBtn != null) {
    logoutBtn.addEventListener("click", () => {
        for (let btn of document.getElementsByName("logout"))
            btn.submit();
    });
}