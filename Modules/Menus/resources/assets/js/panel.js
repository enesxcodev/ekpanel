import { initializeMenuWorkspace } from './workspace';
import { initializeMenus } from './menus';
import '../css/panel.css';

export default function initialize(panel) {
    initializeMenuWorkspace(panel);
    initializeMenus();
}
