window.addEventListener("load", () => {
    const overlay = document.getElementById("overlay");

    // everything for hiding the overloay
    function hide() {
        overlay.classList.remove("visible");
    }
    document.getElementById("close-overlay").onclick = hide;
    document.getElementById("overlay").onclick = function (evt) {
        if (evt.target == this) {
            hide();
        }
    };

    // showing the overlay
    function addMeeting() {
        const session_id = this.parentNode.dataset.session_id;
        const date= this.parentNode.parentNode.dataset.date;
        const day = this.parentNode.parentNode.dataset.day; 
        const stype = this.parentNode.dataset.stype;
        let day_part = " Morning";
        let start = "10:30:00";
        if (stype == "PM") {
            day_part = " Afternoon";
            start = "13:30:00";
        }
        document.getElementById("session_id").value = session_id;
        document.getElementById("start").value = start;

        document.getElementById("manual_session_id").value = session_id;
        document.getElementById("manual_title").value = day + day_part;
        document.getElementById("manual_date").value = date;
        document.getElementById("manual_start").value = start;

        overlay.classList.add("visible");
    }
    const addBtns = document.getElementsByClassName("fa-plus-square");
    for (const btn of addBtns) {
        btn.onclick = addMeeting;
    }

    const timeValidationMsg = "Ivalid 24 hour colon separated time format";
    document.getElementById("start").setCustomValidity(timeValidationMsg);
    document.getElementById("manual_start").setCustomValidity(timeValidationMsg);
    document.getElementById("manual_stop").setCustomValidity(timeValidationMsg);

});