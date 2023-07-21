window.addEventListener("load", () => {
    function sendUpdate() {
        const id = document.getElementById("offering_id").value;
        const block = document.getElementById("block").value;
        const start = document.getElementById("start").value;
        const daysPerLesson = document.getElementById("daysPerLesson").value;
        const lessonsPerPart = document.getElementById("lessonsPerPart").value;
        const lessonParts = document.getElementById("lessonParts").value;
        const hasQuiz = document.getElementById("hasQuiz").checked;
        const hasLab = document.getElementById("hasLab").checked;
        const showDates = document.getAnimations("showDates").checked;
        const usesFlowcharts = document.getElementById("usesFlowcharts").checked;
        const hasCAMS = document.getElementById("hasCAMS").checked;
        const username = document.getElementById("username").value;
        const course_id = document.getElementById("course_id").value;
        const AM_id = document.getElementById("AM_id").value;
        const PM_id = document.getElementById("PM_id").value;
        const SAT_id = document.getElementById("SAT_id").value

        let body = `id=${id}&block=${encodeURIComponent(block)}` 
                    + `&start=${encodeURIComponent(start)}`
                    + `&daysPerLesson=${daysPerLesson}`
                    + `&lessonsPerPart=${lessonsPerPart}`
                    + `&lessonParts=${lessonParts}`
                    + "&hasQuiz="+ (hasQuiz ? 1 : 0) 
                    + "&hasLab=" + (hasLab ? 1 : 0)
                    + "&showDates=" + (showDates ? 1: 0)
                    + "&usesFlowcharts=" + (usesFlowcharts ? 1 : 0)
                    + "&hasCAMS=" + (hasCAMS ? 1 : 0);

        if (hasCAMS) {
            body += "&username=" + encodeURIComponent(username)
                    + "&course_id=" + course_id
                    + "&AM_id=" + AM_id
                    + "&PM_id=" + PM_id
                    + "&SAT_id=" + SAT_id
        }

        fetch("settings", {
            method : "POST",
            body : body,
            headers : {'Content-Type' : 'application/x-www-form-urlencoded'},
        });
    }
    const inputs = document.querySelectorAll("input");
    for (const input of inputs) {
        input.onchange = sendUpdate;
    }

    document.getElementById("hasCAMS").onclick = function() {
        const header = document.getElementById("CAMSheader");
        const settings = document.getElementById("CAMSsettings");
        if (this.checked) {
            header.classList.remove('hide');
            settings.classList.remove('hide');
        } else {
            header.classList.add('hide');
            settings.classList.add('hide');
        }
    }
});            
