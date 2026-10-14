import { initSidebar, initModals } from './modules/admin-shell';
import { initForms } from './modules/forms';
import { initTabs, initFlashToasts, toast } from './modules/ui';

window.toast = toast;

document.addEventListener('DOMContentLoaded', () => {
    initSidebar();
    initModals();
    initForms();
    initTabs();
    initFlashToasts();
});
