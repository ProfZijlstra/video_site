window.addEventListener("load", () => {    
    // data needed by multiple functions
    const quiz_id = document.getElementById('quiz_id').dataset.id;

    // timer code
    COUNTDOWN.start(() => window.location.reload());

    // enable markdown previews
    MARKDOWN.enablePreview("../markdown");
    MARKDOWN.activateButtons(saveQuestionChange)

    // automatically save changes to answers
    function saveQuestionChange() {
        let parent = this.parentNode;
        while (!parent.classList.contains('question')) {
            parent = parent.parentNode;
        }
        const text = parent.querySelector("textarea.answer");

        const qid = parent.dataset.id;
        const aid = text.dataset.id;
        const shifted = MARKDOWN.ceasarShift(text.value);
        const answer = encodeURIComponent(shifted);
        const hasMD = text.parentNode.querySelector("i")
                .classList.contains("active") ? 1 : 0;

        fetch(`${quiz_id}/question/${qid}/text`, {
            method : "POST",
            body : `answer=${answer}&answer_id=${aid}&hasMarkDown=${hasMD}`,
            headers :
                {'Content-Type' : 'application/x-www-form-urlencoded'},
        })
        .then((response) => response.json())
        .then((data) => {
            text.dataset.id = data.answer_id;
        });
    }
    const areas = document.querySelectorAll('.qcontainer textarea');
    for (const area of areas) {
        area.onchange = saveQuestionChange;
    }

    // enable image question image uploads
    function uploadImage() {
        const img = this.parentNode.parentNode.querySelector('img');
        // if there already is an image, get the answer_id from it
        let aid = false;
        if (!img.classList.contains('hide')) {
            aid = img.dataset.id;
        }
        const qid = this.parentNode.parentNode.dataset.id;
        const spinner = this.parentNode.querySelector('i.fa-circle-notch');
        const anchor = this.parentNode.querySelector('a');
        spinner.classList.add('rotate');
        const data = new FormData();
        data.append("answer_id", aid);
        data.append("image", this.files[0]);

        fetch(`${quiz_id}/question/${qid}/image`, {
            method: "POST",
            body: data
        })
        .then((response) => response.json())
        .then((data) => {
            if (data.error) {
                alert(data.error);
            } else {
                img.src = data.dst;
                img.dataset.id = data.answer_id;
                img.classList.remove('hide');
                anchor.href = data.dst;
                const name = data.dst.split('/').pop();
                anchor.innerText = name;
            }
            spinner.classList.remove('rotate');    
        });

    }
    const files = document.querySelectorAll("div.question input[type=file]");
    for (const file of files) {
        file.onchange = uploadImage;
    }
    const uploads = document.querySelectorAll("i.fa-upload");
    for (const upload of uploads) {
        upload.onclick = () => {
            upload.parentNode.querySelector("input[type=file]").click();
        }
    }
    
    // enable camera uploads
    let videoDevs = undefined;
    let deviceIdx = 0;
    function openCamera() {
        const parent = this.closest('div');
        const video = parent.querySelector('video');
        const switchCam = parent.querySelector('.switchCamera');
        const close = parent.querySelector(".closeCamera");
        const camera = parent.querySelector('div.camera');
        const spinner = parent.querySelector('i.fa-circle-notch');
        const img = parent.querySelector('img');
        const pic = parent.querySelector('div.takePicture');
        spinner.classList.add('rotate');
        navigator.mediaDevices.getUserMedia({ video: true, audio: false})
            .then((stream) => {
                video.srcObject = stream;
                video.classList.add('show');
                video.play();
                img.classList.add('hide');
                camera.classList.remove('hide');
                close.classList.remove('hide');
                pic.classList.remove('hide');
                spinner.classList.remove('rotate');
                if (videoDevs && videoDevs.length > 1) {
                    switchCam.classList.remove('hide');
                }
            })
            .catch(() => {
                alert("Unable to open camera");
                spinner.classList.remove('rotate');
            });
        navigator.mediaDevices.enumerateDevices()
            .then(devices => {
                videoDevs = devices.filter(d => d.kind == "videoinput");
            });
    }
    document.querySelectorAll("span i.fa-camera").forEach((camera) => {
        camera.onclick = openCamera;
    });

    function stopCamera() {
        const parent = this.parentNode.closest('div.camera');
        const video = parent.querySelector('video');
        const tracks = video.srcObject.getTracks();
        tracks.forEach(track => track.stop());
        parent.classList.add('hide');
    }
    document.querySelectorAll('div.closeCamera').forEach((div) => {
        div.onclick = function() {
            stopCamera.call(this); 
            this.closest("div.question")
                .querySelector('img')
                .classList.remove('hide');
        } 
    });

    function switchCamera() {
        stopCamera.call(this);

        const parent = this.closest('div.question');
        const spinner = parent.querySelector('i.fa-circle-notch');
        const video = parent.querySelector('video');
        const camera = parent.querySelector('div.camera');
        spinner.classList.add('rotate');

        // start the stream for the next device
        deviceIdx = (deviceIdx + 1) % videoDevs.length; 
        const deviceId = videoDevs[deviceIdx]['deviceId'];
        navigator.mediaDevices.getUserMedia(
            { video: { deviceId: { exact: deviceId } } }
        ).then(stream => {
            video.srcObject = stream;
            video.play();
            spinner.classList.remove('rotate');
            camera.classList.remove('hide');
        })
        .catch((e) => {
            alert('Fairled to switch camera: ' + e);
            spinner.classList.remove('rotate');
        });
    }
    document.querySelectorAll('div.switchCamera').forEach((div) => {
        div.onclick = switchCamera;
    });

    function takePicture() {
        const parent = this.closest('div.question');
        const video = parent.querySelector('video');
        const canvas = parent.querySelector('canvas');
        const img = parent.querySelector('img');
        const context = canvas.getContext("2d");
        canvas.width = 640;
        canvas.height = 480;
        context.drawImage(video, 0, 0, 640, 480);
        const picture = canvas.toDataURL("image/png");
        img.setAttribute("src", picture);
        video.classList.add('hide');
        stopCamera.call(this);
        img.classList.remove('hide');

        // upload the image
        // if there already is an image, get the answer_id from it
        let aid = false;
        if (img.dataset.id) {
            aid = img.dataset.id;
        }
        const qid = parent.dataset.id;
        const spinner = parent.querySelector('i.fa-circle-notch');
        const anchor = parent.querySelector('a');
        spinner.classList.add('rotate');
        const data = new FormData();
        data.append("answer_id", aid);
        data.append("image", picture);

        fetch(`${quiz_id}/question/${qid}/picture`, {
            method: "POST",
            body: data
        })
        .then((response) => response.json())
        .then((data) => {
            if (data.error) {
                alert(data.error);
            } else {
                img.src = data.dst;
                img.dataset.id = data.answer_id;
                img.classList.remove('hide');
                anchor.href = data.dst;
                const name = data.dst.split('/').pop();
                anchor.innerText = name;
            }
            spinner.classList.remove('rotate');    
        });
    }
    document.querySelectorAll('div.takePicture').forEach((div) => {
        div.onclick = takePicture;
    });

    // make back button also send 'finish' signal
    document.getElementById('back').onclick = function() {
        document.getElementById('finish').click();
    }
});            
