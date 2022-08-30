function deleteUser(userId){
    fetch("/api/users/" + userId, {
        method: "DELETE"
    }).then(res => {
        if (res.ok) {
            window.location.reload();
        } else {
            alert("Ett fel uppstod!");
        }
    });
}

function saveUserUpdate(formElement, userId){
    if (formElement == null) {
        alert("Du har inte tillåtelse att ändra denna användare!");
        return;
    }

    let formData = new FormData(formElement);
    let object = {};
    formData.forEach((value, key) => object[key] = value);

    fetch("/api/users/" + userId, {
        method: "PUT",
        body: JSON.stringify(object)
    }).then(res => {
        if (res.ok) {
            window.location.reload();
        } else {
            alert("Ett fel uppstod när användaren skulle sparas!");
        }
    });
}

for (let deleteBtn of document.getElementsByClassName("deleteBtn")) {
    deleteBtn.addEventListener("click", () => deleteUser(deleteBtn.dataset.userId));
}

for (let saveBtn of document.getElementsByClassName("saveBtn")) {
    saveBtn.addEventListener("click", () => saveUserUpdate(saveBtn.parentElement, saveBtn.dataset.userId));
}

for (let editDiv of document.getElementsByClassName("editUser")){
    editDiv.addEventListener("keydown", (e) => {
        if(e.keyCode === 13){
            const saveBtn = editDiv.querySelector(".saveBtn");
            if(saveBtn != null)
                saveBtn.click();
        }
    })
}

for (let roleSelect of document.getElementsByClassName("roleSelect")) {
    for (let option of roleSelect.options) {
        if (option.value === roleSelect.dataset.value) {
            option.setAttribute("selected", "selected");
        }
    }
}