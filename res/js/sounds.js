"use strict";

const SOUNDS = (function(){
    var audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    var compressor = audioCtx.createDynamicsCompressor();
    compressor.threshold.setValueAtTime(-50, audioCtx.currentTime);
    compressor.knee.setValueAtTime(40, audioCtx.currentTime);
    compressor.ratio.setValueAtTime(12, audioCtx.currentTime);
    compressor.attack.setValueAtTime(0, audioCtx.currentTime);
    compressor.release.setValueAtTime(0.25, audioCtx.currentTime);
    compressor.connect(audioCtx.destination);

    /**
     * @returns {undefined}
     */
    function present() {
        var gainNode1 = audioCtx.createGain();
        gainNode1.connect(compressor);
        var oscil1 = audioCtx.createOscillator();
        oscil1.type = "sine";
        oscil1.frequency.value = 432.0;
        oscil1.connect(gainNode1);
        oscil1.start();

        var gainNode2 = audioCtx.createGain();
        gainNode2.connect(compressor);
        var oscil2 = audioCtx.createOscillator();
        oscil2.type = "sine";
        oscil2.frequency.value = 648.0;
        oscil2.connect(gainNode2);
        oscil2.start();

        gainNode2.gain.setValueAtTime(0, audioCtx.currentTime);
        gainNode1.gain.setTargetAtTime(0, audioCtx.currentTime + 0.15, 0.15);
        gainNode2.gain.setValueAtTime(1, audioCtx.currentTime + 0.15);
        gainNode2.gain.setTargetAtTime(0, audioCtx.currentTime + 0.3, 0.075);
    }

    /**
     * @returns {undefined}
     */
    function notFound() {
        var gainNode1 = audioCtx.createGain();
        gainNode1.connect(compressor);
        var oscil1 = audioCtx.createOscillator();
        oscil1.type = "sine";
        oscil1.frequency.value = 256.0;
        oscil1.connect(gainNode1);
        oscil1.start();

        gainNode1.gain.setTargetAtTime(0, audioCtx.currentTime + 0.5, 0.1);
    }

    return {
        present, 
        notFound
    };
})();