
window.addEventListener("load", () => {
    'use strict';
    document.getElementById("days").onclick = function (e) {
        if (e.target.tagName == "TD") {
            e.target.querySelector("a").click();
        }
    }

    const info = document.getElementById("info-btn");
    if (info) {
        const overlay = document.getElementById("overlay");
        const tables = document.getElementById("tables");
        const offering_id = document.getElementById('offering').dataset.id;
        let enrollment = false;

        function displayTables(props) {
            ReactDOM.render(
                React.createElement(INFO.ViewersTable, props, null),
                tables
            );
        }

        function offeringViewers() {
            overlay.classList.add("visible");
            fetch(`viewers?offering_id=${offering_id}`)
                .then(response => response.json())
                .then(json => {
                    const props = {
                        title: "Offering Viewers",
                        users: json,
                    };
                    displayTables(props)
                });
        }
        function dayViewers() {
            overlay.classList.add("visible");
        }
        function hideViewers() {
            overlay.classList.remove("visible");
        }
        document.getElementById("close-overlay").onclick = hideViewers;

        info.onclick = function () {
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
    }

});
