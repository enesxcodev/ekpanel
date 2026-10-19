export function synchronizeEditorForm(editor) {
    const textarea = editor.getElement();
    const required = textarea.required;

    editor.on('init', () => {
        textarea.required = false;
    });
    editor.on('input change undo redo', () => {
        editor.save({ set_dirty: false });
    });
    editor.on('remove', () => {
        textarea.required = required;
    });
}
