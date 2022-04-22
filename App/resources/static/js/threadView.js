const editorConfig = {toolbar: ['heading', '|', 'bold', 'italic', 'bulletedList', 'numberedList', 'blockQuote']};

ClassicEditor
    .create(document.querySelector("#createEditor"), editorConfig)
    .then(() => {
        const createArea = document.querySelector("#createPostArea");

        if(createArea != null)
            createArea.style.display = "block";
    })
    .catch(error => {
        console.error(error);

    });

let currentEditPostElement;
let currentEditingPost;
let editEditor;

const cancelEditBtn = document.querySelector("#cancelEditBtn");
if (cancelEditBtn != null) {
    ClassicEditor
        .create(document.querySelector("#editEditor"), editorConfig)
        .then(editor => {
            editEditor = editor;
            document.querySelector("#createPostArea").style.display = "block";
        })
        .catch(error => {
            console.error(error);
        });

    cancelEditBtn.addEventListener("click", cancelEdit);

    // Dessa element skickas ej då användaren visar sidan "Skapa ny tråd"
    document.querySelector("#editPostBtn").addEventListener("click", function () {
        fetch("/api/posts/" + currentEditingPost, {
            method: "PUT",
            body: editEditor.getData()
        }).then(res => {
            if (res.status === 200) {
                window.location.reload();
            } else {
                alert("Ett fel uppstod!");
            }
        });
    });
}

const removeThreadBtn = document.querySelector("#removeThreadBtn");
if (removeThreadBtn != null) {
    removeThreadBtn.addEventListener("click", deleteThread);
}

async function deleteThread() {
    let res = await fetch("/api/threads/" + removeThreadBtn.dataset.threadId, {
        method: "delete"
    });

    if (res.ok) {
        window.location.href = "/boards/" + removeThreadBtn.dataset.boardId;
    } else {
        alert("Ett fel uppstod!");
    }
}

function cancelEdit() {
    currentEditPostElement.style.backgroundColor = "white";
    currentEditPostElement.querySelector(".postData").style.display = "block";
    currentEditPostElement = null;

    const editArea = document.querySelector("#editPostArea");
    editArea.style.display = "none";
    document.querySelector("body").appendChild(editArea);
}

for (const deleteButton of document.querySelectorAll(".deletePostButton")) {
    deleteButton.addEventListener("click", function () {
        fetch("/api/posts/" + deleteButton.dataset.postId, {
            method: "DELETE"
        }).then(res => {
            if (res.ok) {
                window.location.reload();
            } else {
                alert("Ett fel uppstod!");
            }
        })
    });
}

for (const editButton of document.querySelectorAll(".updatePostButton")) {
    editButton.addEventListener("click", function () {
        if (currentEditPostElement != null)
            cancelEdit();

        const postElement = editButton.parentElement.parentElement.parentElement;
        postElement.scrollIntoView();
        postElement.style.backgroundColor = "orange";
        postElement.querySelector(".postData").style.display = "none";
        editEditor.setData(postElement.querySelector(".postContent").innerHTML);

        const editArea = document.querySelector("#editPostArea");
        editArea.style.display = "block";
        postElement.appendChild(editArea);

        currentEditingPost = editButton.dataset.postId;
        currentEditPostElement = postElement;
    });
}