window.addEventListener("load", () => {
    // display on page summary when clicking info button
    document.getElementById("info-btn").onclick = function () {
        const e = React.createElement;
        const offering_id = document.getElementById('offering').dataset.id;

        fetch('info')
            .then(response => response.json())
            .then(function (json) {
                for (const day in json) {
                    const elm =
                        document.getElementById(day).getElementsByClassName(
                            "info")[0];
                    const props = json[day];

                    if (day == "total") {
                        props.showUsers = INFO.offeringViewers;
                    } else {
                        props.showUsers = INFO.dayViewers;
                    }

                    ReactDOM.render(e(INFO.Info, props), elm);
                }
            });
        fetch(`enrollment?offering_id=${offering_id}`)
            .then(response => response.json())
            .then(json => INFO.setEnrollment(json));
    };

    document.getElementById("close-overlay").onclick = INFO.hideTables;
    document.getElementById("overlay").onclick = function (evt) {
        if (evt.target == this) {
            INFO.hideTables();
        }
    };

    document.getElementById("clone").onclick = function () { 
        fetch('/videos/user/faculty')
            .then(response => response.json())
            .then(response => createCloneModal(response));
    };

    function createCloneModal(fac_users) {
        const fac_user_opts = fac_users.map((user) => {
            return (
                <option value={user.id}>{user.firstname} {user.lastname}</option>
            )});
        const content = document.getElementById("content");
        const offering_id = document.getElementById("offering").dataset.id;
        ReactDOM.unmountComponentAtNode(content);
        const clone = (
            <div class="modal">
                <h2>Clone Offering</h2>
                <form method="POST" action="clone">
                    <input type="hidden" name="offering_id" value={offering_id} />
                    <div class="line">
                        <label>New Block:</label>
                        <input name="block" />
                    </div>
                    <div class="line">
                        <label>Faculty</label>
                        <select name="fac_user_id">{fac_user_opts}</select>
                    </div>
                    <div class="line">
                        <label>Start Date:</label>
                        <input type="date" name="date" />
                    </div>
                    <div>
                        <label>Days per Lesson</label>
                        <input type="number" name="daysPerLesson" />
                        <label>Lessons per Part</label>
                        <input type="number" name="lessonsPerPart" />
                        <label>Parts</label>
                        <input type="number" name="lessonParts" />
                    </div>
                    <div class="submit">
                        <button>Submit</button>
                    </div>
                </form>
            </div>
        );
        ReactDOM.render(clone, content);
        document.getElementById("overlay").classList.add("visible");
    };

    function updValue(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    function editDay(day_id, desc, evt) {
        evt.preventDefault();
        evt.stopPropagation();
        const content = document.getElementById("content");
        ReactDOM.unmountComponentAtNode(content);
        const edit = (
            <div class="modal">
                <h2>Edit Day Title</h2>
                <form method="POST" action="edit">
                    <input type="hidden" name="day_id" value={day_id} />
                    <div class="line">
                        <label>Title:</label>
                        <input name="desc" placeholder={desc} />
                    </div>
                    <div class="submit">
                        <button>Submit</button>
                    </div>
                </form>
            </div>
        );
        ReactDOM.render(edit, content);
        document.getElementById("overlay").classList.add("visible");
    }

    document.getElementById("edit").onclick = function() {
        const divs = document.querySelectorAll("div.data");
        for (const div of divs) {
            const nextSib = div.querySelector("time");
            const text = div.querySelector(".text").innerText;
            const edit = document.createElement("i");
            edit.setAttribute("class", "far fa-edit");
            edit.onclick = editDay.bind(null, div.dataset.day_id, text);
            div.insertBefore(edit, nextSib);
        }
    };
});