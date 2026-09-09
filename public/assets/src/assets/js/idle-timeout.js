// Idle-timeout auto-logout.
// Last-activity timestamp lives in localStorage so activity in any tab
// resets the idle clock for every open tab.
(function () {
    var IDLE_LIMIT_MS = (window.IDLE_TIMEOUT_MS) || (10 * 60 * 1000);
    var WARNING_MS = (window.IDLE_WARNING_MS) || (60 * 1000);
    var STORAGE_KEY = 'dt_last_activity';
    var CHECK_INTERVAL_MS = 1000;
    var ACTIVITY_THROTTLE_MS = 1000;

    var modalEl = document.getElementById('idle-timeout-modal');
    var countdownEl = document.getElementById('idle-timeout-countdown');
    var stayBtn = document.getElementById('idle-timeout-stay-btn');
    if (!modalEl || !stayBtn) return;

    var bsModal = window.bootstrap ? new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: false }) : null;
    var modalShown = false;
    var lastActivityWrite = 0;

    function now() {
        return Date.now();
    }

    function getLastActivity() {
        var stored = parseInt(localStorage.getItem(STORAGE_KEY), 10);
        return isNaN(stored) ? now() : stored;
    }

    function recordActivity() {
        var t = now();
        if (t - lastActivityWrite < ACTIVITY_THROTTLE_MS) return;
        lastActivityWrite = t;
        localStorage.setItem(STORAGE_KEY, String(t));
        if (modalShown) hideWarning();
    }

    function showWarning() {
        if (modalShown) return;
        modalShown = true;
        if (bsModal) bsModal.show();
    }

    function hideWarning() {
        modalShown = false;
        if (bsModal) bsModal.hide();
    }

    function doLogout() {
        window.location.href = '/logout';
    }

    function keepAlive() {
        var token = document.querySelector('meta[name="csrf-token"]');
        fetch('/keepalive', {
            credentials: 'same-origin',
            headers: token ? { 'X-CSRF-TOKEN': token.content } : {}
        }).catch(function () {});
    }

    stayBtn.addEventListener('click', function () {
        recordActivity();
        lastActivityWrite = 0; // force-write even if throttled
        recordActivity();
        keepAlive();
        hideWarning();
    });

    ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart', 'click'].forEach(function (evt) {
        document.addEventListener(evt, recordActivity, { passive: true });
    });

    // Seed on load so a freshly opened tab doesn't inherit a stale timestamp.
    localStorage.setItem(STORAGE_KEY, String(now()));

    setInterval(function () {
        var idleFor = now() - getLastActivity();

        if (idleFor >= IDLE_LIMIT_MS) {
            doLogout();
            return;
        }

        if (idleFor >= IDLE_LIMIT_MS - WARNING_MS) {
            var secondsLeft = Math.max(0, Math.ceil((IDLE_LIMIT_MS - idleFor) / 1000));
            if (countdownEl) countdownEl.textContent = secondsLeft;
            showWarning();
        } else if (modalShown) {
            hideWarning();
        }
    }, CHECK_INTERVAL_MS);
})();
