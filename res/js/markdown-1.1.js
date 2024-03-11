const MARKDOWN = (function() {
    let mdurl = "markdown";
    let btnCallback = null;

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

        fetch(mdurl + "?markdown=" + encodeURIComponent(shifted))
            .then((response) => response.text())
            .then((data) => {
                container.innerHTML = data;
                Prism.highlightAllUnder(container);
            });
    }

    function enablePreview(url) {
        mdurl = url;
        const buttons = document.querySelectorAll('button.previewBtn');
        for (const button of buttons) {
            button.onclick = getHtmlForMarkdown;
        }    
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

    return { getHtmlForMarkdown, enablePreview, ceasarShift, activateButtons, toggleMarkDown };
})()
