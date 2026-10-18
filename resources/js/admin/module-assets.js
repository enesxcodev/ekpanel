export async function initializeModuleAssets(panel) {
    const configuration = document.querySelector('[data-module-panel-assets]');
    if (!configuration) return;

    for (const url of JSON.parse(configuration.textContent)) {
        try {
            const module = await import(/* @vite-ignore */ url);
            if (typeof module.default !== 'function') {
                throw new Error('Module asset must export an initializer.');
            }
            await module.default(panel);
        } catch (error) {
            console.error('Module panel initialization failed:', url, error);
            panel.notify({ type: 'error', message: 'Modül ekranı yüklenemedi. Sayfayı yenileyin.' });
        }
    }
}
