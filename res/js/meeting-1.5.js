/* global Html5Qrcode */
/* global SOUNDS */

window.addEventListener("load", () => {
    // TODO redo how badge scans are shown -- needs to become a persistent list
    let codesRead = {};
    let html5QrCode = null
    let currentCamera = 0;
    let unknownBadge = null;

    // back logic is different from other pages
    // we don't want background clicks to trigger a back
    document.querySelector("body > main").addEventListener("click", function (evt) {
        if (evt.target.classList.contains('back') || 
            evt.target.classList.contains('fa-arrow-left')) {
                window.location = "../attendance";
        }
    });

    // enable regen meeting button
    document.getElementById("regen_meeting").onclick = function() {
        document.getElementById("regen_form").submit();
    };

    // enable delete meeting button
    document.getElementById("delete_meeting").onclick =
        function() {
        if (confirm("Delete this meeting and all related data?")) {
            document.getElementById("delete_form").submit();
        }
    }

    // make POST on change meeting details
    function saveSettings() {
        const form = document.getElementById("meeting_form");
        const id = form.id.value;
        const update = {};
        update.id = id;
        update.title = form.title.value;
        update.date = form.date.value;
        update.start = form.start.value;
        update.stop = form.stop.value;
        fetch(`${id}`, {
            method : 'POST',
            headers : {'Content-Type' : 'application/x-www-form-urlencoded'},
            body : new URLSearchParams(update)
        });
    }
    const inps = document.querySelectorAll("#meeting_form input");
    for (const inp of inps) {
        inp.onchange = saveSettings;
    }

    // select all checkboxes for column on header click
    const ths = document.querySelectorAll("#present th");
    function checkAll(evt) {
        if (!confirm("Check all boxes in this column?")) {
            return;
        }
        // find which column is clicked
        let idx = 0;
        for(const th of ths) {
            if (th == evt.target) {
                break;
            }
            idx++;
        }

        const trs = document.querySelectorAll("#present tr");
        for (const tr of trs) {
            const tds = tr.querySelectorAll("td");
            const box = tds[idx]?.querySelector("input[type=checkbox]");
            if (box && !box.checked) {
                box.click();
            }
        }
    }
    for (const th of ths) {
        th.onclick = checkAll;
    }


    // do updates on checkbox clicks
    const present = document.getElementById("present");
    if (present) {
        present.onclick = (evt) => {
            if (evt.target.tagName === "INPUT") {
                doUpdate(evt);
            }
        };
    }
    const inputs = document.getElementsByClassName("time");
    for (const input of inputs) {
        input.onchange = doUpdate;
    }

    function doUpdate(evt) {
        const tr = evt.target.parentNode.parentNode;
        if (unknownBadge) {
            const links = tr.getElementsByTagName('a');
            const studentID = links[0].textContent;
            registerBadge(studentID, unknownBadge);
        }
        const id = tr.dataset.id;
        const boxes = tr.getElementsByTagName("input");
        const startFields = tr.getElementsByClassName("start");
        const start = startFields[0].value;
        const stopFields = tr.getElementsByClassName("stop");
        const stop = stopFields[0].value;
        const update = {
            "id" : id,
            "start" : start,
            "stop" : stop,
            "late" : 0,
            "mid" : 0,
            "left" : 0,
            "excu" : 0,
            "phys" : 0
        };
        for (const box of boxes) {
            if (box.checked) {
                const name = box.getAttribute("name");
                update[name] = 1;
            }
        }
        console.log(update);

        fetch(`attend/${id}`, {
            method : 'POST',
            headers : {'Content-Type' : 'application/json'},
            body : JSON.stringify(update)
        });
    }

    function registerBadge(studentID, badge) {
        const data = {"studentID": studentID, "badge": badge};
        fetch(`/videos/user/registerBadge`, {
            method : 'POST',
            headers : {'Content-Type' : 'application/json'},
            body : JSON.stringify(data)
        }).then(clearRegisterMsg);
    }

    document.getElementById("regen").onclick = () => {
        const boxes = present.getElementsByClassName("phys");
        let has_phys = false;
        for (const box of boxes) {
            if (box.checked) {
                has_phys = true;
                break;
            }
        }
        if (has_phys &&
            !confirm(
                "Regenerate and delete all physical attendance and manually excused?")) {
            return false;
        }
        return true;
    };

    function markPresent(evt) {
        const aid = evt.target.parentNode.parentNode.dataset.id;
        document.getElementById("present_id").value = aid;
        const form = document.getElementById("presentForm");
        form.submit();
    }
    const presents = document.querySelectorAll("span.right.present");
    for (const present of presents) {
        present.onclick = markPresent;
    }

    function markAbsent(evt) {
        const aid = evt.target.parentNode.parentNode.dataset.id;
        document.getElementById("absent_id").value = aid;
        const form = document.getElementById("absentForm");
        form.submit();
    }
    const absents = document.querySelectorAll("span.right.absent");
    for (const absent of absents) {
        absent.onclick = markAbsent;
    }

    function markAbsenceExcused(evt) {
        const id = evt.target.parentNode.parentNode.dataset.id;
        const excu = evt.target.checked ? 1 : 0;
        const update = {
            "id" : id,
            "late" : 0,
            "mid" : 0,
            "left" : 0,
            "excu" : excu,
            "phys" : 0,
            "start": null,
            "stop": null
        };

        fetch(`attend/${id}`, {
            method : 'POST',
            headers : {'Content-Type' : 'application/json'},
            body : JSON.stringify(update)
        });
    }
    const excuses = document.querySelectorAll("input.absent_excused");
    for (const excuse of excuses) {
        excuse.onclick = markAbsenceExcused;
    }

    // enable email absent
    document.getElementById("email_absent")?.addEventListener("click", () => {
        if (confirm("Email Unexcused Absent?")) {
            const meeting_id = document.getElementById("meeting_id").value;
            fetch(`${meeting_id}/emailAbsent`, {
                method : 'POST',
            }).then(() => {alert("Emails sent")});
        }    
    });

    // enable email tardy
    document.getElementById("email_tardy")?.addEventListener("click", () => {
        if (confirm("Email Unexcused Tardy?")) {
            const meeting_id = document.getElementById("meeting_id").value;
            fetch(`${meeting_id}/emailTardy`, {
                method : 'POST',
            }).then(() => {alert("Emails sent")});
        }
    });

    // variables used multiple times in the upcoming functions
    const content = document.getElementById("content");
    const scanner = document.getElementById("scannerContainer");
    const reader = document.getElementById("readerContainer");
    const input = document.getElementById("barcode");
    const box = document.getElementById('msgContainer');
    let interval = false;


    // input field for the laser barcode scanner
    input.onkeyup = function(evt) {
        if (evt.key ===  "Enter") {
            processCode(this.value);
            this.value = "";
        }
    }

    function focusInput() {
        input.focus();
    }

    // toggle laser barcode scanner
    document.getElementById("barcodeScanner").onclick = function() {
        // stop camera if open
        reader.classList.add("hide");
        if (html5QrCode) {
            stopCamera();
        }
        // open barcode input pane (or close it)
        if (scanner.classList.contains("hide")) {
            scanner.classList.remove("hide");
            box.classList.remove("hide");
            if (!content.classList.contains("left")) {
                content.classList.add("left");
            }
            interval = setInterval(focusInput, 200);
        } else {
            scanner.classList.add("hide");
            box.classList.add("hide");
            content.classList.remove("left");
            clearInterval(interval);
        }
    };

    // toggle camera barcode reader
    document.getElementById("barcodeReader").onclick = function() {
        // hide barcode input pane
        scanner.classList.add("hide");
        clearInterval(interval);
        // open camera barcode reader (or close it)
        if (!html5QrCode) {
            if (!content.classList.contains("left")) {
                content.classList.add("left");
            }
            reader.classList.remove("hide");
            startCamera();
        } else {
            content.classList.remove("left");
            reader.classList.add("hide");
            box.classList.add("hide");
            stopCamera();
        }
    };

    function processCode(barcode) {
        if (!codesRead[barcode]) {
            setPhysicalAttendance(barcode);
            codesRead[barcode] = true;
        }
    }

    function stopCamera() {
        html5QrCode.stop();
        html5QrCode = null;
    }

    function startCamera() {
        // from: https://blog.minhazav.dev/research/html5-qrcode.html
        html5QrCode = new Html5Qrcode("reader");
        // This method will trigger user permissions
        Html5Qrcode.getCameras()
            .then(devices => {
                if (devices && devices.length) {
                    cameraCount = devices.length;
                    if (cameraCount > 1) {
                        document.getElementById("rotate").classList.remove("hide");
                    } else {
                        document.getElementById("rotate").classList.add("hide");
                    }

                    let cameraId = devices[currentCamera].id;
                    // this will also trigger a user permissions check
                    html5QrCode
                        .start(
                            cameraId, 
                            {fps : 10, qrbox: {width: 800, height: 300 }, 
                            formatsToSupport: [ Html5QrcodeSupportedFormats.CODE_128 ]},
                            processCode
                        )
                        .catch(err => {
                            // Start failed, handle it. For example,
                            console.log(
                                `Unable to start scanning, error: ${err}`);
                        });
                }
            })
            .catch(err => {
                // handle err
                console.log(`Unable to get cameras, error: ${err}`);
            });
    }

    document.getElementById("rotate").onclick = function() {
        html5QrCode.stop().then(() => {
            currentCamera = (currentCamera + 1) % cameraCount;
            startCamera();
        });
    };

    // try to take physical attendance
    function setPhysicalAttendance(barcode) {
        const phys = document.getElementById(barcode);
        if (phys) {
            if (!phys.checked) {
                phys.click();
            }
            const links = phys.parentNode.parentNode.getElementsByTagName('a');
            const name = links[1].textContent;
            document.getElementById("physicallyPresent").textContent = name;
            const msg = document.getElementById("attendMsg");
            box.classList.remove('hidden');
            msg.classList.remove('hidden');
            SOUNDS.present();
            setTimeout(() => {
                box.classList.add("hidden"); 
                msg.classList.add("hidden")
            }, 7000);
        } else {
            SOUNDS.notFound();
            box.classList.remove('hidden');
            document.getElementById('registerMsg').classList.remove('hidden');
            document.getElementById('unknownBadge').innerText = barcode;
            unknownBadge = barcode;
        }
    }

    function clearRegisterMsg() {
        unknownBadge = null;
        document.getElementById('registerMsg').classList.add('hidden');
        box.classList.add('hidden');
    }
    document.getElementById("cancelRegister").onclick = clearRegisterMsg;
});