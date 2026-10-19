import '@tabler/core';
import $ from 'jquery';
import select2 from 'select2';
import { initializeCrudNotifications, notify } from './admin/crud-notification';
import { initializeTables } from './admin/table-import';
import { initializeNotifications } from './admin/notifications';

import { initializePermissionMatrix } from './admin/permission-matrix';
import { confirmWithModal, initializeFormConfirmation } from './admin/form-confirmation';
import { initializeSettings } from './admin/settings';
import { initializeFormTabs } from './admin/form-tabs';
import { initializeSlugs } from './admin/slug';
import { initializePages } from './admin/pages';
import { initializeModuleAssets } from './admin/module-assets';
import { initializeModuleInstallations } from './admin/module-installations';
import { initializeMedia } from './admin/media';
import { initializeDatepickers } from './admin/datepicker';
import { initializeFormBuilder } from './admin/form-builder';

select2(window, $);
window.$ = $;
window.jQuery = $;
$('.js-select2').select2({ width: '100%' });

window.Panel = { notify, confirm: confirmWithModal };

initializeCrudNotifications();
initializeTables();
initializeNotifications();
initializePermissionMatrix();
initializeFormConfirmation();
initializeSettings();
initializeFormTabs();
initializeSlugs();
initializePages();
if (document.querySelector('[data-hugerte]')) {
    import('./admin/pages-editor');
}
initializeMedia();
initializeDatepickers();
initializeFormBuilder();
initializeModuleAssets(window.Panel);
initializeModuleInstallations();

import { initializeTheme } from './admin/theme';
initializeTheme();
