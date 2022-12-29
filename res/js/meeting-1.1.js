/* global Html5Qrcode */
/* global SOUNDS */

window.addEventListener("load", () => {
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
                "Regenerate and delete all excused and all physical attendance?")) {
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

    document.getElementById("delete_meeting").onclick =
        function() {
        if (confirm("Delete this meeting and all related data?")) {
            document.getElementById("delete_form").submit();
        }
    }

    // enable email absent
    const emailAbsent = document.getElementById("email_absent");
    if (emailAbsent) {
        emailAbsent.onclick = function() {
            if (confirm("Email Unexcused Absent?")) {
                const meeting_id = document.getElementById("meeting_id").value;
                fetch(`${meeting_id}/emailAbsent`, {
                    method : 'POST',
                }).then(() => {alert("Emails sent")});
            }
        }    
    }

    // enable email tardy
    document.getElementById("email_tardy").onclick = function() {
        if (confirm("Email Unexcused Tardy?")) {
            const meeting_id = document.getElementById("meeting_id").value;
            fetch(`${meeting_id}/emailTardy`, {
                method : 'POST',
            }).then(() => {alert("Emails sent")});
        }
    }

    // toggle barcode reader
    document.getElementById("barcodeReader").onclick = function() {
        document.getElementById("content").classList.toggle("left");
        document.getElementById("readerContainer").classList.toggle("hide");
        if (!html5QrCode) {
            startScanning();
        } else {
            stopScanning();
        }
    };

    function stopScanning() {
        html5QrCode.stop();
        html5QrCode = null;
    }

    function startScanning() {
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
                        .start(cameraId, {fps : 10, qrbox: {width: 800, height: 300 }, formatsToSupport: [ Html5QrcodeSupportedFormats.CODE_128 ]},
                               barcode => {
                                    if (!codesRead[barcode]) {
                                        setPhysicalAttendance(barcode);
                                        codesRead[barcode] = true;
                                    }
                                })
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
            startScanning();
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
            msg.classList.remove('hidden');
            SOUNDS.present();
            setTimeout(() => {msg.classList.add("hidden")}, 7000);
        } else {
            SOUNDS.notFound();
            document.getElementById('registerMsg').classList.remove('hidden');
            document.getElementById('unknownBadge').innerText = barcode;
            unknownBadge = barcode;
        }
    }

    function clearRegisterMsg() {
        unknownBadge = null;
        document.getElementById('registerMsg').classList.add('hidden');
    }
    document.getElementById("cancelRegister").onclick = clearRegisterMsg;
});