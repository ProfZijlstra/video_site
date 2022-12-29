const COUNTDOWN = (function() {
    function start(callback) {
        // timer code
        let hours = parseInt(document.getElementById("hours").innerText);
        let minutes = parseInt(document.getElementById("minutes").innerText);
        let seconds = parseInt(document.getElementById("seconds").innerText);
        const intid = setInterval(updateTime, 1000);
        function updateTime() {
            seconds -= 1;
            if (seconds < 0) {
                minutes -= 1;
                seconds = 59;
                if (minutes < 0) {
                    minutes = 59;
                    hours -= 1;
                    let hoursText = hours;
                    if (hours < 10) {
                        hoursText = "0" + hours;
                    }
                    document.getElementById("hours").innerText = hoursText;
                }    
                let minutesText = minutes;
                if (minutes < 10) {
                    minutesText = "0" + minutes;
                }
                document.getElementById("minutes").innerText = minutesText;
            }
            let secondsText = seconds;
            if (seconds < 10) {
                secondsText = "0" + seconds;
            }
            document.getElementById("seconds").innerText = secondsText;
            
            if (seconds == 0 && minutes == 0 && hours == 0) {
                clearInterval(intid);
                const inputs = document.querySelectorAll("input");
                for (const input of inputs) {
                    input.blur();
                }
                setTimeout(callback, 2000);
            }
        }
    }
    return { start };
})();