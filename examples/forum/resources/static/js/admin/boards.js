for (let btn of document.getElementsByClassName("delete-board-btn")) {
    btn.addEventListener("click", () => {
        fetch("/api/boards/" + btn.dataset.boardId, {
            method: "DELETE"
        }).then(res => {
            if (res.ok) {
                window.location.reload();
            } else {
                alert("Ett fel uppstod!");
            }
        })
    })
}