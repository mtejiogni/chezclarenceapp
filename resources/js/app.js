import './bootstrap';
import '../css/app.css';

import Alpine from 'alpinejs';
import Swal from 'sweetalert2';
import Chart from 'chart.js/auto';

// Rendre accessibles globalement depuis les vues Blade
window.Alpine = Alpine;
window.Swal   = Swal;
window.Chart  = Chart;

// Démarrer Alpine
Alpine.start();