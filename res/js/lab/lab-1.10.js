window.addEventListener("load", () => {
    COUNTDOWN.start(() => window.location.reload());

    // markdown related functions
    function mdToggle() {
        sendDeliverable.call(this);
    }
    MARKDOWN.enablePreview("../markdown", false, true);
    MARKDOWN.activateButtons(mdToggle);

    // user_id if present
    const user_id = document.getElementById("user_id")?.value;

    // automatically save changes to deliverables
    document.querySelectorAll("input, select, textarea").forEach(input => {
        input.addEventListener("change", sendDeliverable);
    });

    // code for sending deliverable data
    const lab_id = document.getElementById("lab_id").dataset.id;
    const group = document.getElementById("labGroup")?.dataset.id;
    let submission_id = document.getElementById("submission").dataset.id;
    function sendDeliverable() {
        if (this.classList.contains("fileUpload")) {
            return; // separate event listner for files below
        }
        const delivery = this.closest("div.delivery");
        const deliverable = delivery.previousElementSibling;
        const type = deliverable.parentNode.dataset.type;
        const durSel = delivery.querySelector("select.duration");
        const comSel = delivery.querySelector("select.completion");
        const completion = comSel.value;
        const duration = durSel.value;

        let data = "";
        data += "submission_id=" + submission_id;
        data += "&deliverable_id=" + deliverable.dataset.id;
        data += "&completion=" + completion;
        data += "&duration=" + duration;
        if (group) {
            data += "&group=" + group;
        }
        const stuComment = delivery.querySelector("textarea.cmt").value;
        if (stuComment) {
            const stuShifted = encodeURIComponent(MARKDOWN.ceasarShift(stuComment));
            data += "&stuComment=" + stuShifted;
            const stuMD = delivery.querySelector("i.cmt").classList.contains("active") ? 1 : 0;
            data += "&stuCmntHasMD=" + stuMD;
        }

        let check = true;
        if (type == "txt") {
            const md = delivery.querySelector("i.txt").classList.contains("active") ? 1 : 0
            const text = delivery.querySelector("textarea.txt").value;
            const shifted = encodeURIComponent(MARKDOWN.ceasarShift(text));
            data += "&hasMarkDown=" + md;
            data += "&text=" + shifted;
            check = text ? true : false;
        } else if (type == "url") {
            const url = delivery.querySelector("input.url").value;
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

        const delivery_id = delivery.dataset.id;
        let url = `${lab_id}/${type}`;
        let method = "POST";
        let action = "Creating";
        if (delivery_id) {
            url += `/${delivery_id}`;
            method = "PUT";
            action = "Updating";
        }
        if (user_id) {
            url += `?student=${user_id}`;
        }
        if (window.localStorage.view == "multi") {
            url = "../" + url;
        }

        fetch(url, {
            method: method,
            body: data,
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            }
        })
            .then(response => {
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
                delivery.dataset.id = data.id;
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
        const delivery = this.closest("div.delivery");
        const deliverable = delivery.previousElementSibling;
        const type = deliverable.parentNode.dataset.type;
        const delivery_id = delivery.dataset.id;
        const data = new FormData();
        const durSel = delivery.querySelector("select.duration");
        const comSel = delivery.querySelector("select.completion");
        const completion = comSel.value;
        const duration = durSel.value;
        const stuComment = delivery.querySelector("textarea.cmt").value;

        const spinner = delivery.querySelector("i.spinner");
        const check = delivery.querySelector("span.check");
        const upload = delivery.querySelector("i.upload");
        const trash = delivery.querySelector("i.fa-trash-can");
        check.classList.remove("show");

        data.append("submission_id", submission_id);
        data.append("deliverable_id", deliverable.dataset.id);
        data.append("delivery_id", delivery_id);
        data.append("completion", completion);
        data.append("duration", duration);
        data.append("file", this.files[0]);
        if (group) {
            data.append("group", group);
        }

        if (stuComment) {
            const stuShifted = MARKDOWN.ceasarShift(stuComment);
            data.append("stuComment", stuShifted);
            const stuMD = delivery.querySelector("i.cmt").classList.contains("active") ? 1 : 0;
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
        let url = `${lab_id}/${type}/file`;
        if (user_id) {
            url += `?student=${user_id}`;
        }
        if (window.localStorage.view == "multi") {
            url = "../" + url;
        }
        fetch(url, {
            method: "POST",
            body: data,
        })
            .then(response => {
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
                        delivery.querySelectorAll('.zipCheck').forEach(check => {
                            check.classList.remove("error");
                        });

                        // set failure highlights
                        data.failed.forEach(fail => {
                            const failDiv = delivery.querySelector(`#c${fail}`);
                            failDiv.classList.add("error");
                        });
                    } else {
                        throw new Error(data.error);
                    }
                } else {
                    check.classList.add("show");
                }
                submission_id = data.submission_id;
                delivery.dataset.id = data.id;
                spinner.classList.remove("rotate");
                upload.setAttribute("title", `Replace ${type}`);
                const link = delivery.querySelector("a.fileLink");
                link.setAttribute("href", data.file);
                link.textContent = data.name;
                trash.dataset.id = data.id;
                trash.classList.remove("hide");

                if (type == "img") {
                    const img = delivery.querySelector("img");
                    img.setAttribute("src", data.file);
                    img.classList.add("show");
                } else if (type == "zip") {
                    const listing = delivery.querySelector(".listing");
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

    // Hook up the delete functions
    document.querySelectorAll("i.fa-trash-can").forEach(trash => {
        trash.addEventListener("click", deleteFile);
    });
    function deleteFile() {
        const id = this.dataset.id;
        url = `${lab_id}/delivery/${id}`;
        if (window.localStorage.view == "multi") {
            url = '../' + url;
        }
        fetch(url, {
            method: "DELETE",
        })
            .then(response => {
                if (!response.ok) {
                    alert("Deleting file failed.");
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    return;
                }
                // remove the file from the DOM
                const parent = this.closest("div.fileContainer");
                const link = parent.querySelector("a.fileLink");
                link.removeAttribute("href");
                link.textContent = "";
                this.classList.add("hide");

                // remove the previous delivery id
                this.closest("div.delivery").dataset.id = "";

                // for images, remove the image
                const img = parent.querySelector("img.answer");
                if (img) {
                    img.removeAttribute("src");
                    img.classList.remove("show");
                    img.classList.add("hide");
                    img.dataset.id = "";
                }
                // for zip files, remove the listing
                const listing = parent.querySelector("div.listing");
                if (listing) {
                    listing.innerHTML = "";
                }
            });
    }
});
