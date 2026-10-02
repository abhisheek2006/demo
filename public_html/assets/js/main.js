/* Swasti Homoeo Clinic — public behaviour.
   Scroll reveals, hero slider, testimonial carousel, FAQ filtering and form
   enhancements. Everything degrades gracefully without JavaScript. */

(function () {
    'use strict';

    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* --------------------------------------------------------- scroll reveal */

    var revealables = Array.prototype.slice.call(document.querySelectorAll('[data-reveal]'));

    if (revealables.length) {
        if (!('IntersectionObserver' in window) || reduceMotion) {
            revealables.forEach(function (el) { el.classList.add('is-visible'); });
        } else {
            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });

            revealables.forEach(function (el, index) {
                el.style.setProperty('transition-delay', Math.min(index % 4, 3) * 70 + 'ms');
                observer.observe(el);
            });
        }
    }

    /* ------------------------------------------------------------ hero slider */

    Array.prototype.forEach.call(document.querySelectorAll('[data-slider]'), function (slider) {
        var slides = Array.prototype.slice.call(slider.querySelectorAll('[data-slide]'));
        var dots = Array.prototype.slice.call(slider.querySelectorAll('[data-slide-dot]'));
        var kicker = slider.querySelector('[data-caption-kicker]');
        var title = slider.querySelector('[data-caption-title]');
        var text = slider.querySelector('[data-caption-text]');
        var autoplay = parseInt(slider.getAttribute('data-autoplay') || '0', 10);
        var timer = null;
        var index = 0;

        if (slides.length < 2) {
            return;
        }

        var show = function (next) {
            index = (next + slides.length) % slides.length;

            slides.forEach(function (slide, i) {
                var active = i === index;
                slide.classList.toggle('is-active', active);
                slide.setAttribute('aria-hidden', active ? 'false' : 'true');
            });

            dots.forEach(function (dot, i) {
                var active = i === index;
                dot.classList.toggle('is-active', active);
                dot.setAttribute('aria-selected', active ? 'true' : 'false');
            });

            if (title && slides[index].dataset.title) {
                title.textContent = slides[index].dataset.title;
            }
            if (kicker && slides[index].dataset.kicker) {
                kicker.textContent = slides[index].dataset.kicker;
            }
            if (text && slides[index].dataset.text) {
                text.textContent = slides[index].dataset.text;
            }
        };

        var stop = function () {
            if (timer) {
                window.clearInterval(timer);
                timer = null;
            }
        };

        var start = function () {
            if (autoplay > 0 && !reduceMotion) {
                stop();
                timer = window.setInterval(function () { show(index + 1); }, autoplay);
            }
        };

        dots.forEach(function (dot) {
            dot.addEventListener('click', function () {
                show(parseInt(dot.getAttribute('data-slide-dot') || '0', 10));
                start();
            });
        });

        slider.addEventListener('mouseenter', stop);
        slider.addEventListener('mouseleave', start);
        slider.addEventListener('focusin', stop);
        slider.addEventListener('focusout', start);

        document.addEventListener('visibilitychange', function () {
            if (document.hidden) {
                stop();
            } else {
                start();
            }
        });

        show(0);
        start();
    });

    /* ------------------------------------------------------------- carousels */

    Array.prototype.forEach.call(document.querySelectorAll('[data-carousel]'), function (carousel) {
        var track = carousel.querySelector('[data-carousel-track]');
        var slides = Array.prototype.slice.call(carousel.querySelectorAll('[data-carousel-slide]'));
        var prev = carousel.querySelector('[data-carousel-prev]');
        var next = carousel.querySelector('[data-carousel-next]');
        var dotsWrap = carousel.querySelector('[data-carousel-dots]');

        if (!track || slides.length === 0) {
            return;
        }

        var page = function () {
            var first = slides[0];
            if (!first) {
                return slides.length || 1;
            }
            var styles = window.getComputedStyle(track);
            var gap = parseFloat(styles.columnGap || styles.gap || '0') || 0;
            return Math.max(1, Math.floor((track.clientWidth + gap) / (first.offsetWidth + gap)));
        };

        var pageCount = function () {
            return Math.max(1, Math.ceil(slides.length / page()));
        };

        var dots = [];

        var buildDots = function () {
            if (!dotsWrap) {
                return;
            }

            dotsWrap.textContent = '';
            dots = [];

            for (var i = 0; i < pageCount(); i++) {
                var dot = document.createElement('button');
                dot.type = 'button';
                dot.className = 'hero__dot';
                dot.setAttribute('data-carousel-dot', String(i));
                dot.setAttribute('aria-label', 'Go to testimonial ' + (i + 1));
                (function (target) {
                    dot.addEventListener('click', function () { scrollToPage(target); });
                }(i));
                dotsWrap.appendChild(dot);
                dots.push(dot);
            }
        };

        var currentPage = function () {
            var per = page();
            return Math.min(pageCount() - 1, Math.floor(track.scrollLeft / (slides[0].offsetWidth * per) || 0));
        };

        var paint = function () {
            var active = currentPage();
            dots.forEach(function (dot, i) {
                dot.classList.toggle('is-active', i === active);
                dot.setAttribute('aria-selected', i === active ? 'true' : 'false');
            });
            if (prev) {
                prev.disabled = track.scrollLeft < 4;
            }
            if (next) {
                next.disabled = track.scrollLeft + track.clientWidth >= track.scrollWidth - 4;
            }
        };

        var scrollToPage = function (index) {
            var per = page();
            var step = (slides[0].offsetWidth + 24) * per;
            var target = Math.max(0, index) * step;

            if ('scrollBehavior' in document.documentElement.style && !reduceMotion) {
                track.scrollTo({ left: target, behavior: 'smooth' });
            } else {
                track.scrollLeft = target;
            }
        };

        buildDots();
        paint();

        if (prev) {
            prev.addEventListener('click', function () {
                scrollToPage(Math.max(0, currentPage() - 1));
            });
        }

        if (next) {
            next.addEventListener('click', function () {
                scrollToPage(Math.min(pageCount() - 1, currentPage() + 1));
            });
        }

        track.addEventListener('scroll', function () {
            window.requestAnimationFrame(paint);
        }, { passive: true });

        window.addEventListener('resize', function () {
            buildDots();
            paint();
        });
    });

    /* ------------------------------------------------------------ FAQ search */

    Array.prototype.forEach.call(document.querySelectorAll('[data-faq]'), function (faq) {
        var input = faq.querySelector('[data-faq-search]');
        var groups = Array.prototype.slice.call(faq.querySelectorAll('[data-faq-group]'));
        var empty = faq.querySelector('[data-faq-empty]');
        var countEl = faq.querySelector('[data-faq-count]');

        if (!input) {
            return;
        }

        input.addEventListener('input', function () {
            var term = input.value.trim().toLowerCase();
            var visible = 0;

            groups.forEach(function (group) {
                var items = Array.prototype.slice.call(group.querySelectorAll('[data-faq-item]'));
                var shown = 0;

                items.forEach(function (item) {
                    var haystack = item.getAttribute('data-search') || item.textContent.toLowerCase();
                    var match = term === '' || haystack.indexOf(term) !== -1;
                    item.hidden = !match;
                    if (match) {
                        shown++;
                    }
                });

                group.hidden = shown === 0;
                visible += shown;
            });

            if (empty) {
                empty.hidden = visible > 0;
            }

            if (countEl) {
                countEl.textContent = term === ''
                    ? ''
                    : visible + (visible === 1 ? ' question matches' : ' questions match') + ' “' + input.value.trim() + '”';
            }
        });
    });

    /* ---------------------------------------------------------------- forms */

    var normaliseDigits = function (value) {
        var digits = value.replace(/\D+/g, '');
        if (digits.length === 10) {
            return digits;
        }
        if (digits.length === 12 && digits.indexOf('91') === 0) {
            return digits.slice(2);
        }
        return value;
    };

    Array.prototype.forEach.call(document.querySelectorAll('input[type="tel"], input[inputmode="tel"]'), function (input) {
        input.addEventListener('blur', function () {
            var cleaned = normaliseDigits(input.value.trim());
            if (cleaned !== input.value) {
                input.value = cleaned;
            }
        });
    });

    /* Live character counters. */
    Array.prototype.forEach.call(document.querySelectorAll('[data-counter]'), function (field) {
        var target = document.getElementById(field.getAttribute('data-counter'));
        if (!target) {
            return;
        }

        var max = parseInt(field.getAttribute('maxlength') || '0', 10);

        var update = function () {
            var length = field.value.length;
            target.textContent = length + (max ? ' / ' + max : '');
            target.classList.toggle('is-over', max > 0 && length > max);
        };

        field.addEventListener('input', update);
        update();
    });

    /* Suggestion chips that fill a textarea. */
    Array.prototype.forEach.call(document.querySelectorAll('[data-chip-fill]'), function (chip) {
        chip.addEventListener('click', function () {
            var target = document.querySelector('[name="' + chip.getAttribute('data-chip-fill') + '"]');
            if (!target) {
                return;
            }

            var value = chip.getAttribute('data-chip-value') || chip.textContent.trim();
            var lead = target.value && !/\s$/.test(target.value) ? target.value + ' ' : target.value;

            target.value = lead + value;
            target.focus();

            if (target.hasAttribute('data-counter')) {
                target.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });
    });

    /* Single-select radio chip groups. */
    Array.prototype.forEach.call(document.querySelectorAll('[data-chip-group]'), function (group) {
        var inputs = Array.prototype.slice.call(group.querySelectorAll('[data-chip-input]'));

        inputs.forEach(function (input) {
            input.addEventListener('change', function () {
                inputs.forEach(function (other) {
                    var label = other.closest('.chip');
                    if (label) {
                        label.classList.toggle('is-active', other.checked);
                    }
                });
            });
        });
    });

    /* Guard the booking date fields on the client as well as the server. */
    Array.prototype.forEach.call(document.querySelectorAll('input[type="date"]'), function (input) {
        var sync = function () {
            var min = input.min ? new Date(input.min + 'T00:00:00') : null;
            var max = input.max ? new Date(input.max + 'T00:00:00') : null;
            var value = input.value ? new Date(input.value + 'T00:00:00') : null;

            if (value && min && value < min) {
                input.value = '';
                input.setCustomValidity('Choose a date from today onwards.');
            } else if (value && max && value > max) {
                input.value = '';
                input.setCustomValidity('That date is too far ahead.');
            } else {
                input.setCustomValidity('');
            }
        };

        input.addEventListener('change', sync);
    });

    /* ------------------------------------------------- AJAX form submission */

    function csrfToken(form) {
        var field = form.querySelector('input[name="_token"]');
        return field ? field.value : '';
    }

    function setCsrfToken(form, token) {
        var field = form.querySelector('input[name="_token"]');
        if (field) {
            field.value = token;
        }
    }

    function fetchCsrf(form) {
        var url = form.getAttribute('data-csrf-url') || '/api/csrf.php';

        return fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (data && data.token) {
                    setCsrfToken(form, data.token);
                    return data.token;
                }
                return csrfToken(form);
            })
            .catch(function () { return csrfToken(form); });
    }

    function fieldWrap(input) {
        return input.closest('.field') || input.parentElement;
    }

    function clearFieldErrors(form) {
        Array.prototype.forEach.call(form.querySelectorAll('.field__error[data-js]'), function (node) {
            node.remove();
        });

        Array.prototype.forEach.call(form.querySelectorAll('[aria-invalid="true"]'), function (input) {
            input.removeAttribute('aria-invalid');
            var wrap = fieldWrap(input);
            if (wrap) {
                wrap.classList.remove('field--error');
            }
        });
    }

    function showFieldErrors(form, errors) {
        var first = null;

        Object.keys(errors || {}).forEach(function (name) {
            var input = form.querySelector('[name="' + name + '"]');
            if (!input) {
                return;
            }

            input.setAttribute('aria-invalid', 'true');

            var wrap = fieldWrap(input);
            if (wrap) {
                wrap.classList.add('field--error');
            }

            var note = document.createElement('p');
            note.className = 'field__error';
            note.setAttribute('data-js', '');
            note.textContent = errors[name];
            (wrap || input.parentElement).appendChild(note);

            if (!first) {
                first = input;
            }
        });

        if (first) {
            first.focus({ preventScroll: true });
            first.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'center' });
        }
    }

    function setStatus(form, message, kind) {
        var status = form.querySelector('[data-form-status]');
        if (!status) {
            return;
        }

        status.textContent = message;
        status.className = 'form__status' + (kind ? ' form__status--' + kind : '');
    }

    function setBusy(form, busy) {
        var button = form.querySelector('[data-submit-button]') || form.querySelector('button[type="submit"]');
        var spinner = form.querySelector('[data-submit-spinner]');

        if (button) {
            button.classList.toggle('is-busy', busy);
            button.toggleAttribute('disabled', busy);
            button.setAttribute('aria-busy', busy ? 'true' : 'false');
        }

        if (spinner) {
            spinner.toggleAttribute('hidden', !busy);
        }
    }

    Array.prototype.forEach.call(document.querySelectorAll('form[data-ajax-form]'), function (form) {
        var status = form.querySelector('[data-form-status]');

        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                form.reportValidity();
                return;
            }

            event.preventDefault();
            clearFieldErrors(form);

            var payload = new FormData(form);
            setBusy(form, true);
            setStatus(form, 'Sending your request — please wait a moment. Do not close this tab.', 'pending');

            track('form_submit', form.getAttribute('data-form-name') || 'form');

            fetchCsrf(form)
                .then(function () {
                    payload = new FormData(form);

                    return fetch(form.getAttribute('action') || window.location.pathname, {
                        method: 'POST',
                        body: payload,
                        credentials: 'same-origin',
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    });
                })
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { status: response.status, data: data };
                    });
                })
                .then(function (result) {
                    setBusy(form, false);

                    if (result.data && result.data.ok) {
                        form.reset();
                        setStatus(form, result.data.message || 'Thank you. We will be in touch shortly.', 'success');
                        track('form_success', form.getAttribute('data-form-name') || 'form');
                        if (status) {
                            status.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'center' });
                        }
                        return;
                    }

                    var message = (result.data && result.data.message) || 'Something went wrong. Please try again.';
                    setStatus(form, message, 'error');
                    track('form_error', form.getAttribute('data-form-name') || 'form');

                    if (result.data && result.data.errors) {
                        showFieldErrors(form, result.data.errors);
                    }
                })
                .catch(function () {
                    setBusy(form, false);
                    setStatus(form, 'We could not reach the server. Check your connection and try again, or call the clinic.', 'error');
                });
        });

        /* Keep the honeypot empty for honest visitors. */
        var honeypot = form.querySelector('#website');
        if (honeypot) {
            honeypot.addEventListener('input', function () {
                honeypot.value = '';
            });
        }
    });

    /* No-JavaScript fallback: the endpoint redirects back with a status flag. */
    (function statusFromQuery() {
        if (!window.location.search || !document.querySelector('form[data-ajax-form]')) {
            return;
        }

        var params = new URLSearchParams(window.location.search);
        var flags = {
            sent: 'Thank you. Your request has been received and we will be in touch shortly.',
            limited: 'Too many submissions from this device. Please wait a few minutes, or call the clinic.',
            invalid: 'Please check the highlighted fields and submit again.',
            error: 'Something went wrong on our side. Please try again or call the clinic.'
        };

        Object.keys(flags).forEach(function (key) {
            if (params.get(key) !== '1') {
                return;
            }

            var form = document.querySelector('form[data-ajax-form]');
            if (form) {
                setStatus(form, flags[key], key === 'sent' ? 'success' : 'error');
            }

            if (history.replaceState) {
                params.delete(key);
                var query = params.toString();
                history.replaceState(null, '', window.location.pathname + (query ? '?' + query : ''));
            }
        });
    })();

    /* ------------------------------------------------------------ flash bars */

    Array.prototype.forEach.call(document.querySelectorAll('[data-flash]'), function (flash) {
        var close = flash.querySelector('[data-flash-close]');
        var dismiss = function () {
            flash.classList.add('is-hiding');
            window.setTimeout(function () { flash.remove(); }, 320);
        };

        if (close) {
            close.addEventListener('click', dismiss);
        }

        if (flash.classList.contains('flash--success')) {
            window.setTimeout(dismiss, 9000);
        }
    });

    /* --------------------------------------------------- floating call button */

    var floaters = document.querySelector('.floaters');

    if (floaters) {
        window.addEventListener('scroll', function () {
            floaters.classList.toggle('is-compact', window.pageYOffset > 520);
        }, { passive: true });
    }

    /* ------------------------------------------------------------- analytics */

    function track(event, detail) {
        if (Array.isArray(window.dataLayer)) {
            window.dataLayer.push({ event: event, detail: detail || null });
        }
    }

    Array.prototype.forEach.call(document.querySelectorAll('[data-track], [data-call-track]'), function (el) {
        el.addEventListener('click', function () {
            track(el.getAttribute('data-track') || (el.hasAttribute('data-call-track') ? 'click_call' : 'click'), el.getAttribute('href'));
        });
    });

    /* ------------------------------------------------- anchor links + copy */

    Array.prototype.forEach.call(document.querySelectorAll('a[href^="#"]'), function (link) {
        link.addEventListener('click', function (event) {
            var id = link.getAttribute('href').slice(1);
            if (!id) {
                return;
            }

            var target = document.getElementById(id);
            if (!target) {
                return;
            }

            event.preventDefault();
            target.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });

            if (history.replaceState) {
                history.replaceState(null, '', '#' + id);
            }

            if (!target.hasAttribute('tabindex')) {
                target.setAttribute('tabindex', '-1');
            }
            target.focus({ preventScroll: true });
        });
    });

    /* Scroll to the FAQ entry the visitor was linked to. */
    if (window.location.hash) {
        var deep = document.querySelector('[data-faq-item][id="' + window.location.hash.slice(1) + '"]');
        if (deep && deep.tagName === 'DETAILS') {
            deep.open = true;
        }
    }

    /* The static 404 page is one shared file, so it cannot know which address
       the visitor typed. Show it here instead of echoing a build-time path. */
    Array.prototype.forEach.call(document.querySelectorAll('[data-404-path]'), function (node) {
        var requested = window.location.pathname + window.location.search;

        if (requested && requested !== '/' && requested.length > 1) {
            node.textContent = requested;
        }
    });
}());