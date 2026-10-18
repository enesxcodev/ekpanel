import '@tabler/core';
import $ from 'jquery';
import select2 from 'select2';
import { initializeCrudNotifications, notify } from './admin/crud-notification';
import { initializeTables } from './admin/table-import';

import { initializePermissionMatrix } from './admin/permission-matrix';
import { initializeFormConfirmation } from './admin/form-confirmation';
import { initializeSettings } from './admin/settings';
import { initializeFormTabs } from './admin/form-tabs';
import { initializeSlugs } from './admin/slug';
import { initializePages } from './admin/pages';
import { initializeMenuWorkspace } from './admin/menu';
import { initializeMedia } from './admin/media';
import { initializeDatepickers } from './admin/datepicker';
import { initializeFormBuilder } from './admin/form-builder';
const modulePanelAssets = import.meta.glob('../../Modules/*/resources/assets/js/panel.js', { eager: true });

select2(window, $);
window.$ = $;
window.jQuery = $;
$('.js-select2').select2({ width: '100%' });

window.Panel = { notify };

initializeCrudNotifications();
initializeTables();
initializePermissionMatrix();
initializeFormConfirmation();
initializeSettings();
initializeFormTabs();
initializeSlugs();
initializePages();
if (document.querySelector('[data-hugerte]')) {
    import('./admin/pages-editor');
}
initializeMenuWorkspace();
initializeMedia();
initializeDatepickers();
initializeFormBuilder();
Object.values(modulePanelAssets).forEach((module) => module.default?.());
