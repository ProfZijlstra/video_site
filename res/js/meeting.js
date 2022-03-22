window.addEventListener("load", () => {
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
            "phys" : 0
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
    document.getElementById("email_absent").onclick = function() {
        if (confirm("Email Unexcused Absent?")) {
            const meeting_id = document.getElementById("meeting_id").value;
            fetch(`${meeting_id}/emailAbsent`, {
                method : 'POST',
            }).then(() => {alert("Emails sent")});
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
});