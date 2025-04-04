window.addEventListener("load", () => {
    // close the addMeeting modal
    document.getElementById("closeAddMeeting").onclick = function() {
        document.getElementById('addMeeting').close();
    };

    // showing the add_meeting overlay
    const days = document.getElementById("days");
    const AM_start = days.dataset.amstart ? days.dataset.amstart : '10:00';
    const AM_stop = days.dataset.amstop ? days.dataset.amstop : '12:30';
    const PM_start = days.dataset.pmstart ? days.dataset.pmstart : '13:30';
    const PM_stop = days.dataset.pmstop ? days.dataset.pmstop : '15:20';
    function addMeeting() {
        const session_id = this.parentNode.dataset.session_id;
        const date = this.parentNode.parentNode.dataset.date;
        const day = this.parentNode.parentNode.dataset.day;
        const stype = this.parentNode.dataset.stype;
        let day_part = " Morning";
        let start = AM_start;
        let stop = AM_stop;
        if (stype == "PM") {
            day_part = " Afternoon";
            start = PM_start;
            stop = PM_stop;
        }
        document.getElementById("manual_session_id").value = session_id;
        document.getElementById("manual_title").value = day + day_part;
        document.getElementById("manual_date").value = date;
        document.getElementById("manual_start").value = start;
        document.getElementById("manual_stop").value = stop;

        document.getElementById('addMeeting').showModal();
    }
    const addBtns = document.getElementsByClassName("fa-plus-square");
    for (const btn of addBtns) {
        btn.onclick = addMeeting;
    }

    const timeValidationMsg = "Ivalid 24 hour colon separated time format";
    document.getElementById("manual_start").validationMessage = timeValidationMsg;
    document.getElementById("manual_stop").validationMessage = timeValidationMsg;

    // excused modal
    document.getElementById("closeAddExcused").onclick = function() {
        document.getElementById('addExcused').close();
    };
    function addExcused() {
        const session_id = this.parentNode.dataset.session_id;
        document.getElementById("excused_session_id").value = session_id;
        const data = this.dataset.excused
        const excused = JSON.parse(data);
        if (excused) {
            document.getElementById('none').classList.add('hidden');
            const area = document.getElementById('excused_list');
            area.classList.remove('hidden');

            const ul = document.createElement("ul")
            for (const student of excused) {
                // text parts
                const span = document.createElement("span");
                span.classList.add("teamsName");
                const teamsNameTxt = document.createTextNode(student.teamsName + " ");
                span.appendChild(teamsNameTxt);
                const excuseTxt = document.createTextNode(student.reason);
                // remove icon
                const rem = document.createElement("i");
                rem.classList.add("fa-solid");
                rem.classList.add("fa-xmark");
                rem.setAttribute("title", "Remove");
                rem.onclick = removeExcused;
                // list element
                const li = document.createElement("li");
                li.appendChild(span);
                li.appendChild(excuseTxt)
                li.appendChild(rem);
                ul.appendChild(li);
            }
            area.replaceChildren(ul);
        } else {
            document.getElementById('none').classList.remove('hidden');
            document.getElementById('excused_list').classList.add('hidden');
        }

        document.getElementById('addExcused').showModal();
    }
    function removeExcused() {
        const li = this.parentNode;
        const ul = this.parentNode.parentNode;
        const session_id = document.getElementById("excused_session_id").value;
        const span = this.parentNode.querySelector("span.teamsName");
        const teamsNameTxt = span.textContent;
        const teamsName = encodeURIComponent(teamsNameTxt);
        fetch('delExcuse', {
            method: 'POST',
            body: `session_id=${session_id}&teamsName=${teamsName}`,
            headers:
                { 'Content-Type': 'application/x-www-form-urlencoded' },
        })
        ul.removeChild(li);
    }
    const excuBtns = document.querySelectorAll("div.session i.fa-user-xmark");
    for (const btn of excuBtns) {
        btn.onclick = addExcused;
    }

    // physical attendance modal
    document.getElementById("physical_icon").onclick = function() {
        document.getElementById('physicalModal').showModal();
    };
    document.getElementById("closePhysicalModal").onclick = function() {
        document.getElementById('physicalModal').close();
    };

    document.getElementById("physicalBtn").onclick = function() {
        const week = document.getElementById("week").value;
        window.location = "physical/W" + week;
    };
});
