/* Swasti Homoeo Clinic — admin interface.
   Sidebar, row action menus, destructive-action confirmation and small form
   niceties. No inline handlers, no dependencies. */

(function () {
    'use strict';

    /* -------------------------------------------------------------- sidebar */

    var burger = document.querySelector('[data-sidebar-toggle]');
    var sidebar = document.querySelector('[data-sidebar]');

    if (burger && sidebar) {
        var isNarrow = function () { return window.innerWidth <= 1024; };

        var setOpen = function (open) {
            if (!isNarrow()) {
                open = true;
                document.body.classList.remove('sidebar-open');
            }

            document.body.classList.toggle('sidebar-open', open && isNarrow());
            burger.setAttribute('aria-expanded', open ? 'true' : 'false');

            // Only hide the off-canvas drawer from assistive tech when it is
            // actually collapsed; on wide screens it is permanently visible.
            if (isNarrow()) {
                sidebar.setAttribute('aria-hidden', open ? 'false' : 'true');
                burger.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
            } else {
                sidebar.removeAttribute('aria-hidden');
                burger.setAttribute('aria-label', 'Toggle menu');
            }
        };

        burger.addEventListener('click', function () {
            setOpen(!document.body.classList.contains('sidebar-open'));
        });

        document.addEventListener('click', function (event) {
            if (!document.body.classList.contains('sidebar-open')) {
                return;
            }

            var inSidebar = event.target.closest('.admin-sidebar');
            var onBurger = event.target.closest('[data-sidebar-toggle]');

            if (!inSidebar && !onBurger) {
                setOpen(false);
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && document.body.classList.contains('sidebar-open')) {
                setOpen(false);
                burger.focus();
            }
        });

        window.addEventListener('resize', function () {
            setOpen(isNarrow() && document.body.classList.contains('sidebar-open'));
        });

        setOpen(!isNarrow());
    }

    /* ------------------------------------------------- row action menus */

    var menus = Array.prototype.slice.call(document.querySelectorAll('.row-menu'));

    var closeAll = function (except) {
        menus.forEach(function (menu) {
            if (menu !== except) {
                menu.open = false;
            }
        });
    };

    menus.forEach(function (menu) {
        var summary = menu.querySelector('summary');

        if (!summary) {
            return;
        }

        summary.addEventListener('click', function (event) {
            event.preventDefault();
            var willOpen = !menu.open;
            closeAll(menu);
            menu.open = willOpen;
        });

        menu.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && menu.open) {
                menu.open = false;
                summary.focus();
            }
        });
    });

    document.addEventListener('click', function (event) {
        if (!event.target.closest('.row-menu')) {
            closeAll(null);
        }
    });

    /* -------------------------------------------- destructive confirmation */

    Array.prototype.forEach.call(document.querySelectorAll('[data-confirm]'), function (form) {
        form.addEventListener('submit', function (event) {
            var message = form.getAttribute('data-confirm') || 'Are you sure?';
            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });

    /* ----------------------------------------------------------- FAQ forms */

    var faqForm = document.querySelector('.form[action*="/admin/faqs"]');

    if (faqForm) {
        var question = faqForm.querySelector('#question');
        var answer = faqForm.querySelector('#answer');

        var bound = function (field, max) {
            if (!field || !max) {
                return;
            }

            var hint = document.createElement('span');
            hint.className = 'field__count';
            field.parentNode.appendChild(hint);

            var update = function () {
                var left = max - field.value.length;
                hint.textContent = left > 0 ? left + ' characters left' : 'At the limit';
                hint.classList.toggle('is-over', left < 0);
            };

            field.addEventListener('input', update);
            update();
        };

        bound(question, parseInt(question ? question.getAttribute('maxlength') || '0' : '0', 10));
        bound(answer, parseInt(answer ? answer.getAttribute('maxlength') || '0' : '0', 10));

        faqForm.addEventListener('submit', function (event) {
            if (!faqForm.checkValidity()) {
                event.preventDefault();
                faqForm.reportValidity();
                return;
            }

            var button = faqForm.querySelector('button[type="submit"]');
            if (button) {
                button.classList.add('is-busy');
                button.setAttribute('aria-busy', 'true');
            }
        });
    }

    /* ------------------------------------- flash away success notifications */

    Array.prototype.forEach.call(document.querySelectorAll('.flash--success'), function (flash) {
        var close = flash.querySelector('[data-flash-close]');
        var dismiss = function () {
            flash.classList.add('is-hiding');
            window.setTimeout(function () { flash.remove(); }, 320);
        };

        if (close) {
            close.addEventListener('click', dismiss);
        } else {
            window.setTimeout(dismiss, 8000);
        }
    });

    /* --------------------------------------------- bring a saved row into view */

    var highlight = document.querySelector('[data-highlight]');

    if (highlight && highlight.scrollIntoView) {
        window.setTimeout(function () {
            highlight.scrollIntoView({ block: 'center' });
        }, 120);
    }

    /* ---------------------------------------- keep filters usable on mobile */

    var dateInputs = Array.prototype.slice.call(document.querySelectorAll('.filter-bar input[type="date"]'));

    dateInputs.forEach(function (input) {
        input.addEventListener('change', function () {
            var form = input.form;
            if (form) {
                form.submit();
            }
        });
    });
}());