window.addEventListener("load", () => {
    COUNTDOWN.start(() => window.location.reload());

    // markdown related functions
    function mdToggle() {
        sendDeliverable.call(this);
    }
    MARKDOWN.enablePreview("../markdown");
    MARKDOWN.activateButtons(mdToggle);

    // automatically save changes to deliverables
    document.querySelectorAll("input, select, textarea").forEach(input => {
        input.addEventListener("change", sendDeliverable);
    });

    const lab_id = document.getElementById("lab_id").dataset.id;
    const group = document.getElementById("labGroup")?.dataset.id;
    let submission_id = document.getElementById("submission").dataset.id;
    function sendDeliverable() {
        if (this.classList.contains("fileUpload")) {
            return; // separate event listner for files below
        }
        const deliv = this.closest("div.deliv");
        const type = deliv.parentNode.dataset.type;
        const durSel = deliv.querySelector("select.duration");
        const comSel = deliv.querySelector("select.completion");
        const completion = comSel.value;
        const duration = durSel.value;

        let data = "";
        data += "submission_id=" + submission_id;
        data += "&deliverable_id="+ deliv.parentNode.dataset.id;
        data += "&completion=" + completion;
        data += "&duration=" + duration;
        if (group) {
            data += "&group=" + group;
        }
        const stuComment = deliv.querySelector("textarea.cmt").value;
        if (stuComment) {
            const stuShifted = encodeURIComponent(MARKDOWN.ceasarShift(stuComment));
            data += "&stuComment=" + stuShifted;
            const stuMD = deliv.querySelector("i.cmt").classList.contains("active") ? 1 : 0;
            data += "&stuCmntHasMD=" + stuMD;
        }

        let check = true;
        if (type == "txt") {
            const md = deliv.querySelector("i.txt").classList.contains("active") ? 1 : 0
            const text = deliv.querySelector("textarea.txt").value;
            const shifted = encodeURIComponent(MARKDOWN.ceasarShift(text));
            data += "&hasMarkDown=" + md;
            data += "&text=" + shifted;
            check = text ? true : false;
        } else if (type == "url") {
            const url = deliv.querySelector("input.url").value;
            data += "&url=" + encodeURIComponent(url);
            check = url ? true : false;
        } // all other types are files (and have own event listener)

        // alert if duration and completion are not set 
        if ((check || stuComment) 
            && (duration == "00:00" || completion == "0")
            && !this.classList.contains("duration") 
            && !this.classList.contains("completion")) {
            alert("Please set duration and completion before continuing.");
            if (duration == "00:00") {
                durSel.focus();
            } else {
                comSel.focus();
            }
        }

        const id = deliv.dataset.id;
        let url = `${lab_id}/${type}`;
        let method = "POST";
        let action = "Creating";
        if (id) {
            url += `/${id}`;
            method = "PUT";
            action = "Updating";
        }         

        fetch(url, {
            method: method,
            body: data,
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            }
        })
        .then(response =>  {
            if (!response.ok) {
                throw new Error(action + " deliverable failed.");
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            submission_id = data.submission_id;
            deliv.dataset.id = data.id;
        })
        .catch(error => {
            alert(error);
        });
    }

    // hook up icon click to file input
    document.querySelectorAll("i.upload").forEach(icon => {
        icon.addEventListener("click", function() {
            this.parentNode.querySelector("input.file").click();
        });
    });
    // hook up file change to sendFile
    document.querySelectorAll("input.file").forEach(file => {
        file.addEventListener("change", sendFile);
    });
    function sendFile() {
        const deliv = this.closest("div.deliv");
        const type = deliv.parentNode.dataset.type;
        const id = deliv.dataset.id;
        const data = new FormData();
        const durSel = deliv.querySelector("select.duration");
        const comSel = deliv.querySelector("select.completion");
        const completion = comSel.value;
        const duration = durSel.value;
        const stuComment = deliv.querySelector("textarea.cmt").value;

        const spinner = deliv.querySelector("i.spinner");
        const check = deliv.querySelector("span.check");
        const upload = deliv.querySelector("i.upload");
        check.classList.remove("show");

        data.append("submission_id", submission_id);
        data.append("deliverable_id", deliv.parentNode.dataset.id);
        data.append("delivery_id", id);
        data.append("completion", completion);
        data.append("duration", duration);
        data.append("file", this.files[0]);
        if (group) {
            data.append("group", group);
        }

        if (stuComment) {
            const stuShifted = encodeURIComponent(MARKDOWN.ceasarShift(stuComment));
            data.append("stuComment", stuShifted);
            const stuMD = deliv.querySelector("i.cmt").classList.contains("active") ? 1 : 0;
            data.append("stuCmntHasMD", stuMD);
        }

        // alert if duration and completion are not set 
        if ((duration == "00:00" || completion == "0")
            && !this.classList.contains("duration") 
            && !this.classList.contains("completion")) {
            alert("Please set duration and completion before continuing.");
            if (duration == "00:00") {
                durSel.focus();
            } else {
                comSel.focus();
            }
        }

        spinner.classList.add("rotate");
        fetch(`${lab_id}/${type}/file`, {
            method: "POST",
            body: data,
        })
        .then(response =>  {
            if (!response.ok) {
                throw new Error("Uploading file failed.");
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                if (data.failed) {
                    alert(data.error);
                    // reset failure highlights
                    deliv.querySelectorAll('zipCheck').forEach(check => {
                        check.classList.remove("error");
                    });

                    // set failure highlights
                    data.failed.forEach(fail => {
                        const failDiv = deliv.querySelector(`#c${fail}`);
                        failDiv.classList.add("error");
                    });
                } else {
                    throw new Error(data.error);
                }
            }
            submission_id = data.submission_id;
            deliv.dataset.id = data.id;
            spinner.classList.remove("rotate");
            upload.setAttribute("title", `Replace ${type}`);
            const link = deliv.querySelector("a.fileLink");
            link.setAttribute("href", data.file);
            link.textContent = data.name;
            check.classList.add("show");

            if (type == "img") {
                const img = deliv.querySelector("img");
                img.setAttribute("src", data.file);
                img.classList.add("show");
            } else if (type == "zip") {
                const listing = deliv.querySelector(".listing");
                listing.innerHTML = data.text;
            }
        })
        .catch(error => {
            alert(error);
            spinner.classList.remove("rotate");
        });
    }

    // Hook up the camera functions
    CAMERA.init(`${lab_id}`); 
});
