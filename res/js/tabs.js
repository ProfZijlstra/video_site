setTimeout(function() {
    const nav = document.getElementById("videos");
    const videos = document.getElementsByTagName("video");
	let view_id = false;
	const day_id = document.getElementById("day").dataset.id;
    nav.onclick = function(e) {
        const tab = e.target.parentNode;
        if (!tab.classList.contains("video_link")) {
            return;
        }

		for (const video of videos) {
			video.pause();
		}

        const divs = nav.childNodes;
        divs.forEach(function(d) {
            if (d.nodeType === 1) {
                d.classList.remove("selected")
            }
        });
        tab.classList.add("selected");

        const articles = document.querySelectorAll("main > article");
        articles.forEach(function(a){
            a.classList.remove("selected");
        });
        document.getElementById(tab.dataset.show).classList.add("selected");
    };
    document.getElementById("days").onclick = function(e) {
        if (e.target.tagName == "TD") {
            e.target.querySelector("a").click();
        }
    }
    const curSpeed = document.getElementById("curSpeed");
	const numOpts = {minimumFractionDigits: 1, minimumFractionDigits: 1};
    document.getElementById("faster").onclick = function(e) {
        let speed = parseFloat(curSpeed.innerHTML)
        speed += 0.1
        curSpeed.innerHTML = speed.toLocaleString('en-US', numOpts);
        for (const video of videos) {
            video.playbackRate = speed;
        }
    }
    document.getElementById("slower").onclick = function(e) {
        let speed = parseFloat(curSpeed.innerHTML)
        speed -= 0.1
        curSpeed.innerHTML = speed.toLocaleString('en-US', numOpts);
        for (const video of videos) {
            video.playbackRate = speed;
        }
    }
	function playHandler(evt) {
		let video = encodeURIComponent(evt.target.parentNode.id);
		// get view_id by posting to start: day_id, video
		let url = `./start?day_id=${day_id}&video=${video}`;
		fetch(url, { cache: 'no-cache' })
			.then(response => response.text())
			.then(text => view_id = text );
	}
	function pauseHandler(evt) {
		if (view_id) {
			// post view_id to url: stop
			fetch("./stop", {
				method: "POST",
				body: `view_id=${view_id}`,
				headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			});
		}
	}
	for (const video of videos) {
		video.addEventListener('play', playHandler);
		video.addEventListener('pause', pauseHandler);
	}

}, 1000); // a timeout because document ready didn't do it with the videos
// this is really not the best as timeouts are super unpredictable and we
// could end up with much bigger network delays that throw everything off
