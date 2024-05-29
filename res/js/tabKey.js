window.addEventListener("load", () => {    
    // make tab insert 4 spaces when in a textarea with markdown enabled
    function doTab(evt) {
        if (evt.key !== "Tab") {
            return;
        }
        const mdc = this.parentNode.querySelector("div.mdContainer");
        if (!mdc.classList.contains('active')) {
            return;
        }
        evt.preventDefault();
        const selStart = this.selectionStart;
        const selStop = this.selectionEnd;
        if (selStart == selStop) {
            const txtBegin = this.value.substring(0, this.selectionStart);
            const txtStop = this.value.substring(this.selectionEnd);
            this.value = txtBegin + "    " + txtStop;
            this.selectionStart = selStart + 4;
            this.selectionEnd = selStart + 4;
        } else {
            // change indent level from selected text
            if (!evt.shiftKey) { // add indent level
                const txtBegin = this.value.substring(0, this.selectionStart);
                const txtStop = this.value.substring(this.selectionEnd - 1);
                const lines = this.value.substring(selStart, selStop - 1).split("\n");
                const newLines = lines.map((line) => "    " + line);
                const newText = newLines.join("\n");
                this.value = txtBegin + newText + txtStop;
                this.selectionStart = selStart;
                this.selectionEnd = selStop + newLines.length * 4;
            } else {  // remove indent level
                const txtBegin = this.value.substring(0, this.selectionStart);
                const txtStop = this.value.substring(this.selectionEnd);
                const lines = this.value.substring(selStart, selStop).split("\n");
                const newLines = lines.map((line) => line.substring(4));
                const newText = newLines.join("\n");
                this.value = txtBegin + newText + txtStop;
                this.selectionStart = selStart;
                this.selectionEnd = selStop - (newLines.length -1) * 4;
            }
        }
    }
    document.querySelectorAll('textarea').forEach((area) => {
        area.onkeydown = doTab;
    });
});            
