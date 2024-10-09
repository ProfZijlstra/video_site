window.addEventListener('load', () => {
    // 'global' variables needed by a couple of functions
    const day_id = document.getElementById('day').dataset.id;
    const videos = document.querySelectorAll('video');
    let video = document.querySelector("article.selected video");
    let video_id = document.querySelector(".video_link.selected").id;

    // disable right clicking
    for (const vid of videos) {
        vid.oncontextmenu = function() {
            return false;
        }
    }

    // highlight url selected comment
    const hash = window.location.hash;
    if (hash && hash != 'commentForm' && (hash[1] == 'q' || hash[1] == 'r')) {
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

    // hide / show video list (theater mode)
    const nav = document.querySelector("nav#videos");
    function toggleTheater() {
        nav.classList.toggle("hidden");
        document.getElementById("bars").classList.toggle("theater");

        let state = '';
        if (nav.classList.contains('hidden')) {
            state = 'hidden';
        }
        fetch('./theater', {
            method : 'POST',
            body : `toggle=${state}`,
            headers : {'Content-Type' : 'application/x-www-form-urlencoded'},
        });
    };
    document.getElementById("bars").onclick = toggleTheater; 

    // auto toggle theater mode if the window.width is below 900
    function checkToggle() {
        if (!nav.classList.contains('hidden') 
                && window.innerWidth <= 900) {
            toggleTheater()
        }
    }
    window.onresize = checkToggle;
    checkToggle();

    // video speed controls
    const curSpeed = document.querySelector('.curSpeed');
    const numOpts = {minimumFractionDigits : 1, maximumFractionDigits : 2};
    function updateSpeed(speed) {
        const curSpeeds = document.querySelectorAll('.curSpeed');
        for (const elm of curSpeeds) {
            elm.innerHTML = speed.toLocaleString('en-US', numOpts);
        }
        for (const vid of videos) {
            vid.playbackRate = speed;
        }
        // post the speed to the server
        fetch('./speed', {
            method : 'POST',
            body : `speed=${speed}`,
            headers : {'Content-Type' : 'application/x-www-form-urlencoded'},
        });
    }
    function faster(e) {
        let speed = parseFloat(curSpeed.innerHTML)
        speed += 0.1
        if (speed > 4) {
            speed = 4;
        }
        updateSpeed(speed);
    };
    function slower(e) {
        let speed = parseFloat(curSpeed.innerHTML)
        speed -= 0.1
        if (speed < 0.3) {
            speed = 0.3;
        }
        updateSpeed(speed);
    };
    function normalSpeed() {
        const speed = 1.0;
        updateSpeed(speed);
    }
    curSpeed.onclick = normalSpeed;
    document.querySelectorAll('.faster').forEach(
        elm => elm.onclick = faster
    );
    document.querySelectorAll('.slower').forEach(
        elm => elm.onclick = slower
    );

    // set speed when page is loaded
    if (video) {
        const speed = parseFloat(curSpeed.innerHTML);
        for (const vid of videos) {
            vid.playbackRate = speed;
        }
    }

    function clickMobileNav(e) {
        e.preventDefault();
        genericClick(this.dataset.video);
    }
    function nextVideo() {
        const tab = document.querySelector(".video_link.selected");
        if (tab.nextElementSibling) {
            const nextTab = tab.nextElementSibling.querySelector('a');
            nextTab.click();    
        }
    }
    document.querySelectorAll("nav.mobileNav .next a").forEach(
        e => e.onclick = clickMobileNav
    );
    function prevVideo() {
        const tab = document.querySelector(".video_link.selected");
        if (tab.previousElementSibling) {
            const prevTab = tab.previousElementSibling.querySelector('a');
            prevTab.click();    
        }
    }
    document.querySelectorAll("nav.mobileNav .prev a").forEach(
        e => e.onclick = clickMobileNav
    );

    // keyboard controls
    video?.focus();
    document.addEventListener('keydown', (e) => {
        // don't event handle for text input fields
        if (e.target.tagName == "TEXTAREA" || e.target.tagName == "INPUT") {
            return;
        }
        video = document.querySelector("article.selected video");
        switch (e.code) {
        case "Space":
            e.preventDefault();
        case "KeyK":
            if (video?.paused) {
                video?.play()
            } else {
                video?.pause();
            }
            break;
        case "ArrowLeft":
            video.currentTime -= 10;
            break;
        case "KeyJ":
            video.currentTime -= 5;
            break;
        case "ArrowRight":
            video.currentTime += 10;
            break;
        case "KeyL":
            video.currentTime += 5;
            break;
        case "BracketLeft":
            slower();
            break;
        case "BracketRight":
            faster();
            break;
        case "Digit0":
            normalSpeed();
            break;
        case "KeyA":
            clickAutoplay();
            break;
        case "KeyF":
            if (document.fullscreenElement) {
                document.exitFullscreen();
            } else {
                video?.requestFullscreen();
            }
            break;
        case "KeyT":
            document.getElementById("bars").click();
            break;
        case "KeyN":
            nextVideo();
            break;
        case "KeyP":
            prevVideo();
            break;
        case "KeyD":
            document.getElementById("pdf").click();
        }
    });
    // show keyboard shortcuts
    function showHideShortcuts() {
        document.querySelectorAll("div.keyboard").forEach(
            elm => elm.classList.toggle("hidden")
        );
    }
    document.querySelectorAll("i.shortcuts").forEach(elm => {
        elm.addEventListener("mousedown", showHideShortcuts);
    });

    // play and pause events are communicated to the server
    let view_id = false;
    function playHandler(evt) {
        window.scrollTo(0, 80);
        this.playbackRate = parseFloat(curSpeed.innerHTML);;
        const video_name = encodeURIComponent(evt.target.parentNode.id);
        // invalidate any old id that may have still been in the system
        view_id = false;
        // get view_id by posting to start: day_id, video
        const url = `./start?day_id=${day_id}&video=${video_name}`;
        fetch(url, {cache : 'no-cache'})
            .then(response => response.text())
            .then(text => view_id = text);
    }
    let pauseAction = null;
    function pauseHandler(action) {
        if (view_id) {
            // post view_id to url: stop
            fetch('./stop', {
                method : 'POST',
                body : `view_id=${view_id}`,
                headers :
                    {'Content-Type' : 'application/x-www-form-urlencoded'},
            }).then(() => {
                if (pauseAction) {
                    pauseAction();
                    pauseAction = null;
                }
            });
        }
    }
    function endedHandler() {
        const toggle = document.querySelector("i.auto_toggle");
        if (toggle.classList.contains("fa-toggle-on")) {
            const tab = document.querySelector(".video_link.selected");
            const nextTab = tab.nextElementSibling.querySelector('a');
            // wait half a second to smooth the transition
            setTimeout(() => nextTab.click(), 500);
        }
    }
    function ratechangeHandler(evt) {
        const speed = evt.target.playbackRate;
        updateSpeed(speed);
    }
    for (const vid of videos) {
        vid.addEventListener('play', playHandler);
        vid.addEventListener('pause', pauseHandler);
        vid.addEventListener('ended', endedHandler);
        vid.addEventListener('ratechange', ratechangeHandler);
    }

    // clicking on another video (or pdf) first sends a 'pause' to current video
    function stopBeforeClick(evt) {
        if (video && !video.paused) {
            evt.preventDefault();
            pauseAction = function() {
                evt.target.click();
            };
            video?.pause();
        }
    }
    const anchors = document.getElementsByTagName('a');
    for (const a of anchors) {
        a.addEventListener('click', stopBeforeClick);
    }

    // clicking on a video link switches the browser URL
    function switchVideo(video_id) {
        document.querySelectorAll('video').forEach(vid => vid.pause());
        if (document.fullscreenElement) {
            document.exitFullscreen();
        }

        // siwtch tab
        const tab = document.querySelector(".video_link.selected");
        if (tab) {
            tab.classList.remove('selected');
        }

        const parent = document.getElementById(video_id);
        parent.classList.add('selected');
        const video_name = parent.dataset.show;

        // show the video
        document.querySelector("main article.selected").classList.remove('selected');
        document.getElementById(video_name).classList.add('selected');

        // load the video if it's not already loaded
        const videoTag = document.querySelector(`article.selected video`);
        if (!videoTag.src) {
            videoTag.src = videoTag.dataset.src;
        }

        const auto = document.querySelector('i.auto_toggle');
        if (auto.classList.contains('fa-toggle-on')) {
            video = document.querySelector("article.selected video");
            video?.play();
        }
    }
    function genericClick(video_id) {
        const video_seq = encodeURIComponent(video_id);

        // switch video
        switchVideo(video_id);

        // update history
        window.history.pushState({"id": video_id}, '', `./${video_seq}`);
    }
    function clickTab(evt) {
        evt.preventDefault();

        // update the module wide video_id variable
        video_id = this.parentElement.parentElement.id;
        genericClick(video_id);
    }
    const video_links = document.querySelectorAll('#tabs div.video_link a');
    for (const link of video_links) {
        link.addEventListener('click', clickTab);
    }
    function clickProgress(evt) {
        const video_id = this.dataset.vid;
        genericClick(video_id);
    }
    const progressTabs = document.querySelectorAll('div.progress div.tab');
    for (const tab of progressTabs) {
        tab.addEventListener('click', clickProgress);
    }

    // using the browser's back button will pop the history
    const initial_id = document.querySelector(".video_link.selected").id;
    window.addEventListener('popstate', (e) => {
            const state = e.state;
            if (state && state.id) {
                switchVideo(state.id);
            } else {
                switchVideo(initial_id);
            }
    });


     // make clicking on the PDF icon work while communicating with server
    function openPDF(evt) {
        const file = this.dataset.file;
        const href = this.href;
        const url = `./pdf?day_id=${day_id}&file=${file}`;
        fetch(url, {
            cache : 'no-cache'
        }).then(() => {window.open(href, '_blank')});
        evt.preventDefault();
    }
    function disableRightClick(evt) {
        evt.preventDefault();
    }
    const pdfs = document.querySelectorAll('pdf');
    pdfs.forEach(
        elm => {
            elm.addEventListener("mousedown", openPDF)
            elm.addEventListener("contextmenu", disableRightClick);
        }
    );

    // make clicking on autoplay work
    function clickAutoplay() {
        const toggles = document.querySelectorAll("i.auto_toggle");
        for (const t of toggles) {
            t.classList.toggle("fa-toggle-off");
            t.classList.toggle("fa-toggle-on");
        }
        let state = 'off';
        if (toggles[0].classList.contains('fa-toggle-on')) {
            state = 'on';
        }
        fetch('./autoplay', {
            method : 'POST',
            body : `toggle=${state}`,
            headers : {'Content-Type' : 'application/x-www-form-urlencoded'},
        });
    }
    document.querySelectorAll("i.auto_toggle").forEach(elm => {
        elm.addEventListener("click", clickAutoplay);
    });

    // used for both creating and updating comments and replies
    function ceaseShiftText() {
        const text = this.elements.text;
        const markdown = text.value;
        const shifted = MARKDOWN.ceasarShift(markdown);
        text.value = shifted;
    }

    // connect ceasar shift to new comment submit
    document.querySelectorAll('.commentForm').forEach(
        elm => elm.addEventListener("submit", ceaseShiftText)
    );    

    // make clicking on delete comment and delete reply work
    function delHandler() {
        if (window.confirm('Do you really want to delete?')) {
            this.parentNode.submit();
        }
    } 
    const dels = document.getElementsByClassName('fa-trash-alt');
    for (const del of dels) {
        del.addEventListener('click', delHandler);
    }

    // make clicking on edit comment and edit reply work
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
        tab.setAttribute('value', video_id);
        form.append(tab);
        const text = document.createElement('textarea');
        text.setAttribute('name', 'text');
        text.setAttribute('placeholder', placeholder);
        text.classList.add('commentText');
        text.append(content);
        form.append(text);
        const actions = document.createElement('div');
        actions.classList.add('commentActions');
        const mdBtn = document.createElement('button');
        mdBtn.setAttribute('type', 'button');
        mdBtn.innerText = "Preview Markdown";
        mdBtn.onclick = MARKDOWN.getHtmlForMarkdown;
        // to match what makdown.js wants
        const extra = document.createElement('span'); 
        extra.append(mdBtn);
        actions.append(extra);
        const submit = document.createElement('button');
        submit.setAttribute('type', 'submit');
        submit.innerText = btn;
        actions.append(submit);
        const cancel = document.createElement('button');
        cancel.setAttribute('type', 'button');
        cancel.append("Cancel");
        cancel.onclick = function() {
            form.remove();
            cancelFn();
        };
        actions.append(cancel);
        const preview = document.createElement('div');
        preview.classList.add("previewArea");
        actions.append(preview);
        form.append(actions);
        form.onsubmit = ceaseShiftText;
        return form;
    }
    function editHandler(type, evt) {
        const target = evt.target;
        const id = target.dataset.id;
        const del = target.parentNode.querySelector("i.fa-trash-alt");
        target.style.display = "none";
        del.style.display = "none";
        fetch(`get${type}?id=${id}`)
            .then(response => response.json())
            .then(json => {
                const initial = evt.target.parentNode.nextSibling.nextSibling;
                const form =
                    createEditBox(`upd${type}`, "Update", id, json.text, "",
                                  () => { 
                                    initial.style.display = 'block'; 
                                    target.style.display = "inline"
                                    del.style.display = 'inline';
                                });
                evt.target.parentNode.after(form);
                initial.style.display = 'none';
            });
    }
    const comment_edits =
        document.querySelectorAll('#comments > .author > .fa-edit');
    for (const edit of comment_edits) {
        edit.addEventListener('click', editHandler.bind(null, "Comment"));
    }
    const reply_edits =
        document.querySelectorAll('.comment > .author > .fa-edit');
    for (const edit of reply_edits) {
        edit.addEventListener('click', editHandler.bind(null, "Reply"));
    }

    // make 'add reply' links work
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
    
    // make clicking on upvote and downvote comment and reply work
    function voteHandler(url, evt) {
        const parent = evt.target.parentNode;
        const id = parent.dataset.id; // either comment_id or reply_id
        const vid =
            parent.dataset.vid; // vote id (in comment_vote or reply_vote)
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
    const comment_ups = document.querySelectorAll(
        '#comments > .author > .vote > .fa-angle-up');
    const comment_downs = document.querySelectorAll(
        '#comments > .author > .vote > .fa-angle-down');
    for (const up of comment_ups) {
        up.addEventListener('click', voteHandler.bind(null, 'upvote'));
    }
    for (const down of comment_downs) {
        down.addEventListener('click', voteHandler.bind(null, 'downvote'));
    }
    const reply_ups =
        document.querySelectorAll('.comment > .author > .vote > .fa-angle-up');
    const reply_downs = document.querySelectorAll(
        '.comment > .author > .vote > .fa-angle-down');
    for (const up of reply_ups) {
        up.addEventListener('click', voteHandler.bind(null, 'upreply'));
    }
    for (const down of reply_downs) {
        down.addEventListener('click', voteHandler.bind(null, 'downreply'));
    }

    // make markdown preview work
    MARKDOWN.enablePreview("../markdown");
});
