window.addEventListener("load", () => {
    const overlay = document.getElementById("overlay");
    const tables = document.getElementById("tables");
    const offering_id = document.getElementById('offering').dataset.id;
    let enrollment = false;

    function displayTables(title, users) {
        ReactDOM.unmountComponentAtNode(tables);
        const enrolled = [];
        const enrol_nv = [];
        const non_enrol = [];
        for (const user of users) {
            if (enrollment[user.id]) {
                enrolled.push(user);
                enrollment[user.id].seen = true;
            } else {
                non_enrol.push(user);
            }
        }
        for (const id in enrollment) {
            const user = enrollment[id];
            if (!user.seen) {
                enrol_nv.push(user);
            }
        }

        const header = React.createElement('h2', null, title);
        const enrolTable = React.createElement(INFO.ViewersTable, {
            title: "Enrolled Users",
            users: enrolled,
        }, null);
        const enrnvTable = React.createElement(INFO.ViewersTable, {
            title: "Enrolled No View",
            users: enrol_nv,
        }, null);
        const non_enrTable = React.createElement(INFO.ViewersTable, {
            title: "Non-Enrolled Users",
            users: non_enrol,
        }, null);
        const combined = React.createElement(
            'div', null, header, enrolTable, enrnvTable, non_enrTable
        );

        ReactDOM.render(
            combined,
            tables
        );
    }

    function offeringViewers() {
        const course = document.getElementById("course_num").textContent;
        const offering = document.getElementById("offering").textContent;
        const title = `${course} ${offering}`;
        fetch(`viewers?offering_id=${offering_id}`)
            .then(response => response.json())
            .then(json => displayTables(title, json));
        overlay.classList.add("visible");
    }

    function dayViewers(evt) {
        let td = evt.target.parentNode;
        while (td.tagName != "TD") {
            td = td.parentNode;
        }
        const day = td.id;
        const day_id = td.dataset.id;
        const text = td.getElementsByTagName('a')[0].textContent;
        const title = `${day} ${text}`;
        fetch(`${day}/viewers?day_id=${day_id}`)
            .then(response => response.json())
            .then(json => displayTables(title, json));
        overlay.classList.add("visible");
    }

    function hideViewers() {
        overlay.classList.remove("visible");
    }

    document.getElementById("close-overlay").onclick = hideViewers;

    document.getElementById("info-btn").onclick = function () {
        const e = React.createElement;

        fetch('info')
            .then(function (response) {
                return response.json();
            })
            .then(function (json) {
                for (const day in json) {
                    const elm = document.getElementById(day)
                        .getElementsByClassName("info")[0];
                    const props = json[day];

                    if (day == "total") {
                        props.showUsers = offeringViewers;
                    } else {
                        props.showUsers = dayViewers;
                    }

                    ReactDOM.render(e(INFO.Info, props), elm);
                }
            });
        fetch(`enrollment?offering_id=${offering_id}`)
            .then(response => response.json())
            .then(function (json) {
                enrollment = json;
            });
    };
});