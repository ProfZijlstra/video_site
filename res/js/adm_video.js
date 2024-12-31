window.addEventListener('load', () => {
    let editLink = null;

    // enable video configuration when clicking config button
    document.getElementById("config-btn").onmousedown = function() {
        const videoLinks = document.querySelectorAll(".video_link");
        for (const div of videoLinks) {
            div.classList.toggle("config");
        }
        document.getElementById("back").classList.toggle("config");
        document.querySelector("article.selected .media")
            .classList.toggle("hide");
        document.querySelector("article.selected .media.upload")
            .classList.toggle("hide");
    }

    // enable clicking plus to show add  dialog
    document.getElementById("add_part").onmousedown = function() {
        document.getElementById("addDialog").showModal();
        document.getElementById("addTitle").focus();
    };
    document.getElementById("closeAdd").onmousedown = function() {
        document.getElementById("addDialog").close();
    }

    // enable clicking edit to show edit dialog
    function editLessonPart() {
        const parent = this.closest(".video_link");
        editLink = parent;

        const title = parent.querySelector("a").innerText;
        const file = parent.querySelector(".config").dataset.file;
        document.getElementById("editDialog").showModal();
        const editTitle = document.getElementById("editTitle");
        editTitle.value = title;
        editTitle.dataset.file = file;
    }
    document.querySelectorAll(".video_link i.fa-pen-to-square").forEach(
        e => e.onmousedown = editLessonPart
    );
    function closeEditDialog() {
        document.getElementById("editDialog").close();
    }
    document.getElementById("closeEdit").onmousedown = closeEditDialog;

    // submit edit should:
    // 1. send to server
    // 2. update the tab
    // 3. update the article title
    //
    // .onclick is used so that enter inside the textfield also triggers
    document.getElementById("editBtn").onclick = function(evt) {
        evt.preventDefault();

        const editTitle = document.getElementById("editTitle");
        const title = editTitle.value;
        const file = editTitle.dataset.file;
        if (title.indexOf("_") !== -1) {
            alert("Title cannot not contain underscores.\n"
                + "Please remove the underscore and try again");
            return;
        }
        closeEditDialog();

        const data = new FormData();
        data.append("title", title);
        data.append("file", file);
        fetch('title', {
            method: "POST",
            body: data,
        })
            .then(response => {
                if (!response.ok) {
                    alert("Updating title failed");
                    return;
                }

                // change title on page
                const tab = editLink.querySelector("a");
                tab.innerText = title;
                const id = "a" + editLink.id;
                const hdr = document.querySelector(`#${id} h2`);
                hdr.innerText = title;
                const config = tab.parentNode.querySelector(".config");
                const parts = file.split('_');
                parts[1] = title;
                config.dataset.file = parts.join('_');
            })
            .catch(e => alert(e));
    }
});
