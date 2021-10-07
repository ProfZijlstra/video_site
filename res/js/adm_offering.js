window.addEventListener("load", () => {
    // display on page summary when clicking info button
    document.getElementById("info-btn").onclick = function() {
        const e = React.createElement;
        const offering_id = document.getElementById('offering').dataset.id;

        fetch('info')
            .then(response => response.json())
            .then(function(json) {
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
    document.getElementById("overlay").onclick = function(evt) {
        if (evt.target == this) {
            INFO.hideTables();
        }
    };
});