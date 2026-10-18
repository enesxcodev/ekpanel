<template data-media-template="card">
    <article class="media-file-card" data-media-item>
        <div class="media-card-top">
            <input type="checkbox" class="form-check-input" data-media-check aria-label="Dosyayı seç">
            <span class="media-favorite-indicator text-warning d-none"><x-admin.icon name="star" /></span>
            <div class="dropdown ms-auto">
                <button type="button" class="btn btn-sm btn-icon btn-ghost-secondary" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Dosya işlemleri"><x-admin.icon name="dots" /></button>
                <div class="dropdown-menu dropdown-menu-end" data-media-menu></div>
            </div>
        </div>
        <button type="button" class="media-file-open" data-action="select-file">
            <span class="media-thumbnail" data-media-thumbnail></span>
            <span class="media-file-name" data-media-name></span>
        </button>
        <div class="media-file-meta"><span class="text-secondary" data-media-size></span><span class="badge" data-media-extension></span></div>
    </article>
</template>
<template data-media-template="row">
    <tr data-media-item>
        <td><input type="checkbox" class="form-check-input" data-media-check aria-label="Dosyayı seç"></td>
        <td><button type="button" class="media-row-open" data-action="select-file"><span class="media-row-thumbnail" data-media-thumbnail></span><span data-media-name></span></button></td>
        <td><span class="badge" data-media-extension></span></td><td class="text-secondary" data-media-size></td>
        <td class="text-secondary text-nowrap" data-media-date></td>
        <td><div class="dropdown"><button type="button" class="btn btn-sm btn-icon btn-ghost-secondary" data-bs-toggle="dropdown" aria-label="Dosya işlemleri" aria-expanded="false"><x-admin.icon name="dots" /></button><div class="dropdown-menu dropdown-menu-end" data-media-menu></div></div></td>
    </tr>
</template>
<template data-media-template="folder">
    <article class="media-folder-card" data-media-folder>
        <button type="button" class="media-folder-open" data-action="open-folder"><x-admin.icon name="folder" /><span data-media-name></span></button>
        <div class="dropdown"><button type="button" class="btn btn-sm btn-icon btn-ghost-secondary" data-bs-toggle="dropdown" aria-label="Klasör işlemleri" aria-expanded="false"><x-admin.icon name="dots" /></button><div class="dropdown-menu dropdown-menu-end" data-media-menu></div></div>
    </article>
</template>
<template data-media-template="folder-row">
    <tr data-media-folder><td><x-admin.icon name="folder" /></td><td><button type="button" class="media-row-open" data-action="open-folder"><span data-media-name></span></button></td><td colspan="3" class="text-secondary">Klasör</td><td><div class="dropdown"><button type="button" class="btn btn-sm btn-icon btn-ghost-secondary" data-bs-toggle="dropdown" aria-label="Klasör işlemleri" aria-expanded="false"><x-admin.icon name="dots" /></button><div class="dropdown-menu dropdown-menu-end" data-media-menu></div></div></td></tr>
</template>
<template data-media-template="folder-icon"><x-admin.icon name="folder" /></template>
<template data-media-template="file-icon"><x-admin.icon name="file" /></template>
