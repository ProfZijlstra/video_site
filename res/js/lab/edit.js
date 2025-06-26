window.addEventListener("load", () => {
    function htmlOrError(error) {
        return function responseToHTML(response) {
            if (response.ok) {
                return response.text();
            } else {
                throw new Error(error);
            }
        }
    }
    function alertError(error) {
        alert(error);
    }
    // auto update on detail change
    function updateDetails() {
        const form = document.forms.updateLab;
        const id = form.dataset.id;
        const visible = form.visible.checked ? 1 : 0;
        const name = encodeURIComponent(MARKDOWN.ceasarShift(form.name.value));
        const day_id = form.day_id.value;
        const startdate = form.startdate.value;
        const starttime = form.starttime.value;
        const stopdate = form.stopdate.value;
        const stoptime = form.stoptime.value;
        const type = form.type.value;

        fetch(`../${id}`, {
            method: "PUT",
            body: `visible=${visible}&name=${name}&day_id=${day_id}&startdate=${startdate}&starttime=${starttime}&stopdate=${stopdate}&stoptime=${stoptime}&type=${type}`,
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
        });
    }
    const form = document.forms.updateLab;
    const inputs = form.querySelectorAll("input, select, textarea");
    inputs.forEach(input => {
        input.addEventListener("change", updateDetails);
    });

    // markdown related functions
    function mdToggle() {
        if (this.classList.contains("details")) {
            updateDetails();
        } else if (this.classList.contains("deliverable")) {
            updateDeliv.call(this);
        }
    }
    // enable markdown previews
    MARKDOWN.enablePreview("../../markdown");
    MARKDOWN.activateButtons(mdToggle);

    // enable delete button
    document.getElementById("delBtn").addEventListener("click", () => {
        // server will check if there are any submissions
        if (confirm("Are you sure you want to delete this lab?")) {
            fetch(`../${document.forms.delLab.dataset.id}`, {
                method: "DELETE"
            })
                .then(htmlOrError("Deleting lab failed (probably has submissions)."))
                .then(() => {
                    window.location = "../../";
                })
                .catch(alertError);
        }
    });

    // enable remove attachment icons
    function delAttachment(e) {
        if (!e.target.classList.contains("remove")) {
            return;
        }

        const attachment = e.target.parentElement;
        const link = attachment.querySelector("a");
        const name = link.textContent;
        const id = e.target.dataset.id;
        if (!confirm(`Are you sure you want to remove ${name}? Including download data, and possibly related zip actions?`)) {
            return;
        }

        const container = e.target.closest("div.attachContainer");
        const spinner = container.querySelector("i.attachSpin");
        spinner.classList.add("rotate");
        fetch(`attach/${id}`, {
            method: "DELETE",
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.error) {
                    alert(data.error);
                } else {
                    attachment.remove();

                    // remove the entry from each labzip dropdown on the page
                    document.querySelectorAll("select.zipAttachment option").forEach((e) => {
                        if (e.value == id) {
                            e.remove();
                        }
                    });
                }
                spinner.classList.remove('rotate');
                const attachments = container.querySelector("div.attachments");
                if (attachments.childElementCount == 0) {
                    attachments.previousElementSibling.classList.add("empty");
                }
            });
    }
    document.querySelectorAll(".attachment i").forEach((e) => {
        e.addEventListener("click", delAttachment);
    });

    // enable add attachment icon
    function clickAttachBtn(evt) {
        const parent = evt.target.parentElement;
        const input = parent.querySelector("input.attachment");
        input.click();
    }
    document.querySelectorAll("i.attachBtn").forEach((e) =>
        e.addEventListener("click", clickAttachBtn)
    );
    function uploadAttachment(e) {
        const container = e.target.closest("div.attachContainer");
        const spinner = container.querySelector("i.attachSpin");
        spinner.classList.add("rotate");
        const data = new FormData();
        data.append("attachment", this.files[0]);
        data.append("deliverable_id", this.dataset.deliverable_id);
        fetch("attach", {
            method: "POST",
            body: data
        })
            .then(htmlOrError("Upload attachment failed."))
            .then((html) => {
                const div = document.createElement("div");
                div.innerHTML = html;
                div.querySelector("i.remove").addEventListener("click", delAttachment);
                const attachments = container.querySelector("div.attachments");
                attachments.appendChild(div);
                attachments.previousElementSibling.classList.remove('empty');
                spinner.classList.remove('rotate');

                // zip attachments also have a config icon
                const type = div.firstElementChild.dataset.type;
                if (type == "zip") {
                    div.querySelector("i.zipActionConfig").addEventListener("click", openZipActionDialog);
                }
            })
            .catch((error) => {
                spinner.classList.remove('rotate');
                alert(error);
            });
    }
    document.querySelectorAll("input.attachment").forEach((e) =>
        e.addEventListener("change", uploadAttachment)
    );

    // enable add deliverable 
    const addDeliv = document.getElementById("addDelivIcon");
    addDeliv.onclick = function() {
        const dialog = document.getElementById("addDelivDialog");
        dialog.showModal();
    };
    document.getElementById("closeAddDialog").onclick = function() {
        document.getElementById("addDelivDialog").close();
    };
    document.getElementById("addDelivBtn").onclick = function(e) {
        const data = new FormData();
        data.append("type", document.getElementById("delivType").value);
        data.append("seq", e.target.dataset.seq);
        data.append("lab_id", e.target.dataset.lab_id);
        e.target.dataset.seq++;
        fetch("deliverable", {
            method: "POST",
            body: data,
        })
            .then(htmlOrError("Adding deliverable failed."))
            .then((html) => {
                const noDelivs = document.getElementById("noDelivs");
                if (noDelivs) {
                    noDelivs.previousElementSibling.classList.remove("empty");
                    noDelivs.classList.add("hide");
                }
                const div = document.createElement("div");
                div.innerHTML = html;
                const txt = div.querySelector("textarea");
                if (txt) {
                    txt.addEventListener("change", updateDeliv);
                    txt.addEventListener("keydown", MARKDOWN.keyEventHandler);
                    txt.dataset.initialHeight = "108"; // bad hardcoding
                    txt.addEventListener("keydown", MARKDOWN.autoExpand);
                }
                div.querySelector("i.delDeliv").addEventListener("click", delDeliv);
                div.querySelector(".labPoints").textContent = document.getElementById("labPoints").value;
                div.querySelector(".points").addEventListener("change", updatePoints);
                div.querySelector(".points").addEventListener("change", updateDeliv);
                div.querySelector("i.deliverable").addEventListener("click", MARKDOWN.toggleMarkDown);
                div.querySelector("button.previewBtn").addEventListener("click", MARKDOWN.getHtmlForMarkdown);
                div.querySelector("select.zipAttachment")?.addEventListener("change", updateZipAttachment);
                div.querySelector("i.attachBtn").addEventListener("click", clickAttachBtn);
                div.querySelector("input.attachment").addEventListener("change", uploadAttachment);
                const conf = div.querySelector("i.zipCheckConfig");
                if (conf) {
                    conf.addEventListener("click", openZipCheckDialog);
                }
                const delivs = document.getElementById("deliverables");
                delivs.appendChild(div);
                document.getElementById("addDelivDialog").close();
                window.scrollTo(0, document.body.scrollHeight);
                txt?.focus();
            })
            .catch((error) => {
                document.getElementById("addDelivDialog").close();
                alert(error);
            });
    };

    // enable delete deliverable
    function delDeliv() {
        const deliv = this.parentElement.parentElement;
        if (confirm("Are you sure you want to remove this deliverable?")) {
            fetch(`deliverable/${this.dataset.id}`, {
                method: "DELETE",
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.error) {
                        alert(data.error);
                    } else {
                        deliv.remove();
                    }
                });
        }
    }
    document.querySelectorAll(".delDeliv").forEach((e) => {
        e.addEventListener("click", delDeliv);
    });

    // enable update deliverable
    function updateDeliv() {
        const dcontainer = this.closest(".dcontainer");
        const id = dcontainer.dataset.id;
        const points = dcontainer.querySelector(".points").value;
        const desc = encodeURIComponent(MARKDOWN.ceasarShift(dcontainer.querySelector(".desc").value));
        const hasMarkDown = dcontainer.querySelector("i.deliverable").classList.contains("active") ? 1 : 0;

        fetch(`deliverable/${id}`, {
            method: "PUT",
            body: `desc=${desc}&points=${points}&hasMarkDown=${hasMarkDown}`,
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
        })
            .then(htmlOrError("Updating deliverable failed."))
            .catch(alertError);
    }
    document.querySelectorAll(".about input, .deliv textarea").forEach((e) => {
        e.addEventListener("change", updateDeliv);
    });

    function updateZipAttachment() {
        const dcontainer = this.closest(".dcontainer");
        const id = dcontainer.dataset.id;
        const zipAttachment_id = this.value;

        fetch(`deliverable/${id}/zipAttachment`, {
            method: "PUT",
            body: `zipAttachment_id=${zipAttachment_id}`,
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
        })
            .then(htmlOrError("Updating deliverable failed."))
            .catch(alertError);
    }
    document.querySelectorAll(".dcontainer select.zipAttachment").forEach((e) => {
        e.addEventListener("change", updateZipAttachment);
    });

    // have points update when any deliverable points change
    function updatePoints() {
        let points = 0;
        document.querySelectorAll(".points").forEach((e) => {
            points += parseInt(e.value);
        });
        document.getElementById("labPoints").value = points;
        document.querySelectorAll(".labPoints").forEach((e) => {
            e.textContent = points;
        });
    }
    document.querySelectorAll(".points").forEach((e) => {
        e.addEventListener("change", updatePoints);
    });

    /**
     * Zip Action related code
     */
    function setZipActionHTML(html) {
        const zipActions = document.getElementById("zipActions");
        zipActions.innerHTML = html;
        zipActions.querySelectorAll(".remove").forEach((e) => {
            e.onclick = removeZipAction;
        });
    }
    function removeZipAction() {
        const action_id = this.parentNode.dataset.id;
        fetch("zipActions/" + action_id, {
            method: "DELETE",
        })
            .then((response) => {
                if (response.ok) {
                    this.parentNode.remove();
                } else {
                    throw new Error("Error deleting zip action.");
                }
            })
            .catch(alertError);
    }
    function openZipActionDialog() {
        const dialog = document.getElementById("zipActionDialog");
        dialog.showModal();
        const aid = this.parentNode.dataset.aid;
        document.getElementById("attachment_id").value = aid;
        document.getElementById("zipActionForm").setAttribute("action", `${aid}/zipActions`);

        // get all zip actions for this zip attachment
        fetch(aid + "/zipActions")
            .then(htmlOrError("Getting zip actions failed."))
            .then(setZipActionHTML)
            .catch(alertError);
        // get all files for this zip attachment
        fetch("attachment/" + aid)
            .then(htmlOrError("Getting zip listing failed."))
            .then((html) => {
                document.getElementById("fileSelect").innerHTML = html;
            })
            .catch(alertError);

    };
    document.querySelectorAll(".zipActionConfig").forEach((e) => {
        e.onclick = openZipActionDialog;
    });
    document.getElementById("closeZipDialog").onclick = function() {
        document.getElementById("zipActionDialog").close();
    };

    // add action to zip attachment
    document.getElementById("addZipActionBtn").onclick = function(evt) {
        evt.preventDefault();
        const aid = document.getElementById("attachment_id").value;

        // check if byte is a number
        const byteField = document.getElementById("byte");
        const byte = byteField.value;
        const num = parseInt(byte);
        if (isNaN(num) || num < 0) {
            alert("Byte must be a non-negative number.");
            byteField.value = "";
            byteField.focus();
            return false;
        }

        // when action is png, check that the file ends in .png
        const fileField = document.getElementById("fileSelect");
        const file = fileField.value;
        const actionField = document.getElementById("zipAction");
        const action = actionField.value;
        const name = file.toLowerCase();
        if (action == "png" && !name.endsWith(".png")) {
            alert("File must end in .png for watermarking a png.");
            fileField.focus();
            return false;
        }

        // send to server to create
        const data = new FormData();
        data.append("type", action);
        data.append("file", file);
        data.append("byte", num);
        fetch(aid + "/zipActions", {
            method: "POST",
            body: data,
        })
            .then(htmlOrError("Adding action failed"))
            .then(setZipActionHTML)
            .catch(alertError);

        // clear fields for next add
        fileField.value = "";
        byteField.value = "";
    }

    /**
     * Zip Check related code
     */
    function removeZipCheck() {
        const check_id = this.parentNode.dataset.id;
        fetch("zipChecks/" + check_id, {
            method: "DELETE",
        })
            .then((response) => {
                if (response.ok) {
                    this.parentNode.remove();
                } else {
                    throw new Error("Error deleting zip check.");
                }
            })
            .catch(alertError);
    }
    function setZipCheckHTML(html) {
        const zipChecks = document.getElementById("zipChecks");
        zipChecks.innerHTML = html;
        zipChecks.querySelectorAll(".remove").forEach((e) => {
            e.onclick = removeZipCheck;
        });
    }
    function openZipCheckDialog() {
        const dialog = document.getElementById("zipCheckDialog");
        dialog.showModal();

        // get all zip checks for this zip attachment
        const did = this.dataset.id;
        document.getElementById("deliverable_id").value = did;
        document.getElementById("zipCheckForm").setAttribute("action", `${did}/zipChecks`);

        fetch(did + "/zipChecks")
            .then(htmlOrError("Getting zip checks failed."))
            .then(setZipCheckHTML)
            .catch(alertError);
    }
    document.querySelectorAll(".zipCheckConfig").forEach((e) => {
        e.onclick = openZipCheckDialog;
    });
    document.getElementById("closeCheckDialog").onclick = function() {
        document.getElementById("zipCheckDialog").close();
    };
    const byteField = document.getElementById("byteField");
    const fileField = document.getElementById("fileField");
    const checkByte = document.getElementById("checkByte");
    document.getElementById("checkType").addEventListener("change", function() {
        const blockTxt = document.getElementById('block');
        if (this.value.endsWith("wm")) {
            fileField.classList.remove("hide");
            byteField.classList.remove("hide");
            blockTxt.classList.remove("active");
            checkByte.setAttribute("placeholder", "Check at Byte");
        } else if (this.value.startsWith("size")) {
            fileField.classList.add("hide");
            byteField.classList.remove("hide");
            blockTxt.classList.add("active");
            checkByte.setAttribute("placeholder", "Size in Bytes");
        } else {
            byteField.classList.add("hide");
            fileField.classList.remove("hide");
            blockTxt.classList.add("active");
        }
    });
    document.getElementById('block').onclick = function() {
        this.classList.toggle("active");
    }
    document.getElementById("addZipCheckBtn").onclick = function(evt) {
        evt.preventDefault();
        const did = document.getElementById("deliverable_id").value;
        const data = new FormData();
        const fileField = document.getElementById("checkFile");
        const byteField = document.getElementById("checkByte");
        const blockTxt = document.getElementById('block');

        // send to server to create
        data.append("type", document.getElementById("checkType").value);
        data.append("file", fileField.value);
        data.append("byte", byteField.value);
        data.append("block", blockTxt.classList.contains('active') ? 1 : 0);
        fetch(did + "/zipChecks", {
            method: "POST",
            body: data,
        })
            .then(htmlOrError("Adding check failed"))
            .then(setZipCheckHTML)
            .catch(alertError);

        // clear fields for next add
        fileField.value = "";
        byteField.value = "";
    };
});
