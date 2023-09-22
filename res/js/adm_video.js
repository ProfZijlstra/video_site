window.addEventListener('load', () => {
    // display on page summary when clicking info button
    document.getElementById('info-btn').onclick = function() {
        const offering_id = document.getElementById('container').dataset.oid;
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
                    if (props) {
                        props.showUsers = INFO.videoViewers;
                        const container = tab.getElementsByClassName('info')[0];
                        ReactDOM.render(e(INFO.Info, props), container);    
                    }
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

    // enable video configuration when clicking config button
    document.getElementById("config-btn").onclick = function() {
        const videoLinks = document.querySelectorAll(".video_link");
        for (const div of videoLinks) {
            div.classList.add("config");
        }
        document.getElementById("total").innerHTML = "";
        document.getElementById("back").classList.add("config");
    }

    // show update title modal when clicking title edit icons
    const edit_modal = document.getElementById("edit_modal");
    const overlay = document.getElementById("overlay");
    const edits = document.querySelectorAll(".fa-pen-to-square");
    const video_file = document.getElementById("video_file");
    const video_title = document.getElementById("video_title");
    const modals = document.querySelectorAll(".modal");
    function showEdit(evt) {
        modals.forEach((e) => e.classList.add("hide"));
        video_file.value = this.dataset.file;
        video_title.value = this.dataset.title;
        overlay.classList.add("visible");
        edit_modal.classList.remove("hide");
    }
    for (const edit of edits) {
        edit.onclick = showEdit;
    }

    // make the title input field not trigger key events 
    video_title.onkeydown = function(evt) {
        evt.stopPropagation();
    };

    // enable clicking arrow up to move video up
    const ups = document.querySelectorAll(".fa-arrow-up");
    function moveUp() {
        document.getElementById("up_file").value = this.dataset.file;
        document.getElementById("prev_file").value = this.dataset.prev_file;
        document.getElementById("decreaseSequence").submit();
    }
    for (const up of ups) {
        if (up.classList.contains("disabled")) {
            continue;
        }
        up.onclick = moveUp;
    }

    // enable clicking arrow down to move video up
    const downs = document.querySelectorAll(".fa-arrow-down");
    function moveDown() {
        document.getElementById("down_file").value = this.dataset.file;
        document.getElementById("next_file").value = this.dataset.prev_file;
        document.getElementById("increaseSequence").submit();
    }
    for (const down of downs) {
        if (down.classList.contains("disabled")) {
            continue;
        }
        down.onclick = moveDown;
    }

    // enable clicking plus to show add video modal
    const add_modal = document.getElementById("add_modal");
    document.getElementById("add_video").onclick = function() {
        modals.forEach((e) => e.classList.add("hide"));
        overlay.classList.add("visible");
        add_modal.classList.remove("hide");
        document.getElementById("add_file").focus();
    };

    // overlay closing related code
    document.getElementById("close-overlay").onclick = INFO.hideTables;
    document.getElementById("overlay").onclick = function(evt) {
        if (evt.target == this) {
            INFO.hideTables();
        }
    };
});