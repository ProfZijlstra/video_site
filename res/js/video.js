window.addEventListener('load', () => {
    // 'global' variables needed by a couple of functions
    const day_id = document.getElementById('day').dataset.id;
    const video = document.querySelector("video");

    // highlight url selected question
    const hash = window.location.hash;
    if (hash && hash != 'questionForm' && (hash[1] == 'q' || hash[1] == 'r')) {
        const elm = document.getElementById(hash.substring(1));
        if (elm) {
            elm.classList.add('selected');
            setTimeout(() => {
                elm.classList.remove('selected');
                elm.classList.add('selectDone');
            }, 2500)
            setTimeout(() => elm.classList.remove('selectDone'), 5000);
        }
    }

    // start video of autoplay is on
    if (document.getElementById("auto_toggle")
            .classList.contains("fa-toggle-on")) {
        document.querySelector('video').play();
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
        const video_name = encodeURIComponent(evt.target.parentNode.id);
        // invalidate any old id that may have still been in the system
        view_id = false;
        // get view_id by posting to start: day_id, video
        const url = `./start?day_id=${day_id}&video=${video_name}`;
        fetch(url, {cache : 'no-cache'})
            .then(response => response.text())
            .then(text => view_id = text);
    }
    function pauseHandler() {
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
    function endedHandler() {
        const toggle = document.getElementById("auto_toggle");
        if (toggle.classList.contains("fa-toggle-on")) {
            const tab = document.querySelector(".video_link.selected");
            const nextTab = tab.nextElementSibling.querySelector('a');
            // wait half a second to make sure that the pause handler
            // reports the video stop event to the server
            setTimeout(() => nextTab.click(), 500);
        }
    }
    video.addEventListener('play', playHandler);
    video.addEventListener('pause', pauseHandler);
    video.addEventListener('ended', endedHandler);

    // make clicking on the PDF icon work while communicating with server
    document.getElementById('pdf').onclick = function(evt) {
        const file = this.dataset.file;
        const href = this.href;
        const url = `./pdf?day_id=${day_id}&file=${file}`;
        fetch(url, {cache : 'no-cache'}).then(() => {window.location = href});
        evt.preventDefault();
    };
    // disable right-clicking on PDF (no download without view)
    document.getElementById('pdf').oncontextmenu = function(evt) {
        evt.preventDefault();
    }

    // make clicking on autoplay work
    document.getElementById("autoplay").onclick =
        function() {
        const toggle = document.getElementById("auto_toggle");
        toggle.classList.toggle("fa-toggle-off");
        toggle.classList.toggle("fa-toggle-on");
        let state = 'off';
        if (toggle.classList.contains('fa-toggle-on')) {
            state = 'on';
        }
        fetch('./autoplay', {
            method : 'POST',
            body : `toggle=${state}`,
            headers : {'Content-Type' : 'application/x-www-form-urlencoded'},
        });
    }

    // make clicking on delete question and delete reply work
    function
    delHandler() {
        if (window.confirm('Do you really want to delete?')) {
            this.parentNode.submit();
        }
    } const dels = document.getElementsByClassName('fa-trash-alt');
    for (const del of dels) {
        del.addEventListener('click', delHandler);
    }

    // make clicking on edit question and edit reply work
    function createEditBox(action, btn, id, content, placeholder, cancelFn) {
        const form = document.createElement('form');
        form.setAttribute('method', 'post');
        form.setAttribute('action', action);
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
        text.setAttribute('placeholder', placeholder);
        text.classList.add('questionText');
        text.append(content);
        form.append(text);
        const submit = document.createElement('input');
        submit.setAttribute('type', 'submit');
        submit.setAttribute('value', btn);
        submit.classList.add('textAction');
        form.append(submit);
        const cancel = document.createElement('button');
        cancel.setAttribute('type', 'button');
        cancel.append("Cancel");
        cancel.classList.add('cancel');
        cancel.onclick = function() {
            form.remove();
            cancelFn();
        };
        form.append(cancel);
        return form;
    }
    function editHandler(type, evt) {
        const id = evt.target.dataset.id;
        fetch(`get${type}?id=${id}`)
            .then(response => response.json())
            .then(json => {
                const initial = evt.target.parentNode.nextSibling.nextSibling;
                const form =
                    createEditBox(`upd${type}`, "Update", id, json.text, "",
                                  () => initial.style.display = 'block');
                evt.target.parentNode.after(form);
                initial.style.display = 'none';
            });
    }
    const question_edits =
        document.querySelectorAll('#questions > .author > .fa-edit');
    for (const edit of question_edits) {
        edit.addEventListener('click', editHandler.bind(null, "Question"));
    }
    const reply_edits =
        document.querySelectorAll('.question > .author > .fa-edit');
    for (const edit of reply_edits) {
        edit.addEventListener('click', editHandler.bind(null, "Reply"));
    }
    // make clicking on upvote and downvote question and reply work
    function voteHandler(url, evt) {
        const parent = evt.target.parentNode;
        const id = parent.dataset.id; // either question_id or reply_id
        const vid =
            parent.dataset.vid; // vote id (in question_vote or reply_vote)
        const type = parent.dataset.type;
        fetch(`./${url}`, {
            method : 'POST',
            body : `id=${id}&vid=${vid}&type=${type}`,
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
    const question_ups = document.querySelectorAll(
        '#questions > .author > .vote > .fa-angle-up');
    const question_downs = document.querySelectorAll(
        '#questions > .author > .vote > .fa-angle-down');
    for (const up of question_ups) {
        up.addEventListener('click', voteHandler.bind(null, 'upvote'));
    }
    for (const down of question_downs) {
        down.addEventListener('click', voteHandler.bind(null, 'downvote'));
    }
    const reply_ups =
        document.querySelectorAll('.question > .author > .vote > .fa-angle-up');
    const reply_downs = document.querySelectorAll(
        '.question > .author > .vote > .fa-angle-down');
    for (const up of reply_ups) {
        up.addEventListener('click', voteHandler.bind(null, 'upreply'));
    }
    for (const down of reply_downs) {
        down.addEventListener('click', voteHandler.bind(null, 'downreply'));
    }

    function createReply() {
        const qid = this.parentNode.id.substring(1);
        const placeholder = `Use **markdown** syntax in your text like: 

\`\`\`javascript
const code = "highlighted";
\`\`\``;
        const form = createEditBox("addReply", "Reply", qid, "", placeholder,
                                   () => this.style.display = "block");
        const container = document.createElement("div");
        container.classList.add("replyContainer");
        container.append(form);
        this.after(container);
        this.style.display = "none";
    }
    const replies = document.getElementsByClassName("addReply");
    for (const reply of replies) {
        reply.addEventListener('click', createReply);
    }
});
