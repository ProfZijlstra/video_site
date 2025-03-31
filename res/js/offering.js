window.addEventListener("load", () => {
    document.getElementById("days").onclick = function(e) {
        if (e.target.classList.contains("data")) {
            e.target.querySelector("a").click();
        }
    }

    // beyond here is instructor / admin code
    const showEditButton = document.querySelector(".tools i.far.fa-edit");
    if (!showEditButton) {
        return;
    }

    // edit dialog
    showEditButton.onclick = function() {
        const editIcons = document.querySelectorAll("div#days i.far.fa-edit");
        editIcons.forEach((edit) => {
            edit.classList.toggle("hide");
        });
    };
    let dayId = null;
    function showEditDialog() {
        dayId = this.closest("div.data").id; // Like W1D1
        const day_id = this.dataset.day_id;
        const desc = this.previousElementSibling.innerText;
        document.getElementById("day_id").value = day_id;
        document.getElementById("day_desc").value = desc;
        document.getElementById("editDialog").showModal();
    }
    const editIcons = document.querySelectorAll("div#days i.far.fa-edit");
    editIcons.forEach((edit) => {
        edit.onclick = showEditDialog;
    });
    document.getElementById("edit").onclick = function() {
        editIcons.forEach((edit) => {
            edit.classList.toggle("hide");
        });
    }
    document.getElementById("closeEditDialog").onclick = function() {
        document.getElementById("editDialog").close();
    }
    document.getElementById("editForm").onsubmit = function() {
        const day_id = document.getElementById("day_id").value;
        const day_desc = document.getElementById("day_desc").value;
        const data = new FormData();
        data.append("day_id", day_id);
        data.append("desc", day_desc);
        fetch("edit", {
            method: "POST",
            body: data,
        }).then(response => {
            if (!response.ok) {
                alert("Edit title failed");
                return;
            }
            const div = document.getElementById(dayId);
            div.querySelector("span.text").innerText = day_desc;
        });
        document.getElementById("editDialog").close();
        return false;
    };
});
