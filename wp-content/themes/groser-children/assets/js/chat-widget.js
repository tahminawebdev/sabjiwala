(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var toggle = document.getElementById('sjw-chat-toggle');
        var panel  = document.getElementById('sjw-chat-panel');
        var close  = document.getElementById('sjw-chat-close');
        var hint   = document.querySelector('#sjw-chat-widget .sjw-chat__hint');

        if (!toggle || !panel) return;

        function openPanel() {
            panel.classList.add('is-open');
            toggle.setAttribute('aria-expanded', 'true');
            if (hint) hint.style.display = 'none';
        }

        function closePanel() {
            panel.classList.remove('is-open');
            toggle.setAttribute('aria-expanded', 'false');
            if (hint) hint.style.display = '';
        }

        toggle.addEventListener('click', function () {
            panel.classList.contains('is-open') ? closePanel() : openPanel();
        });

        if (close) {
            close.addEventListener('click', closePanel);
        }

        // Clicking a suggestion populates the input
        var suggestions = panel.querySelectorAll('.sjw-chat__suggestion');
        var input = panel.querySelector('.sjw-chat__input-row input');
        suggestions.forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (input) {
                    input.value = btn.textContent.trim();
                    input.focus();
                }
            });
        });
    });
}());
