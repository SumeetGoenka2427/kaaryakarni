import { initNav } from './modules/nav';
import { initCarousels } from './modules/carousel';
import { initLightbox } from './modules/lightbox';
import { initForms, initOtpInputs } from './modules/forms';
import { initAccordions, initTabs, initFlashToasts, initProgressBars } from './modules/ui';

document.addEventListener('DOMContentLoaded', () => {
    initNav();
    initCarousels();
    initLightbox();
    initForms();
    initOtpInputs();
    initAccordions();
    initTabs();
    initProgressBars();
    initFlashToasts();
});
