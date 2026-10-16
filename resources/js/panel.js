import '@tabler/core';
import { initializeCrudNotifications, notify } from './admin/crud-notification';
import { initializeTables } from './admin/table-import';

import { initializePermissionMatrix } from './admin/permission-matrix';
import { initializeFormConfirmation } from './admin/form-confirmation';
import { initializeSettings } from './admin/settings';

window.Panel = { notify };

initializeCrudNotifications();
initializeTables();
initializePermissionMatrix();
initializeFormConfirmation();
initializeSettings();
