const COUNTDOWN = (function() {
    let prevStamp = Math.floor((new Date()).getTime() / 1000);

    let hours = null;
    let minutes = null;
    let seconds = null;
    let intid = null;
    let callback = null;

    function updateTime() {
        const stamp = Math.floor((new Date()).getTime() / 1000);
        // mostly -1, but may be -2 due to drift
        // or even much bigger due to suspending the machine!
        const diff = prevStamp - stamp; 
        prevStamp = stamp;
        seconds += diff;

        while (seconds < 0) {
            minutes -= 1;
            seconds += 60;
            if (minutes < 0) {
                minutes += 60;
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
        
        if (seconds <= 0 && minutes <= 0 && hours <= 0) {
            clearInterval(intid);
            const inputs = document.querySelectorAll("input");
            for (const input of inputs) {
                input.blur();
            }
            setTimeout(callback, 2000);
        }
    }

    function start(cb) {
        callback = cb;
        hours = parseInt(document.getElementById("hours").innerText);
        minutes = parseInt(document.getElementById("minutes").innerText);
        seconds = parseInt(document.getElementById("seconds").innerText);
    
        intid = setInterval(updateTime, 1000);
    }
    return { start };
})();