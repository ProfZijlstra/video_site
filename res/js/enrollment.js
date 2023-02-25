window.addEventListener("load", () => {
    // remove individual student
    function unenroll(evt) {
        const uid = evt.target.dataset.uid;
        document.getElementById("removeUid").value = uid;
        const parent = evt.target.parentElement.parentElement;
        const sid = parent.querySelector(".studentID").textContent;
        const name = parent.querySelector(".name").textContent;
        if (confirm(`Remove student ${sid} ${name}?`)) {
            document.forms["removeStudent"].submit();
        }
        return false;
    }
    const removes = document.querySelectorAll(".fa-trash-can");
    for (const remove of removes) {
        remove.addEventListener('click', unenroll);
    }

    // show upload class list modal
    const overlay = document.getElementById("overlay");
    document.getElementById("upload").addEventListener("click", () => {
        overlay.classList.add("visible");
        document.getElementById("upload_modal").classList.remove("hide");
    });

    // show enroll user modal
    document.getElementById("addUser").addEventListener("click", () => {
        overlay.classList.add("visible");
        document.getElementById("enroll_modal").classList.remove("hide");
    });

    // hide overlay and any/all modal(s)
    function hide() {
        overlay.classList.remove("visible");
        const modals = document.querySelectorAll(".modal");
        for (const modal of modals) {
            modal.classList.add("hide");
        }
    }
    document.getElementById("close-overlay").onclick = hide;
    document.getElementById("overlay").onclick = function (evt) {
        if (evt.target == this) {
            hide();
        }
    };

    // validate the upload form before submit
    document.getElementById("upload_form").onsubmit = () => {
        const file = document.getElementById("list_file");
        if (!file.value) {
            return false;
        }
    };
});
