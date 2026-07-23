// Internet Connectivity Monitor (Online/Offline)

document.addEventListener('DOMContentLoaded', () => {
    let connectionToast = null;

    function showToast(status) {
        // Remove existing toast if it exists
        if (connectionToast) {
            connectionToast.remove();
        }

        const isOnline = status === 'online';
        
        connectionToast = document.createElement('div');
        connectionToast.id = 'connectivity-toast';
        connectionToast.className = `fixed bottom-6 right-6 z-50 flex items-center space-x-3 px-4 py-3.5 rounded-xl shadow-2xl border transition-all duration-300 transform translate-y-0 scale-100 ${
            isOnline 
                ? 'bg-emerald-50 border-emerald-200 text-emerald-800' 
                : 'bg-rose-50 border-rose-200 text-rose-800 animate-pulse'
        }`;

        const icon = isOnline 
            ? `<svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
               </svg>`
            : `<svg class="h-5 w-5 text-rose-600 animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-3.536 4.978 4.978 0 011.414-3.536m0 0L8.464 11.29M3 3l18 18" />
               </svg>`;

        const message = isOnline 
            ? 'Conexión a internet restablecida' 
            : 'Sin conexión a internet. Revisa tu red.';

        connectionToast.innerHTML = `
            ${icon}
            <span class="text-sm font-semibold font-poppins">${message}</span>
        `;

        document.body.appendChild(connectionToast);

        // Auto remove success toast after 4 seconds
        if (isOnline) {
            setTimeout(() => {
                if (connectionToast) {
                    connectionToast.style.opacity = '0';
                    connectionToast.style.transform = 'translateY(10px) scale(0.95)';
                    setTimeout(() => connectionToast.remove(), 300);
                }
            }, 4000);
        }
    }

    // Event listeners for connectivity state
    window.addEventListener('online', () => {
        showToast('online');
    });

    window.addEventListener('offline', () => {
        showToast('offline');
    });

    // Check initial state if already offline
    if (!navigator.onLine) {
        showToast('offline');
    }
});
