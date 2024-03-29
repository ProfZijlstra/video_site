window.addEventListener("load", () => {
    // auto update on detail change
    function updateDetails() {
        const form = document.forms.updateLab;
        const id = form.dataset.id;
        const visible = form.visible.checked ? 1 : 0;
        const name = encodeURIComponent(form.name.value);
        const day_id = form.day_id.value;
        const startdate = form.startdate.value;
        const starttime = form.starttime.value;
        const stopdate = form.stopdate.value;
        const stoptime = form.stoptime.value;
        const type = form.type.value;
        const hasMarkDown = form.hasMarkDown.value;
        const desc = form.desc.value;
        const shifted = encodeURIComponent(MARKDOWN.ceasarShift(desc));

        fetch(`../${id}`, {
            method: "PUT",
            body: `visible=${visible}&name=${name}&day_id=${day_id}&startdate=${startdate}&starttime=${starttime}&stopdate=${stopdate}&stoptime=${stoptime}&type=${type}&hasMarkDown=${hasMarkDown}&desc=${shifted}`,
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
        const descMarkDown = document.getElementById("descMarkDown");
        descMarkDown.value = descMarkDown.value == "1" ? "0" : "1";
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
            .then((response) => {
                if (response.ok) {
                    return response.text();
                }
                throw new Error("Deleting lab failed (probably has submissions).");
            })
            .then(() => {
                window.location = "../../lab";
            })
            .catch((error) => {
                alert(error);
            });
        }
    });

    // enable remove attachment icons
    function delAttachment(e) {
        if (e.target.classList.contains("remove")) {
            const attachment = e.target.parentElement;
            const link = attachment.querySelector("a");
            const name = link.textContent;
            const id = e.target.dataset.id;
            if (confirm(`Are you sure you want to remove ${name}?`)) {
                const spinner = document.getElementById("attachSpin");
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
                        const attachments = document.getElementById("attachments");
                        if (attachments.childElementCount == 0) {
                            attachments.previousElementSibling.classList.add("empty");
                        }
                    });
            }
        }
    }
    document.querySelectorAll(".attachment i").forEach((e) => {
        e.addEventListener("click", delAttachment);
    });

    // enable add attachment icon
    document.getElementById("attachBtn").addEventListener("click", () => {
        document.getElementById("attachment").click();
    });
    document.getElementById("attachment").addEventListener("change", function() {
        // upload attachment
        const spinner = document.getElementById("attachSpin");
        spinner.classList.add("rotate");
        const data = new FormData();
        data.append("attachment", this.files[0]);
        fetch("attach", {
                method: "POST",
                body: data
        })
            .then((response) => {
                if (response.ok) {
                    return response.text();
                }
                throw new Error("Upload attachment failed.");
            })
            .then((html) => {
                const div = document.createElement("div");
                div.innerHTML = html;
                div.querySelector("i").addEventListener("click", delAttachment);
                const attachments = document.getElementById("attachments");
                attachments.appendChild(div);
                spinner.classList.remove('rotate');

                // add an entry to each labzip dropdown on the page
                const type = div.firstElementChild.dataset.type;
                if (type == "lab zip") {
                    const id = div.querySelector("i").dataset.id;
                    const name = div.querySelector("a").textContent;
                    document.querySelectorAll("select.zipAttachment").forEach((e) => {
                        const option = document.createElement("option");
                        option.value = id;
                        option.textContent = name;
                        e.appendChild(option);
                    });
                }
            })
            .catch((error) => {
                spinner.classList.remove('rotate');
                alert(error);
            });
    });

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
            .then((response) => {
                if (response.ok) {
                    return response.text();
                }
                throw new Error("Adding deliverable failed.");
            })
            .then((html) => {
                const div = document.createElement("div");
                div.innerHTML = html;
                div.querySelector("i.delDeliv").addEventListener("click", delDeliv);
                div.querySelector(".labPoints").textContent = document.getElementById("labPoints").value;
                div.querySelector(".points").addEventListener("change", updatePoints);
                div.querySelector("i.deliverable").addEventListener("click", MARKDOWN.toggleMarkDown);
                div.querySelector("button.previewBtn").addEventListener("click", MARKDOWN.getHtmlForMarkdown);
                div.querySelector("select.zipAttachment")?.addEventListener("change", updateZipAttachment);
                const delivs = document.getElementById("deliverables");
                delivs.appendChild(div);
                document.getElementById("addDelivDialog").close();
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
        .then((response) => {
            if (response.ok) {
                return response.text();
            }
            throw new Error("Updating deliverable failed.");
        })
        .catch((error) => {
            alert(error);
        });
    }
    document.querySelectorAll(".dcontainer input, .dcontainer textarea").forEach((e) => {
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
        .then((response) => {
            if (response.ok) {
                return response.text();
            }
            throw new Error("Updating deliverable failed.");
        })
        .catch((error) => {
            alert(error);
        });
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
});
