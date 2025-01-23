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

    // switch to PDF if hash indicates it
    if (hash && hash == "#pdf") {
        const btn = document.querySelector("article.selected i.pdf");
        showPDF.call(btn, false);
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
            method: 'POST',
            body: `toggle=${state}`,
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
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
    const numOpts = { minimumFractionDigits: 1, maximumFractionDigits: 2 };
    let ignoreRateChange = false;
    function updateSpeed(speed) {
        ignoreRateChange = true;
        speed = parseFloat(speed);
        curSpeed.innerHTML = speed.toLocaleString('en-US', numOpts);
        for (const vid of videos) {
            vid.playbackRate = speed;
        }
        ignoreRateChange = false;
        window.localStorage.setItem("speed", speed);
    }
    function faster() {
        let speed = parseFloat(curSpeed.innerHTML)
        speed += 0.1
        if (speed > 4) {
            speed = 4;
        }
        updateSpeed(speed);
    };
    function slower() {
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
    const storedSpeed = window.localStorage.getItem("speed");
    if (video && storedSpeed) {
        updateSpeed(storedSpeed);
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
                if (video == document.activeElement) {
                    break;
                }
                video.focus();
            case "KeyK":
                if (video?.paused) {
                    video?.play();
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
            case "KeyV":
                const hash = window.location.hash;
                if (hash && hash == "#pdf") {
                    const btn = document.querySelector("article.selected i.video");
                    showVideo.call(btn);
                } else {
                    const btn = document.querySelector("article.selected i.pdf");
                    showPDF.call(btn);
                }
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
        const name = evt.target.parentNode.dataset.name;
        const video_name = encodeURIComponent(name);
        // invalidate any old id that may have still been in the system
        view_id = false;
        // get view_id by posting to start: day_id, video
        const data = new URLSearchParams();
        data.set("day_id", day_id);
        data.set("video", video_name);
        data.set("speed", window.localStorage.getItem("speed"));
        const url = './start?' + data;
        fetch(url, { cache: 'no-cache' })
            .then(response => response.text())
            .then(text => view_id = text);
    }
    let pauseAction = null;
    function pauseHandler() {
        if (view_id) {
            // post view_id to url: stop
            const data = new URLSearchParams();
            data.set("view_id", view_id);
            data.set("speed", window.localStorage.getItem("speed"));
            fetch('./stop', {
                method: 'POST',
                body: data,
                headers:
                    { 'Content-Type': 'application/x-www-form-urlencoded' },
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
        if (ignoreRateChange) {
            return;
        }
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
        ignoreRateChange = true;

        document.querySelector('article.selected video')?.pause();
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
        const video_name = "a" + parent.dataset.show;

        // show the video
        document.querySelector("main article.selected").classList.remove('selected');
        document.getElementById(video_name).classList.add('selected');

        // load the video if it's not already loaded
        const videoTag = document.querySelector(`article.selected video`);
        if (videoTag && !videoTag.src) {
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
        const btn = document.querySelector("article.selected i.video");
        showVideo.call(btn, false);

        // switch video
        switchVideo(video_id);

        // update history
        window.history.pushState({ "id": video_id }, '', `./${video_seq}`);
    }
    function clickTab(evt) {
        evt.preventDefault();

        // update the module wide video_id variable
        video_id = this.parentElement.id;
        genericClick(video_id);
    }
    const video_links = document.querySelectorAll('#tabs div.video_link a');
    for (const link of video_links) {
        link.addEventListener('click', clickTab);
    }
    function clickProgress() {
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
        if (state && state.pdf) {
            const btn = document.querySelector("article.selected i.pdf");
            showPDF.call(btn, false);
        } else {
            const btn = document.querySelector("article.selected i.video");
            showVideo.call(btn, false);
        }
    });
    window.addEventListener('pushstate', (e) => {
        const state = e.state;
        if (state && state.pdf) {
            const btn = document.querySelector("article.selected i.pdf");
            showPDF.call(btn, false);
        } else {
            const btn = document.querySelector("article.selected i.video");
            showVideo.call(btn, false);
        }

    });


    // make clicking on the PDF icon work while communicating with server
    function showPDF(push = true) {
        const pdf = document.querySelector("article.selected object");
        if (!pdf) {
            return;
        }

        // tell the server about view
        const file = this.dataset.file;
        const url = `./pdf?day_id=${day_id}&file=${file}`;
        fetch(url, {
            cache: 'no-cache'
        });

        // update browser history if needed
        if (push) {
            const path = window.location.pathname;
            const video_id = path.substr(-2);
            window.history.pushState({ "id": video_id, "pdf": true }, '', `${path}#pdf`);
        }

        // show / hide page segments
        const vidIcon = document.querySelector("article.selected .media i.fa-video");
        if (vidIcon) {
            vidIcon.classList.remove("hide");
        }
        const pdfIcon = document.querySelector("article.selected .media div.pdf");
        if (pdfIcon) {
            pdfIcon.classList.add('hide');
        }
        const video = document.querySelector("article.selected video");
        if (video) {
            video.classList.add('hide');
        }
        pdf.classList.remove('hide');
    }
    const pdfs = document.querySelectorAll('i.pdf.available');
    pdfs.forEach(
        elm => {
            elm.addEventListener("mousedown", showPDF)
        }
    );

    // make clicking the video icon work
    function showVideo(push = true) {
        const video = document.querySelector("article.selected video");
        if (!video) {
            return;
        }

        // update browser history if needed
        if (push) {
            const path = window.location.pathname;
            const video_id = path.substr(-2);
            window.history.pushState({ "id": video_id }, '', path);
        }

        // show / hide page segments
        const vidIcon = document.querySelector("article.selected .media i.fa-video");
        if (vidIcon) {
            vidIcon.classList.add("hide");
        }
        const pdfIcon = document.querySelector("article.selected .media div.pdf");
        if (pdfIcon) {
            pdfIcon.classList.remove('hide');
        }
        const pdf = document.querySelector("article.selected object");
        if (pdf) {
            pdf.classList.add('hide');
        }
        video.classList.remove('hide');
    }
    document.querySelectorAll("i.video").forEach(e => e.onmousedown = showVideo);

    // make clicking on autoplay work
    function clickAutoplay() {
        const toggles = document.querySelectorAll("i.auto_toggle");
        for (const t of toggles) {
            t.classList.toggle("fa-toggle-off");
            t.classList.toggle("fa-toggle-on");
        }
        if (autoplay == "on") {
            autoplay = "off";
        } else {
            autoplay = "on";
        }
        window.localStorage.setItem("autoplay", autoplay);
    }
    document.querySelectorAll("i.auto_toggle").forEach(elm => {
        elm.addEventListener("mousedown", clickAutoplay);
    });

    // enable autoplay on start based on local storage
    let autoplay = window.localStorage.getItem('autoplay');
    if (autoplay && autoplay == "on") {
        const toggles = document.querySelectorAll("i.auto_toggle");
        for (const t of toggles) {
            t.classList.toggle("fa-toggle-off");
            t.classList.toggle("fa-toggle-on");
        }
        // then also start playing the video
        video.play();
    }

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
        form.classList.add("textContainer");
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
            method: 'POST',
            body: `id=${id}&vid=${vid}&type=${type}`,
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
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


    /**
     * Admin related code
     **/
    const configBtn = document.getElementById("config-btn");
    if (!configBtn) {
        return;
    }

    // global variables for the admin section
    let editLink = null;
    let dragPart = null;

    // enable video configuration when clicking config button
    function goConfig(evt, flip = true) {
        const videoLinks = document.querySelectorAll(".video_link");
        for (const div of videoLinks) {
            div.classList.toggle("config");
        }
        document.getElementById("back").classList.toggle("config");
        document.querySelectorAll("article .media, article .reencode").forEach(e => {
            e.classList.toggle("hide");
        });
        if (flip && window.sessionStorage.getItem("config") == "true") {
            window.sessionStorage.setItem("config", "false");
        } else {
            window.sessionStorage.setItem("config", "true");
        }
    }
    configBtn.onclick = goConfig;
    if (window.sessionStorage.getItem("config") == "true") {
        goConfig(null, false);
    }

    // enable clicking plus to show add  dialog
    document.getElementById("add_part").onmousedown = function() {
        document.getElementById("addDialog").showModal();
        document.getElementById("addTitle").focus();
    };
    function closeAddDialog() {
        document.getElementById("addDialog").close();
    }
    document.getElementById("closeAdd").onmousedown = closeAddDialog;

    // enable clicking edit to show edit dialog
    function editLessonPart() {
        const parent = this.closest(".video_link");
        editLink = parent;

        const title = parent.querySelector("a").innerText;
        const file = parent.querySelector(".config").dataset.file;
        document.getElementById("editDialog").showModal();
        const editTitle = document.getElementById("editTitle");
        editTitle.value = title;
        editTitle.dataset.file = file;
        document.getElementById("editTitle").focus();
    }
    document.querySelectorAll(".video_link i.fa-pen-to-square").forEach(
        e => e.onmousedown = editLessonPart
    );
    function closeEditDialog() {
        document.getElementById("editDialog").close();
    }
    document.getElementById("closeEdit").onmousedown = closeEditDialog;

    // submit edit should:
    // 1. send to server
    // 2. update the tab
    // 3. update the article title
    //
    // .onclick is used so that enter inside the textfield also triggers it
    document.getElementById("editBtn").onclick = function(evt) {
        evt.preventDefault();

        const editTitle = document.getElementById("editTitle");
        const title = editTitle.value;
        const file = editTitle.dataset.file;
        if (title.indexOf("_") !== -1) {
            alert("Title cannot not contain underscores.\n"
                + "Please remove the underscore and try again");
            return;
        }
        closeEditDialog();

        const data = new FormData();
        data.append("title", title);
        data.append("file", file);
        fetch('title', {
            method: "POST",
            body: data,
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error("Updating title failed");
                }

                // change title on page
                const tab = editLink.querySelector("a");
                tab.innerText = title;
                const id = "a" + editLink.id;
                const hdr = document.querySelector(`#${id} h2`);
                hdr.innerText = title;
                const config = tab.parentNode.querySelector(".config");
                const parts = file.split('_');
                parts[1] = title;
                config.dataset.file = parts.join('_');
            })
            .catch(e => alert(e));
    }

    // validate before submitting
    document.getElementById("addBtn").onclick = function(evt) {
        const title = document.getElementById("addTitle").value;
        if (title.indexOf("_") !== -1) {
            alert("Title cannot not contain underscores.\n"
                + "Please remove the underscore and try again");
            return false;
        }
    }

    function deleteLessonPart() {
        if (window.confirm('Delete this lesson part?')) {
            const parent = this.closest(".video_link");
            const file = parent.querySelector(".config").dataset.file;
            document.getElementById("deletePart").value = file;
            document.getElementById("deleteForm").submit();
        }
    }
    document.querySelectorAll(".video_link i.fa-trash-can").forEach(e =>
        e.onmousedown = deleteLessonPart
    );

    const tabList = document.getElementById("tabs");

    tabList.addEventListener('dragstart', (e) => {
        dragPart = e.target.closest('div.video_link');
        dragPart.classList.add('dragging');
    });
    tabList.addEventListener('dragend', () => {
        dragPart.classList.remove('dragging');
        dragPart = null;
        const tabs = tabList.children;
        const seqs = [];
        const ids = [];
        for (let i = 0; i < tabs.length; i++) {
            seqs.push(tabs[i].dataset.seq)
            ids.push(tabs[i].id);
            let newSeq = i + 1;
            if (newSeq < 10) {
                newSeq = '0' + newSeq;
            }
            tabs[i].dataset.seq = newSeq;
        }

        fetch('./reorder', {
            method: 'POST',
            body: `order=${seqs.join(',')}`,
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error("Server reordering failed");
                }
                const speed = window.localStorage.getItem("speed");
                fixUrls(ids);
                updateSpeed(speed);
            })
            .catch(e => alert(e));
    });
    function fixUrls(ids) {
        for (const key in ids) {
            let newSeq = parseInt(key) + 1;
            if (newSeq < 10) {
                newSeq = '0' + newSeq;
            }
            const id = ids.shift();
            const article = document.getElementById(`a${id}`);
            const video = article.querySelector('video');
            const src = video.dataset.src;
            const dirs = src.split('/');
            const partDir = dirs[dirs.length - 2];
            const parts = partDir.split('_');
            parts[0] = newSeq;
            dirs[dirs.length - 2] = parts.join('_');
            video.dataset.src = dirs.join('/');
            video.setAttribute('src', dirs.join('/'));

            const pdf = article.querySelector('object');
            const data = pdf.data;
            const dirs2 = data.split('/');
            const partDir2 = dirs2[dirs2.length - 2];
            const parts2 = partDir2.split('_');
            parts2[0] = newSeq;
            dirs2[dirs2.length - 2] = parts2.join('_');
            pdf.data = dirs2.join('/');
            pdf.querySelector('a').setAttribute('href', dirs2.join('/'));
        }
    }
    tabList.addEventListener('dragover', (e) => {
        e.preventDefault();
        const target = e.target;
        if (dragPart && dragPart != target && target.tagName === 'DIV') {
            const bounding = target.getBoundingClientRect();
            const offset = e.clientY - bounding.top;
            const middle = bounding.height / 2;

            if (offset > middle) {
                target.insertAdjacentElement('afterend', dragPart);
            } else {
                target.insertAdjacentElement('beforebegin', dragPart);
            }
        }
    });

    function uploadHandler() {
        const file = document.getElementById('file');
        document.getElementById("part").value = this.dataset.part;
        file.click();
    }
    const uploads = document.querySelectorAll('div.media.upload');
    uploads.forEach(e => e.onmousedown = uploadHandler);
    document.getElementById('file').onchange = function() {
        const form = document.getElementById('uploadForm');
        form.submit();
    }

    setTimeout(() => {
        // show warning if video is not optimized for streaming
        if (window.sessionStorage.getItem("config") == "true") {
            let reenc = document.querySelector("article.selected .reencode");
            if (reenc) {
                alert("This video is not optimized for streaming.\n\n" +
                    "Please reencode it by clicking on the paint roller icon" +
                    " in the top right corner of the video.");
            }
        }
    }, 100);
    function clickReencode() {
        const part = this.dataset.part;
        fetch('./reencode', {
            method: 'POST',
            body: `part=${part}`,
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error("Server reencoding failed");
                }
            })
            .catch(e => alert(e));
        this.remove();
        alert("Reencoding started. It may take a while before the video is ready.");
    }
    document.querySelectorAll("article div.reencode").forEach((e) => {
        e.onmousedown = clickReencode;
    });
});
