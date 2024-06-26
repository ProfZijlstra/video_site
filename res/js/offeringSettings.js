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
        const showDates = document.getElementById("showDates").checked;
        const usesFlowcharts = document.getElementById("usesFlowcharts").checked;

        const body = `id=${id}&block=${encodeURIComponent(block)}` 
                    + `&start=${encodeURIComponent(start)}`
                    + `&daysPerLesson=${daysPerLesson}`
                    + `&lessonsPerPart=${lessonsPerPart}`
                    + `&lessonParts=${lessonParts}`
                    + "&hasQuiz="+ (hasQuiz ? 1 : 0) 
                    + "&hasLab=" + (hasLab ? 1 : 0)
                    + "&showDates=" + (showDates ? 1: 0)
                    + "&usesFlowcharts=" + (usesFlowcharts ? 1 : 0)

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
});            
