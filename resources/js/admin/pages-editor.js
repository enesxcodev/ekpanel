import hugerte from 'hugerte';
import 'hugerte/models/dom';
import 'hugerte/icons/default';
import 'hugerte/themes/silver';
import 'hugerte/skins/ui/oxide/skin.js';
import 'hugerte/skins/ui/oxide/content.js';
import 'hugerte/skins/content/default/content.js';
import 'hugerte/plugins/advlist';
import 'hugerte/plugins/autolink';
import 'hugerte/plugins/link';
import 'hugerte/plugins/lists';
import 'hugerte/plugins/table';
import 'hugerte/plugins/code';
import 'hugerte/plugins/wordcount';

hugerte.init({
    selector: '[data-hugerte]',
    plugins: 'advlist autolink link lists table code wordcount',
    toolbar: 'undo redo | blocks | bold italic underline | bullist numlist | blockquote | link table | removeformat | code',
    menubar: false,
    branding: false,
    height: 500,
    skin_url: 'default',
    content_css: 'default',
});
