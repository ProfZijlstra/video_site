window.addEventListener("load", () => {
	const hash = window.location.hash;
	if (hash && hash != "questionForm" && hash[1] == "q") {
		const elm = document.getElementById(hash.substring(1));
		elm.classList.add("selected");
		setTimeout(() => {
			elm.classList.add("selectDone")
		}, 2500)
	}
    const nav = document.getElementById("videos");
    const videos = document.getElementsByTagName("video");
	let view_id = false;
	const day_id = document.getElementById("day").dataset.id;
    function tabclick() {
		const links = this.getElementsByTagName("a");
		window.location = links[0].href;
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
	function pdfHandler(evt) {
		const file = this.dataset.file;
		const href = this.href;
		let url=`./pdf?day_id=${day_id}&file=${file}`;
		fetch(url, { cache: 'no-cache' })
			.then(() => { window.location = href});
		evt.preventDefault();
	}
	const pdfs = document.getElementsByClassName("pdf")
	for (const pdf of pdfs) {
		pdf.addEventListener('click', pdfHandler);
	}
	function delHandler() {
		this.parentNode.submit();
	}
	const dels = document.getElementsByClassName("fa-trash-alt");
	for (const del of dels) {
		del.addEventListener('click', delHandler);
	}
	function editHandler() {
		const id = this.dataset.id;
		fetch(`getQuestion?qid=${id}`)
		.then(response => response.json() )
		.then(json => {
			const form = document.createElement("form");
			form.setAttribute("method", "post");
			form.setAttribute("action", "updQuestion");
			form.style.position = "relative";
			const qid = document.createElement("input");
			qid.setAttribute("type", "hidden");
			qid.setAttribute("name", "id");
			qid.setAttribute("value", id);
			form.append(qid);
			const tab = document.createElement("input");
			tab.setAttribute("type", "hidden");
			tab.setAttribute("name", "tab");
			tab.setAttribute("value", document.getElementById("tab").value);
			form.append(tab);
			const text = document.createElement("textarea");
			text.setAttribute("name", "text");
			text.classList.add("questionText");
			text.append(json.question);
			form.append(text);
			const submit = document.createElement("input");
			submit.setAttribute("type", "submit");
			submit.setAttribute("value", "Update");
			submit.classList.add("textAction");
			form.append(submit);
			this.parentNode.after(form);
			form.nextSibling.nextSibling.style.display = "none";
		});
	}
	const edits = document.getElementsByClassName("fa-edit");
	for (const edit of edits) {
		edit.addEventListener('click', editHandler);
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
