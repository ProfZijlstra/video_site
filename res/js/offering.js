window.addEventListener("load", () => {

    // show and hide edit icons
    function toggleIcons() {
        document.querySelectorAll("div#days i.far.fa-edit, div#days i.fa-regular.fa-square-plus")
            .forEach((edit) => {
                edit.classList.toggle("hide");
            });
    }
    document.getElementById("edit").onclick = function() {
        toggleIcons();
        const config = window.sessionStorage.getItem("config");
        window.sessionStorage.setItem("config", config === "true" ? "false" : "true");
    }
    if (window.sessionStorage.getItem("config") === "true") {
        toggleIcons();
    }

    // edit dialog
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
    function showAddDialog() {
        document.getElementById("add_modal").showModal();

        const day_id = this.parentNode.dataset.day_id;
        const date = this.parentNode.dataset.date;
        const next = this.parentNode.dataset.next;

        document.getElementById('quiz_day_id').value = day_id;
        document.getElementById('lab_day_id').value = day_id;
        document.getElementById('quiz_startdate').value = date;
        document.getElementById('lab_startdate').value = date;
        document.getElementById('quiz_stopdate').value = date;
        document.getElementById('lab_stopdate').value = next;
        document.getElementById('add_modal').showModal();
    }
    document.getElementById("closeAddDialog").onclick = function() {
        document.getElementById("add_modal").close();
    };
    const addIcons = document.querySelectorAll("i.fa-regular.fa-square-plus");
    addIcons.forEach((add) => {
        add.onclick = showAddDialog;
    });
    document.getElementById("add_select").onchange = function() {
        document.querySelectorAll("#add_modal form").forEach(
            (form) => form.classList.toggle("hide")
        )
    };
});
