export function registerNotificacionesDropdown() {
    window.Alpine.data('notificacionesDropdown', () => ({
        abierto: false,
        notificaciones: [],
        noLeidas: 0,
        pollingInterval: null,
        prevNoLeidas: 0,
        campanaSacudida: false,

        init() {
            this.cargar();
            // Polling cada 15 segundos para mayor reactividad
            this.pollingInterval = setInterval(() => this.cargar(), 15000);
        },

        destroy() {
            if (this.pollingInterval) clearInterval(this.pollingInterval);
        },

        async cargar() {
            try {
                const res = await window.axios.get('/notificaciones');
                const nuevasNoLeidas = res.data.noLeidas;

                // Animar campana si hay nuevas notificaciones no leídas
                if (nuevasNoLeidas > this.prevNoLeidas && this.prevNoLeidas >= 0) {
                    this.sacudirCampana();
                }

                this.prevNoLeidas = nuevasNoLeidas;
                this.notificaciones = res.data.notificaciones;
                this.noLeidas = nuevasNoLeidas;
            } catch (e) {
                console.error('Error al cargar notificaciones:', e);
            }
        },

        sacudirCampana() {
            this.campanaSacudida = true;
            setTimeout(() => { this.campanaSacudida = false; }, 800);
        },

        toggle() {
            this.abierto = !this.abierto;
            if (this.abierto) this.cargar();
        },

        async marcarLeida(notif) {
            if (!notif.read_at) {
                try {
                    await window.axios.post(`/notificaciones/${notif.id}/leida`);
                    notif.read_at = new Date().toISOString();
                    this.noLeidas = Math.max(0, this.noLeidas - 1);
                } catch (e) {
                    console.error('Error al marcar notificación como leída:', e);
                }
            }

            if (notif.data?.url) {
                window.location.href = notif.data.url;
            }
        },

        async marcarTodasLeidas() {
            try {
                await window.axios.post('/notificaciones/todas-leidas');
                this.notificaciones.forEach(n => {
                    n.read_at = n.read_at || new Date().toISOString();
                });
                this.noLeidas = 0;
            } catch (e) {
                console.error('Error al marcar todas como leídas:', e);
            }
        },

        async borrarTodas() {
            if (this.notificaciones.length === 0) return;
            try {
                await window.axios.delete('/notificaciones/todas');
                this.notificaciones = [];
                this.noLeidas = 0;
            } catch (e) {
                console.error('Error al borrar notificaciones:', e);
            }
        },

        formatearFecha(fecha) {
            if (!fecha) return '';
            const date = new Date(fecha);
            const ahora = new Date();
            const diffMs = ahora - date;
            const diffMin = Math.floor(diffMs / 60000);
            const diffHoras = Math.floor(diffMs / 3600000);
            const diffDias = Math.floor(diffMs / 86400000);

            if (diffMin < 1) return 'Ahora';
            if (diffMin < 60) return `Hace ${diffMin} min`;
            if (diffHoras < 24) return `Hace ${diffHoras}h`;
            if (diffDias < 7) return `Hace ${diffDias} día${diffDias !== 1 ? 's' : ''}`;
            return date.toLocaleDateString('es-ES', { day: 'numeric', month: 'short' });
        },

        iconoPara(tipo) {
            const icons = {
                'nuevo_trabajo': 'M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                'asignacion':    'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
                'plazo':         'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                'retroalimentacion': 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z',
                'nueva_version': 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12',
                'aprobado':      'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z',
                'propuesta_evaluada': 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                'trabajo_retirado': 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
                'trabajo_reactivado': 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
                'trabajo_eliminado': 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16',
                'trabajo_aceptado': 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                'trabajo_rechazado': 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
                'info':    'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                'success': 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                'warning': 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z',
                'error':   'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
            };
            return icons[tipo] || icons['info'];
        },

        colorPara(tipo) {
            const colors = {
                'nuevo_trabajo':     { bg: 'bg-blue-100',   icon: 'text-blue-600'   },
                'asignacion':        { bg: 'bg-purple-100', icon: 'text-purple-600' },
                'plazo':             { bg: 'bg-amber-100',  icon: 'text-amber-600'  },
                'retroalimentacion': { bg: 'bg-indigo-100', icon: 'text-indigo-600' },
                'nueva_version':     { bg: 'bg-teal-100',   icon: 'text-teal-600'   },
                'aprobado':          { bg: 'bg-green-100',  icon: 'text-green-600'  },
                'propuesta_evaluada': { bg: 'bg-emerald-100',icon: 'text-emerald-600'},
                'trabajo_retirado':  { bg: 'bg-orange-100', icon: 'text-orange-600' },
                'trabajo_reactivado':{ bg: 'bg-emerald-100',icon: 'text-emerald-600'},
                'trabajo_eliminado': { bg: 'bg-red-100',    icon: 'text-red-600'    },
                'trabajo_aceptado':   { bg: 'bg-green-100',  icon: 'text-green-600'  },
                'trabajo_rechazado':  { bg: 'bg-red-100',    icon: 'text-red-600'    },
            };
            return colors[tipo] || { bg: 'bg-gray-100', icon: 'text-gray-500' };
        },
    }));
}
