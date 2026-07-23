// Session Timeout and Inactivity Handler

document.addEventListener('DOMContentLoaded', () => {
    const isAuthMeta = document.querySelector('meta[name="user-authenticated"]');
    if (!isAuthMeta || isAuthMeta.getAttribute('content') !== 'true') {
        return; // No user logged in or layout doesn't support session timeout
    }

    const lifetimeMeta = document.querySelector('meta[name="session-lifetime"]');
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

    // Convert minutes to milliseconds
    const sessionLifetimeMinutes = parseInt(lifetimeMeta ? lifetimeMeta.getAttribute('content') : '15', 10);
    const sessionLifetimeMs = sessionLifetimeMinutes * 60 * 1000;
    
    // Warning appears 2 minutes before expiration (or 1 minute if lifetime is very short)
    const warningBufferMs = sessionLifetimeMinutes > 2 ? 2 * 60 * 1000 : 30 * 1000;
    const idleTimeoutMs = sessionLifetimeMs - warningBufferMs;
    const pingIntervalMs = Math.min(5 * 60 * 1000, idleTimeoutMs / 2); // Ping every 5 mins or half the idle time

    let idleTimer = null;
    let warningTimer = null;
    let countdownInterval = null;
    let hasActivity = false;
    let lastPingTime = Date.now();

    // Track user activity to determine if keep-alive pings are needed
    const activityEvents = ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'];
    
    function handleActivity() {
        hasActivity = true;
        
        // If user is active and we haven't pinged recently, and warning modal is not open
        const now = Date.now();
        if (now - lastPingTime > pingIntervalMs && !document.getElementById('session-warning-modal')) {
            pingSession();
        }
    }

    activityEvents.forEach(event => {
        window.addEventListener(event, handleActivity, { passive: true });
    });

    // Function to ping the server to keep session alive
    async function pingSession() {
        if (!csrfToken) return;
        try {
            const response = await fetch('/session/ping', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (response.ok) {
                lastPingTime = Date.now();
                hasActivity = false;
                resetTimers();
            } else if (response.status === 401) {
                // Session already expired
                forceLogout();
            }
        } catch (error) {
            console.error('Error keeping session alive:', error);
        }
    }

    function resetTimers() {
        clearTimeout(idleTimer);
        clearTimeout(warningTimer);
        clearInterval(countdownInterval);
        removeWarningModal();

        // Start idle warning timer
        warningTimer = setTimeout(showWarningModal, idleTimeoutMs);
        
        // Start absolute expiration timer
        idleTimer = setTimeout(forceLogout, sessionLifetimeMs);
    }

    function showWarningModal() {
        // Create beautiful modal dynamically
        removeWarningModal();

        const modalHtml = `
            <div id="session-warning-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50 font-poppins">
                <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 max-w-md w-full p-6 transform transition-all duration-300 scale-100">
                    <div class="flex items-center space-x-3 text-amber-500 mb-4">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <h3 class="text-xl font-bold text-gray-950">¡Tu sesión va a expirar!</h3>
                    </div>
                    <p class="text-sm text-gray-700 mb-6 leading-relaxed">
                        Has estado inactivo. Por seguridad, tu sesión se cerrará automáticamente en:
                        <span id="session-countdown-timer" class="font-bold text-red-500 text-lg block mt-2 text-center bg-red-50 py-2 rounded-lg border border-red-100">2:00</span>
                    </p>
                    <div class="flex flex-col sm:flex-row sm:space-x-3 space-y-2 sm:space-y-0">
                        <button id="session-extend-btn" class="w-full bg-[#07321e] hover:bg-[#0c4e30] text-white font-semibold py-2.5 px-4 rounded-xl transition duration-200 focus:outline-none focus:ring-2 focus:ring-[#c2d500]">
                            Mantenerse Conectado
                        </button>
                        <button id="session-logout-btn" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2.5 px-4 rounded-xl transition duration-200 focus:outline-none">
                            Cerrar Sesión
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHtml);

        // Setup button handlers
        document.getElementById('session-extend-btn').addEventListener('click', () => {
            pingSession();
        });

        document.getElementById('session-logout-btn').addEventListener('click', () => {
            forceLogout();
        });

        // Initialize countdown
        let secondsLeft = Math.round(warningBufferMs / 1000);
        const updateCountdown = () => {
            const min = Math.floor(secondsLeft / 60);
            const sec = secondsLeft % 60;
            const timerEl = document.getElementById('session-countdown-timer');
            if (timerEl) {
                timerEl.textContent = `${min}:${sec < 10 ? '0' : ''}${sec}`;
            }
            if (secondsLeft <= 0) {
                clearInterval(countdownInterval);
                forceLogout();
            }
            secondsLeft--;
        };

        updateCountdown();
        countdownInterval = setInterval(updateCountdown, 1000);
    }

    function removeWarningModal() {
        const modal = document.getElementById('session-warning-modal');
        if (modal) {
            modal.remove();
        }
    }

    function forceLogout() {
        removeWarningModal();
        
        // Create a new form to avoid mutating the sidebar form DOM
        const logoutForm = document.createElement('form');
        logoutForm.method = 'POST';
        logoutForm.action = '/logout?expired=1';
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        
        logoutForm.appendChild(csrfInput);
        document.body.appendChild(logoutForm);
        logoutForm.submit();
    }

    // Initialize timers on page load
    resetTimers();
});
