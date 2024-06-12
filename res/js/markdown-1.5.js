const MARKDOWN = (function() {
    let mdurl = "markdown";
    let btnCallback = null;
    let closePreview = false;

    // we ceasar shift because dreamhost has some very solid XSS injection 
    // protection... which stops a variety of markdown submits
    function ceasarShift(text, amount = 1) {
        let result = "";
        for (const char of text) {
            const code = char.charCodeAt(0) + amount;
            result += String.fromCharCode(code);
        }
        return result;
    }

    function getHtmlForMarkdown(evt) {
        evt.preventDefault();
        let parent = this.parentNode;
        while (parent.classList && !parent.classList.contains('textContainer')) {
            parent = parent.parentNode;
        }

        const markdown = parent.querySelector("textArea").value;
        const shifted = ceasarShift(markdown);
        const area = parent.querySelector('.previewArea');
        area.replaceChildren();
        const container = document.createElement('div');
        container.className = 'pcontainer';
        area.appendChild(container);

        const data = new FormData();
        data.append("markdown", shifted);

        fetch(mdurl, { method: "POST", body: data })
            .then((response) => response.text())
            .then((data) => {
                if (closePreview) {
                    let close = '<i title="Close" class="fas fa-times-circle"></i>'; 
                    data = close + data;
                }
                container.innerHTML = data;
                container.querySelector('i.fa-times-circle').onclick = function() {
                    container.replaceChildren();
                }
                container.querySelectorAll('pre').forEach(addCopyButton);
                Prism.highlightAllUnder(container);
            });
    }

    function enablePreview(url, closable = false) {
        mdurl = url;
        const buttons = document.querySelectorAll('.previewBtn');
        for (const button of buttons) {
            button.onclick = getHtmlForMarkdown;
        }    
        closePreview = closable;
    }

    function toggleMarkDown() {
        const active = this.classList.contains('active');
        const parent = this.parentNode;
        const mdc = parent.querySelector("div.mdContainer");
        const txt = parent.querySelector("textarea");
        if (active) {
            this.classList.remove('active');
            mdc.classList.remove('active');
            txt.setAttribute("placeholder", txt.dataset.txt);
            this.setAttribute('title', "Enable Markdown");
        } else {
            this.classList.add('active');
            mdc.classList.add('active');
            txt.setAttribute("placeholder", txt.dataset.md);
            this.setAttribute('title', "Disable Markdown");
        }
        if (btnCallback) {
            btnCallback.call(this);
        }
    }

    function activateButtons(callback) {
        btnCallback = callback;
        const btns = document.querySelectorAll("i.fa-markdown");
        for (const btn of btns) {
            btn.onclick = toggleMarkDown;
        }
    }

    // private function
    function getCodeText(elem) {
        // If the user pasted tab characters they are copied
        // as form feed (ASCII code 12) and 3 spaces. 
        let txt = elem.innerText.split('');
        for (let i = 0; i < txt.length; i++) {
            if (txt[i].charCodeAt(0) == 12) {
                txt[i] = ' ';
            }
        }
        return txt.join('');
    }

    // private function
    function showCopied(elem) {
        return function() {
            elem.classList.remove('fa-regular', 'fa-copy');
            elem.classList.add('fa-solid', 'fa-check');
            setTimeout(function() {
                elem.classList.remove('fa-solid', 'fa-check');
                elem.classList.add('fa-regular', 'fa-copy');
            }, 1000);
        }
    }

    function addCopyButton(elem) {
        const button = document.createElement('i');
        button.setAttribute('title', 'Copy');
        button.classList.add('fa-regular', 'fa-copy', 'copy-button');
        button.addEventListener('mousedown', function() {
            navigator.clipboard.writeText(getCodeText(elem))
                .then(showCopied(button));
        });
        elem.before(button);
    }

    return { 
        getHtmlForMarkdown, 
        enablePreview, 
        ceasarShift, 
        activateButtons, 
        toggleMarkDown,
        addCopyButton,
    };
})()

window.addEventListener("load", () => {
    // make tab insert 4 spaces when in a textarea with markdown enabled
    function keyEventHandler(evt) {
        // make CTRL-M toggle markdown
        if (evt.ctrlKey && evt.key == "m") {
            evt.preventDefault();
            const parent = this.parentNode;
            const btn = parent.querySelector("i.fa-markdown");
            MARKDOWN.toggleMarkDown.call(btn);
            return;
        }

        // make Escape move you to the next input field
        if (evt.key == "Escape") {
            evt.preventDefault();
            const content = document.getElementById('content');
            const inputs = content.querySelectorAll('input, textarea, button');
            for (let i = 0; i < inputs.length; i++) {
                if (inputs[i] === this) {
                    const next = inputs[(i + 1) % inputs.length];
                    next.focus();
                    next.select();
                    break;
                }
            }
            return;
        }
        if (evt.key !== "Tab") {
            return;
        }
        const mdc = this.parentNode.querySelector("div.mdContainer");
        if (!mdc || !mdc.classList.contains('active')) {
            return;
        }
        evt.preventDefault();
        const selStart = this.selectionStart;
        const selStop = this.selectionEnd;

        // normal tab press adds 4 spaces
        if (selStart == selStop && !evt.shiftKey) {
            const txtBegin = this.value.substring(0, this.selectionStart);
            const txtStop = this.value.substring(this.selectionEnd);
            this.value = txtBegin + "    " + txtStop;
            this.selectionStart = selStart + 4;
            this.selectionEnd = selStart + 4;
            return;
        }

        // shift-tab removes 4 spaces (or tab) from the beginning of the line
        if (selStart == selStop && evt.shiftKey) {
            let lineStart = selStart;
            while (this.value[lineStart] != "\n" && lineStart > 0) {
                lineStart--;
            }
            if (this.value[lineStart] == "\n") {
                lineStart++;
            }
            let lineEnd = selStop;
            while (this.value[lineEnd] != "\n" && lineEnd < this.value.length) {
                lineEnd++;
            }
            const txtBegin = this.value.substring(0, lineStart);
            const txtStop = this.value.substring(lineEnd);
            let line = this.value.substring(lineStart, lineEnd);
            if (line.startsWith("    ")) {
                line = line.substring(4);
            } else if (line.startsWith("\t")) {
                line = line.substring(1);
            }
            this.value = txtBegin + line + txtStop;
            return;
        }

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
            for (let i = 0; i < lines.length; i++) {
                if (lines[i].startsWith("    ")) {
                    lines[i] = lines[i].substring(4);
                } else if (lines[i].startsWith("\t")) {
                    lines[i] = lines[i].substring(1);
                }
            }
            const newText = lines.join("\n");
            this.value = txtBegin + newText + txtStop;
            this.selectionStart = selStart;
            this.selectionEnd = selStop - (newLines.length - 1) * 4;
        }
    }
    document.querySelectorAll('textarea').forEach((area) => {
        area.onkeydown = keyEventHandler;
    });

    // hook up copy buttons inside already rendered markdown
    document.querySelectorAll('pre').forEach(function(pre) {
        for (const className of pre.classList) {
            if (className.startsWith('language-')) {
                // Remove tabindex added by prism.js to prevent focus
                pre.removeAttribute('tabindex'); 
                MARKDOWN.addCopyButton(pre);
            }
        }
    });
});            
