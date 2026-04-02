// loading.js — shared loading overlay with progress simulation

function showLoading(title, message, steps) {
    var overlay = document.getElementById('loading-overlay');
    if (!overlay) return;

    document.getElementById('loading-title').textContent   = title;
    document.getElementById('loading-message').textContent = message;

    var stepsContainer = document.getElementById('loading-steps');
    stepsContainer.innerHTML = '';

    steps.forEach(function(step, i) {
        var div       = document.createElement('div');
        div.className = 'loading-step' + (i === 0 ? ' active' : '');
        div.id        = 'step-' + i;
        div.innerHTML = '<span class="step-dot"></span>' + step;
        stepsContainer.appendChild(div);
    });

    overlay.classList.add('active');
    simulateProgress(steps.length);
}

function simulateProgress(totalSteps) {
    var bar       = document.getElementById('progress-bar-fill');
    var label     = document.getElementById('progress-label');
    var progress  = 0;
    var stepIndex = 0;
    var stepSize  = 100 / totalSteps;

    if (window._loadingInterval) {
        clearInterval(window._loadingInterval);
    }

    var interval = setInterval(function() {
        var increment;
        if (progress < 30)      increment = 2.5;
        else if (progress < 60) increment = 1.5;
        else if (progress < 80) increment = 0.8;
        else if (progress < 92) increment = 0.3;
        else                    increment = 0;

        progress = Math.min(progress + increment, 95);
        bar.style.width   = progress + '%';
        label.textContent = Math.round(progress) + '%';

        var newStepIndex = Math.floor(progress / stepSize);
        if (newStepIndex !== stepIndex && newStepIndex < totalSteps) {
            var oldStep = document.getElementById('step-' + stepIndex);
            if (oldStep) {
                oldStep.classList.remove('active');
                oldStep.classList.add('done');
            }
            stepIndex = newStepIndex;
            var newStep = document.getElementById('step-' + stepIndex);
            if (newStep) newStep.classList.add('active');
        }

    }, 300);

    window._loadingInterval = interval;
}

function hideLoading() {
    var overlay = document.getElementById('loading-overlay');
    if (!overlay) return;

    var bar   = document.getElementById('progress-bar-fill');
    var label = document.getElementById('progress-label');
    if (bar)   bar.style.width   = '100%';
    if (label) label.textContent = '100%';

    if (window._loadingInterval) {
        clearInterval(window._loadingInterval);
        window._loadingInterval = null;
    }

    document.querySelectorAll('.loading-step').forEach(function(s) {
        s.classList.remove('active');
        s.classList.add('done');
    });

    setTimeout(function() {
        if (overlay) overlay.classList.remove('active');
    }, 600);
}

function resetLoadingOverlay() {
    if (window._loadingInterval) {
        clearInterval(window._loadingInterval);
        window._loadingInterval = null;
    }

    var overlay = document.getElementById('loading-overlay');
    var bar     = document.getElementById('progress-bar-fill');
    var label   = document.getElementById('progress-label');
    var steps   = document.querySelectorAll('.loading-step');

    if (overlay) {
        overlay.classList.remove('active');
        // Force hide with inline style as belt-and-braces
        overlay.style.display = 'none';
    }
    if (bar)   bar.style.width   = '0%';
    if (label) label.textContent = '0%';

    steps.forEach(function(s) {
        s.classList.remove('active', 'done');
    });
}

// Fire on normal page load
document.addEventListener('DOMContentLoaded', function() {
    resetLoadingOverlay();
    // Remove any inline display style so CSS takes over
    var overlay = document.getElementById('loading-overlay');
    if (overlay) overlay.style.display = '';
});

// Fire when navigating back/forward (handles bfcache)
window.addEventListener('pageshow', function(event) {
    resetLoadingOverlay();
    // Small delay to ensure bfcache restoration is complete
    setTimeout(function() {
        var overlay = document.getElementById('loading-overlay');
        if (overlay) {
            overlay.classList.remove('active');
            overlay.style.display = 'none';
            setTimeout(function() {
                overlay.style.display = '';
            }, 50);
        }
        var bar   = document.getElementById('progress-bar-fill');
        var label = document.getElementById('progress-label');
        if (bar)   bar.style.width   = '0%';
        if (label) label.textContent = '0%';
    }, 10);
});

// Also fire on popstate (browser back/forward button)
window.addEventListener('popstate', function() {
    resetLoadingOverlay();
});
