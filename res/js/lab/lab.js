window.addEventListener("load", () => {
    COUNTDOWN.start(() => window.location.reload());

    // markdown related functions
    function mdToggle() {
        sendDeliverable.call(this);
    }
    MARKDOWN.enablePreview("../markdown");
    MARKDOWN.activateButtons(mdToggle);

    document.querySelectorAll("input, select, textarea").forEach(input => {
        input.addEventListener("change", sendDeliverable);
    });

    const lab_id = document.getElementById("lab_id").dataset.id;
    const group = document.getElementById("labGroup")?.dataset.id;
    let submission_id = document.getElementById("submission").dataset.id;
    function sendDeliverable() {
        const deliv = this.closest("div.deliv");
        const type = deliv.parentNode.dataset.type;
        const durSel = deliv.querySelector("select.duration");
        const comSel = deliv.querySelector("select.completion");
        const completion = comSel.value;
        const duration = durSel.value;

        let data = "";
        data += "submission_id=" + submission_id;
        data += "&deliverable_id="+ deliv.parentNode.dataset.id;
        data += "&group=" + group;
        data += "&completion=" + completion;
        data += "&duration=" + duration;
        const stuComment = deliv.querySelector("textarea.cmt").value;
        if (stuComment) {
            const stuShifted = encodeURIComponent(MARKDOWN.ceasarShift(stuComment));
            data += "&stuComment=" + stuShifted;
            const stuMD = deliv.querySelector(".fa-markdown.cmt").classList.contains("active") ? 1 : 0;
            data += "&stuCmntHasMD=" + stuMD;
        }

        if (type == "txt") {
            const md = deliv.querySelector(".fa-markdown.txt").classList.contains("active") ? 1 : 0
            const text = deliv.querySelector("textarea.txt").value;
            const shifted = encodeURIComponent(MARKDOWN.ceasarShift(text));
            data += "&hasMarkDown=" + md;
            data += "&text=" + shifted;

            // don't submit if duration and completion are not set 
            if ((text || stuComment) 
                && (duration == "00:00" || completion == "0")
                && !this.classList.contains("duration") 
                && !this.classList.contains("completion")) {
                alert("Please set duration and completion before continuing.");
                if (duration == "00:00") {
                    durSel.focus();
                } else {
                    comSel.focus();
                }
                return;
            }
        } else if (type == "url") {
            const url = deliv.querySelector("input.url").value;
            data += "&url=" + encodeURIComponent(url);

            if ((url || stuComment) 
                && (duration == "00:00" || completion == "0")
                && !this.classList.contains("duration") 
                && !this.classList.contains("completion")) {
                alert("Please set duration and completion before continuing.");
                if (duration == "00:00") {
                    durSel.focus();
                } else {
                    comSel.focus();
                }
                return;
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
            // TODO: show updated timestamp
            submission_id = data.submission_id;
            deliv.dataset.id = data.id;
        })
        .catch(error => {
            alert(error);
        });
    }
});
