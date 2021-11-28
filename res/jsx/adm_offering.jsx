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
                        <label>Start Date:</label>
                        <input type="date" name="date" />
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
        for (let w = 1; w < 5; w++) {
            for (let d = 1; d < 8; d++) { 
                const td = document.getElementById(`W${w}D${d}`);
                const nextSib = td.querySelector("time");
                const text = td.querySelector(".text").innerText;
                const edit = document.createElement("i");
                edit.setAttribute("class", "far fa-edit");
                edit.onclick = editDay.bind(null, td.dataset.day_id, text);
                td.insertBefore(edit, nextSib);
            }
        }
    };
});