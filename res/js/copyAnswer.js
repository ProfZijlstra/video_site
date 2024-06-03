window.addEventListener('load', function() {
    const answers = []; 
    document.querySelectorAll('pre').forEach(function(pre) {
        for (const className of pre.classList) {
            if (className.startsWith('language-')) {
                // Remove tabindex added by prism.js to prevent focus
                pre.removeAttribute('tabindex'); 
                answers.push(pre);
            }
        }
    });
    
    answers.forEach(function(answer) {
        const button = document.createElement('i');
        button.setAttribute('title', 'Copy');
        button.classList.add('fa-regular', 'fa-copy', 'copy-button');
        button.addEventListener('mousedown', function() {
            navigator.clipboard.writeText(answer.textContent).then(function() {
                button.classList.remove('fa-regular', 'fa-copy');
                button.classList.add('fa-solid', 'fa-check');
                setTimeout(function() {
                    button.classList.remove('fa-solid', 'fa-check');
                    button.classList.add('fa-regular', 'fa-copy');
                }, 1000);
            });
        });
        answer.parentElement.insertBefore(button, answer);
    });
});