window.addEventListener('load', () => {
    // display on page summary when clicking info button
    document.getElementById('info-btn').onclick = function() {
        const offering_id = document.getElementById('course').dataset.oid;
        const day_id = document.getElementById('day').dataset.id;
        fetch(`info?day_id=${day_id}`)
            .then(response => response.json())
            .then(function(json) {
                const e = React.createElement;
                const tabs =
                    document.getElementById('videos').getElementsByClassName(
                        'video_link');
                for (const tab of tabs) {
                    const props = json[tab.dataset.show];
                    props.showUsers = INFO.videoViewers;
                    const container = tab.getElementsByClassName('info')[0];

                    ReactDOM.render(e(INFO.Info, props), container);
                }
                const props = json['total'];
                props.showUsers = INFO.dayViewers;
                ReactDOM.render(e('div', null, 'Total: ', e(INFO.Info, props)),
                                document.getElementById('total'));
            });

        fetch(`enrollment?offering_id=${offering_id}`)
            .then(response => response.json())
            .then(json => INFO.setEnrollment(json));
    };

    document.getElementById("close-overlay").onclick = INFO.hideTables;
});