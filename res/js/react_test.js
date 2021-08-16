
window.addEventListener("load", () => {
	'use strict';
    document.getElementById("days").onclick = function(e) {
        if (e.target.tagName == "TD") {
            e.target.querySelector("a").click();
        }
    }

	document.getElementById("info-btn").onclick = function() {
		const e = React.createElement;

		fetch('info')
		.then(function(response) {
			return response.json();
		})
		.then(function(json) {
			for (const day in json) {
				const elm = document.getElementById(day);
				const user = elm.getElementsByClassName('users')[0];
				const view = elm.getElementsByClassName('views')[0];
				const time = elm.getElementsByClassName('hours')[0];
				
				const props = json[day];
				// uses components from info.js 
				ReactDOM.render(e(INFO.Users, props), user);
				ReactDOM.render(e(INFO.Views, props), view);
				ReactDOM.render(e(INFO.Time, props), time);
			}
		});
	};

});
