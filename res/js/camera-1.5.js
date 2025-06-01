const CAMERA = (function() {

    let videoDevs = undefined;
    let deviceIdx = 0;
    let urlBase = "";
    let pictureBtn = null;
    let multiPageable = true;

    function init(url, multi = true) {
        urlBase = url;
        multiPageable = multi;

        document.querySelectorAll("span i.fa-camera").forEach((camera) => {
            camera.onclick = openCamera;
        });

        document.querySelectorAll('div.closeCamera').forEach((div) => {
            div.onclick = closeCamera;
        });

        document.querySelectorAll('div.switchCamera').forEach((div) => {
            div.onclick = switchCamera;
        });

        document.querySelectorAll('div.takePicture').forEach((div) => {
            div.onclick = takePicture;
        });
    }

    function closeCamera() {
        stopCamera.call(this);
        this.closest("div.answer, div.delivery")
            .querySelector('img.answer')
            .classList.remove('hide');
    }

    function openCamera() {
        // try and find if there are multiple cameras
        let intervalCount = 0;
        let findDevInterval = setInterval(() => {
            intervalCount++;
            navigator.mediaDevices.enumerateDevices()
                .then(devices => {
                    videoDevs = devices.filter(d => d.kind == "videoinput");
                    console.log(videoDevs.length);
                    if (intervalCount >= 20 || videoDevs.length > 1) {
                        clearInterval(findDevInterval);
                        switchCam.classList.remove('hide');
                    }
                });
        }, 500);

        // do the actual opening
        const parent = this.closest('div.camContainer');
        const body = this.closest('body');
        body.addEventListener('keypress', takePictureOnKeyPress);
        const video = parent.querySelector('video');
        const switchCam = parent.querySelector('.switchCamera');
        const close = parent.querySelector(".closeCamera");
        const camera = parent.querySelector('div.camera');
        const spinner = parent.querySelector('i.fa-circle-notch');
        const img = parent.querySelector('img.answer');
        const pic = parent.querySelector('div.takePicture');
        pictureBtn = pic;
        spinner.classList.add('rotate');
        navigator.mediaDevices.getUserMedia({ video: true, audio: false })
            .then((stream) => {
                video.srcObject = stream;
                video.classList.add('show');
                video.play();
                img.classList.add('hide');
                img.classList.remove('show');
                camera.classList.remove('hide');
                close.classList.remove('hide');
                pic.classList.remove('hide');
                spinner.classList.remove('rotate');
                if (videoDevs && videoDevs.length > 1) {
                    switchCam.classList.remove('hide');
                }
            })
            .catch((e) => {
                console.log(e);
                alert("Unable to open camera");
                spinner.classList.remove('rotate');
            });
    }

    function stopCamera() {
        const body = this.closest('body');
        body.removeEventListener('keypress', takePictureOnKeyPress);
        const parent = this.parentNode.closest('div.camera');
        const video = parent.querySelector('video');
        const tracks = video.srcObject.getTracks();
        tracks.forEach(track => track.stop());
        parent.classList.add('hide');
        const img = parent.nextElementSibling;
        if (img.getAttribute("src")) {
            img.classList.remove('hide');
            img.classList.add('show');
        }
    }

    function switchCamera() {
        stopCamera.call(this);

        const body = this.closest('body');
        body.addEventListener('keypress', takePictureOnKeyPress);

        const parent = this.closest('div.answer, div.delivery');
        const spinner = parent.querySelector('i.fa-circle-notch');
        const video = parent.querySelector('video');
        const camera = parent.querySelector('div.camera');
        spinner.classList.add('rotate');

        const img = camera.nextElementSibling;
        if (img.getAttribute("src")) {
            img.classList.add('hide');
            img.classList.remove('show');
        }
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
                alert('Failed to switch camera: ' + e);
                spinner.classList.remove('rotate');
            });
    }

    function takePicture() {
        const parent = this.closest('div.camContainer');
        const video = parent.querySelector('video');
        const canvas = parent.querySelector('canvas');
        const img = parent.querySelector('img.answer');
        const context = canvas.getContext("2d");
        canvas.width = 640;
        canvas.height = 480;
        context.drawImage(video, 0, 0, 640, 480);
        const picture = canvas.toDataURL("image/png");
        img.setAttribute("src", picture);
        video.classList.add('hide');
        stopCamera.call(this);
        img.classList.remove('hide');
        img.classList.add('show');

        // upload the image
        // if there already is an image, get the answer_id from it
        let aid = false;
        if (img.dataset.id) {
            aid = img.dataset.id;
        }
        const qid = parent.dataset.id;
        const spinner = parent.querySelector('i.fa-circle-notch');
        const anchor = parent.querySelector('a');
        const trash = parent.querySelector('i.fa-trash-can');
        spinner.classList.add('rotate');
        const data = new FormData();
        data.append("answer_id", aid);
        data.append("image", picture);

        let url = `${urlBase}/${qid}/picture`;
        const user_id = document.getElementById('user_id')?.value;
        if (user_id) {
            url += `?user_id=${user_id}`;
        }
        if (multiPageable && window.localStorage.view == "multi") {
            url = "../" + url;
        }
        fetch(url, {
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
                    const deliv = this.closest("div.deliv");
                    if (deliv) {
                        deliv.dataset.id = data.answer_id;
                    }
                    img.classList.remove('hide');
                    img.classList.add('show');
                    if (anchor) {
                        anchor.href = data.dst;
                        const name = data.dst.split('/').pop();
                        anchor.innerText = name;
                    }
                    if (trash) {
                        trash.classList.remove('hide');
                        trash.dataset.id = data.answer_id;
                    }
                }
                spinner.classList.remove('rotate');
            })
            .catch((error) => {
                alert("Image upload error: " + error);
            });
    }

    function takePictureOnKeyPress(event) {
        if (event.code === "Space") {
            event.preventDefault();
            pictureBtn.click();
        }
    }

    return {
        init: init,
        openCamera: openCamera,
        closeCamera: closeCamera,
        stopCamera: stopCamera,
        switchCamera: switchCamera,
        takePicture: takePicture
    };
}());
