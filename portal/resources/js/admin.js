import { initSidebar, initModals } from './modules/admin-shell';
import { initForms } from './modules/forms';
import { initActionMenus } from './modules/action-menu';
import { initLangSwitches } from './modules/lang-switch';
import { initTabs, initFlashToasts, toast } from './modules/ui';

window.toast = toast;

document.addEventListener('DOMContentLoaded', () => {
    initSidebar();
    initModals();
    initForms();
    initActionMenus();
    initTabs();
    initLangSwitches();
    initFlashToasts();
});
