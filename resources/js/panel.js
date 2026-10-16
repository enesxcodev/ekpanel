import '@tabler/core';
import $ from 'jquery';
import select2 from 'select2';
import { initializeCrudNotifications, notify } from './admin/crud-notification';
import { initializeTables } from './admin/table-import';

import { initializePermissionMatrix } from './admin/permission-matrix';
import { initializeFormConfirmation } from './admin/form-confirmation';
import { initializeSettings } from './admin/settings';
import { initializePages } from './admin/pages';
import { initializeMenus } from './admin/menus';

select2(window, $);
window.$ = $;
window.jQuery = $;

window.Panel = { notify };

initializeCrudNotifications();
initializeTables();
initializePermissionMatrix();
initializeFormConfirmation();
initializeSettings();
initializePages();
initializeMenus();
