import './bootstrap';
import Alpine from 'alpinejs';
import Collapse from '@alpinejs/collapse';
import 'flowbite';
import './session-timeout';
import './connectivity';
import { registerNotificacionesDropdown } from './notificaciones';
import ApexCharts from 'apexcharts';

Alpine.plugin(Collapse);

window.Alpine = Alpine;
window.ApexCharts = ApexCharts;

registerNotificacionesDropdown();

Alpine.start();
