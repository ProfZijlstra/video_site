window.addEventListener('load', () => {
    // 'global' variables needed by a couple of functions
    const day_id = document.getElementById('day').dataset.id;
    const video = document.querySelector("video");

    // highlight url selected question
    const hash = window.location.hash;
    if (hash && hash != 'questionForm' && hash[1] == 'q') {
        const elm = document.getElementById(hash.substring(1));
        elm.classList.add('selected');
        setTimeout(() => {elm.classList.add('selectDone')}, 2500)
    }

    // video speed controls
    const curSpeed = document.getElementById('curSpeed');
    const numOpts = {minimumFractionDigits : 1, minimumFractionDigits : 1};
    document.getElementById('faster').onclick = function(e) {
        let speed = parseFloat(curSpeed.innerHTML)
        speed += 0.1
        curSpeed.innerHTML = speed.toLocaleString('en-US', numOpts);
        video.playbackRate = speed;
    };
    document.getElementById('slower').onclick = function(e) {
        let speed = parseFloat(curSpeed.innerHTML)
        speed -= 0.1
        curSpeed.innerHTML = speed.toLocaleString('en-US', numOpts);
        video.playbackRate = speed;
    };

    // play and pause events are communicated to the server
    let view_id = false;
    function playHandler(evt) {
        let video = encodeURIComponent(evt.target.parentNode.id);
        // get view_id by posting to start: day_id, video
        let url = `./start?day_id=${day_id}&video=${video}`;
        fetch(url, {cache : 'no-cache'})
            .then(response => response.text())
            .then(text => view_id = text);
    }
    function pauseHandler(evt) {
        if (view_id) {
            // post view_id to url: stop
            fetch('./stop', {
                method : 'POST',
                body : `view_id=${view_id}`,
                headers :
                    {'Content-Type' : 'application/x-www-form-urlencoded'},
            });
        }
    }
    video.addEventListener('play', playHandler)
    video.addEventListener('pause', pauseHandler);

    // make clicking on the PDF icon work while communicating with the server
    document.getElementById('pdf').onclick = function(evt) {
        const file = this.dataset.file;
        const href = this.href;
        let url = `./pdf?day_id=${day_id}&file=${file}`;
        fetch(url, {cache : 'no-cache'}).then(() => {window.location = href});
        evt.preventDefault();
    };

    // make clicking on delete question work
    function delHandler() {
        if (window.confirm('Do you really want to delete?')) {
            this.parentNode.submit();
        }
    }
    const dels = document.getElementsByClassName('fa-trash-alt');
    for (const del of dels) {
        del.addEventListener('click', delHandler);
    }

    // make clicking on edit question work
    function createEditBox(json, id, parent) {
        const form = document.createElement('form');
        form.setAttribute('method', 'post');
        form.setAttribute('action', 'updQuestion');
        form.style.position = 'relative';
        const qid = document.createElement('input');
        qid.setAttribute('type', 'hidden');
        qid.setAttribute('name', 'id');
        qid.setAttribute('value', id);
        form.append(qid);
        const tab = document.createElement('input');
        tab.setAttribute('type', 'hidden');
        tab.setAttribute('name', 'tab');
        tab.setAttribute('value', document.getElementById('tab').value);
        form.append(tab);
        const text = document.createElement('textarea');
        text.setAttribute('name', 'text');
        text.classList.add('questionText');
        text.append(json.question);
        form.append(text);
        const submit = document.createElement('input');
        submit.setAttribute('type', 'submit');
        submit.setAttribute('value', 'Update');
        submit.classList.add('textAction');
        form.append(submit);
        parent.after(form);
        form.nextSibling.nextSibling.style.display = 'none';
    }
    function editHandler() {
        const id = this.dataset.id;
        fetch(`getQuestion?qid=${id}`)
            .then(response => response.json())
            .then(json => createEditBox(json, id, this.parentNode));
    }
    const edits = document.getElementsByClassName('fa-edit');
    for (const edit of edits) {
        edit.addEventListener('click', editHandler);
    }

    // make clicking on upvote and downvote question work
    function voteHandler(url, evt) {
        const parent = evt.target.parentNode;
        const qid = parent.dataset.qid;
        const vid = parent.dataset.vid;
        const type = parent.dataset.type;
        fetch(`./${url}`, {
            method : 'POST',
            body : `qid=${qid}&vid=${vid}&type=${type}`,
            headers : {'Content-Type' : 'application/x-www-form-urlencoded'},
        })
            .then(response => response.json())
            .then((json) => {
                const was_selected = evt.target.classList.contains('selected');
                const selected = parent.getElementsByClassName('selected');
                for (const elm of selected) {
                    elm.classList.remove('selected');
                }
                if (!was_selected) {
                    evt.target.classList.add('selected');
                }
                parent.dataset.vid = json.vid;
                if (type) {
                    parent.dataset.type = '';
                } else {
                    parent.dataset.type = json.type;
                }
            });
    }
    const upHandler = voteHandler.bind(null, 'upvote');
    const downHandler = voteHandler.bind(null, 'downvote');
    const ups = document.getElementsByClassName('fa-angle-up');
    const downs = document.getElementsByClassName('fa-angle-down');
    for (const up of ups) {
        up.addEventListener('click', upHandler);
    }
    for (const down of downs) {
        down.addEventListener('click', downHandler);
    }

    // Admin info
    const info = document.getElementById('info-btn');
    if (info) {
        info.onclick = function() {
            fetch(`info?day_id=${day_id}`)
                .then(function(response) { return response.json(); })
                .then(function(json) {
                    const e = React.createElement;
                    const tabs = document.getElementById('videos')
                                     .getElementsByClassName('video_link');
                    for (const tab of tabs) {
                        const props = json[tab.dataset.show];
                        const container = tab.getElementsByClassName('info')[0];
                        ReactDOM.render(e(INFO.Info, props), container);
                    }
                    ReactDOM.render(
                        e('div', null, 'Total: ', e(INFO.Info, json['total'])),
                        document.getElementById('total'));
                });
        };
    }
});
