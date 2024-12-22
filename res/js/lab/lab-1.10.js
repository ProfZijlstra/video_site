window.addEventListener("load", () => {
    // global state in this module
    let delivId = 1;

    COUNTDOWN.start(() => window.location.reload());

    // markdown related functions
    function mdToggle() {
        sendDeliverable.call(this);
    }
    MARKDOWN.enablePreview("../markdown");
    MARKDOWN.activateButtons(mdToggle);

    // user_id if present
    const user_id = document.getElementById("user_id")?.value;

    // automatically save changes to deliverables
    document.querySelectorAll("input, select, textarea").forEach(input => {
        input.addEventListener("change", sendDeliverable);
    });


    /**
     * Big section of Single Page Application navigation logic
     */

    // aquire emelents onto which we're going to hook
    const multiPageBtn = document.getElementById('multiPage');
    const singlePageBtn = document.getElementById('singlePage');
    const keyShortCuts = document.getElementById("keyShortCuts");
    const multiHeader = document.querySelector("h2.multi");
    const singleHeader = document.querySelector("h2.single");
    const backLink = document.getElementById("back");
    const navBack = document.querySelectorAll("nav.back")[1];
    const delivBtns = document.querySelectorAll('span.delivNum');
    const delivs = document.querySelectorAll('div.deliverables');
    const chevLeft = document.getElementById("chevLeft");
    const chevRight = document.getElementById("chevRight");

    // helper functions to work with the URL
    function urlNoDelivNum() {
        const url = window.location + "";
        const i = url.lastIndexOf('/');
        return url.substring(0, i);
    }

    // actual switching logic starts here
    function toSpa(e, hist = true) {
        multiPageBtn.classList.add('hide');
        singlePageBtn.classList.remove('hide');
        multiHeader.classList.remove('hide');
        singleHeader.classList.add('hide');
        backLink.setAttribute("href", "../../lab");
        navBack.classList.add('hide');
        keyShortCuts.classList.remove('hide');
        switchDeliv(1);
        window.localStorage.setItem("view", "multi");

        if (!hist) {
            return;
        }
        window.history.pushState({"id": 1}, '', window.location + "/1");
    };
    multiPageBtn.onmousedown = toSpa;

    function fromSpa(e, hist = true) {
        multiPageBtn.classList.remove('hide');
        singlePageBtn.classList.add('hide');
        multiHeader.classList.add('hide');
        singleHeader.classList.remove('hide');
        backLink.setAttribute("href", "../lab");
        navBack.classList.remove('hide');
        keyShortCuts.classList.add('hide');
        delivs.forEach(e => e.classList.remove('hide'));
        window.localStorage.setItem("view", "single");

        if (!hist) {
            return;
        }
        window.history.pushState(null, '', urlNoDelivNum());
    };
    singlePageBtn.onmousedown = fromSpa;

    function switchDeliv(id) {
        delivId = parseInt(id);
        if (id == "1") {
            chevLeft.classList.remove("active");
        } else {
            chevLeft.classList.add("active");
        }
        const elem = document.getElementById("db" + id);
        if (elem.nextElementSibling == chevRight) {
            chevRight.classList.remove("active");
        } else {
            chevRight.classList.add("active");
        }
        const buttonId = "db" + id;
        const deliverableId = "d" + id;
        for (const db of delivBtns) {
            if (db.id == buttonId) {
                db.classList.add('active');
            } else {
                db.classList.remove('active');
            }
        }
        for (const d of delivs) {
            if (d.id == deliverableId) {
                d.classList.remove('hide');
                d.querySelector('select').focus();
            } else {
                d.classList.add('hide');
            }
        }
    }
    function clickDeliv() {
        const id = this.textContent;
        switchDeliv(id);
        window.history.pushState({"id": id}, '', urlNoDelivNum() + '/'+id);
    }
    delivBtns.forEach(e => e.onmousedown = clickDeliv);

    function goClickDeliv(id) {
        const elem = document.getElementById("db" + id);
        if (!elem) {
            return;
        }
        elem.onmousedown();
    }
    chevLeft.onmousedown = function() {
        goClickDeliv(delivId - 1);
    };
    chevRight.onmousedown = function() {
        goClickDeliv(delivId + 1);
    };

    // make browser back button work properly
    window.addEventListener('popstate', (e) => {
        const state = e.state;
        if (state && state.id) {
            toSpa(null, false);
            switchDeliv(state.id);
        } else {
            fromSpa(null, false);
        }
    });

    // switch to SPA if user preference indicates it
    const view = window.localStorage.getItem("view");
    const selected = document.getElementById("submission").dataset.selected;
    if (view && view == "multi" && !selected) {
        toSpa();
    }

    // keyboard shortcuts in SPA mode
    document.addEventListener('keydown', (e) => {
        if (window.localStorage.getItem("view") != "multi") {
            return;
        }

        switch(e.code) {
            case "Period":
                if (!e.ctrlKey) {
                    return;
                }
                goClickDeliv(delivId + 1);
            break
            case "Comma":
                if (!e.ctrlKey) {
                    return;
                }
                goClickDeliv(delivId - 1);
            break;
        }
    });

    // code for sending deliverable data
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
        if (user_id) {
            url += `?student=${user_id}`;
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
        const trash = deliv.querySelector("i.fa-trash-can");
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
            const stuShifted = MARKDOWN.ceasarShift(stuComment);
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
        let url = `${lab_id}/${type}/file`;
        if (user_id) {
            url += `?student=${user_id}`;
        }
        fetch(url, {
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
                    deliv.querySelectorAll('.zipCheck').forEach(check => {
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
            trash.dataset.id = data.id;
            trash.classList.remove("hide");

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

    // Hook up the delete functions
    document.querySelectorAll("i.fa-trash-can").forEach(trash => {
        trash.addEventListener("click", deleteFile);
    });
    function deleteFile() {
        const id = this.dataset.id;
        url = `${lab_id}/delivery/${id}`;
        fetch(url, {
            method: "DELETE",
        })
        .then(response =>  {
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
            this.closest("div.deliv").dataset.id = "";

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
