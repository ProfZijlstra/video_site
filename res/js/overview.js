
window.addEventListener("load", () => {
	'use strict';
    document.getElementById("days").onclick = function(e) {
        if (e.target.tagName == "TD") {
            e.target.querySelector("a").click();
        }
    }

	const info = document.getElementById("info-btn");
	if (info) {
		info.onclick = function() {
			const e = React.createElement;

			fetch('info')
			.then(function(response) {
				return response.json();
			})
			.then(function(json) {
				for (const day in json) {
					const elm = document.getElementById(day)
						.getElementsByClassName("info")[0];
					const props = json[day];

					// uses components from info.js 
					ReactDOM.render(e(INFO.Info, props), elm);
				}
			});
		};
	}

});
