window.addEventListener("load", () => {
    const nav = document.getElementById("videos");
    const videos = document.getElementsByTagName("video");
	let view_id = false;
	const day_id = document.getElementById("day").dataset.id;
    function tabclick(e) {
        const tab = this;
		for (const video of videos) {
			video.pause();
		}

		tab.parentNode
			.getElementsByClassName("selected")[0]
			.classList.remove("selected");
        tab.classList.add("selected");

        document.querySelectorAll("main > article.selected")[0]
            .classList.remove("selected");
        document.getElementById(tab.dataset.show).classList.add("selected");
    }
	const tabs = nav.getElementsByClassName('video_link');
	for (const tab of tabs) {
		tab.onclick = tabclick;
	}
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

	const info = document.getElementById("info-btn");
	if (info) {
		info.onclick = function() {

			fetch(`info?day_id=${day_id}`)
			.then(function(response) {
				return response.json();
			})
			.then(function(json) {
				const e = React.createElement;
				const tabs = document.getElementById("videos")
					.getElementsByClassName('video_link');
				for (const tab of tabs) {
					const props = json[tab.dataset.show]
					const container = tab.getElementsByClassName('info')[0];
					ReactDOM.render(e(INFO.Info, props), container);
				}
				ReactDOM.render(e("div", null, 
					"Total: ", e(INFO.Info, json['total'])), 
					document.getElementById('total'));
			});
		};
	}

});
