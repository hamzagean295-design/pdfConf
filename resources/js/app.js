import './bootstrap';
import Alpine from 'alpinejs';
import interact from 'interactjs';
import * as pdfjsLib from 'pdfjs-dist';

// Configurer le worker pour PDF.js (très important)
pdfjsLib.GlobalWorkerOptions.workerSrc = `/build/assets/pdf.worker.min.js`;

window.Alpine = Alpine;
window.interact = interact;
window.pdfjsLib = pdfjsLib;

Alpine.start();
