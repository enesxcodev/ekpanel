<?php

return [
    'blog' => [
        'label' => 'Blog',
        'icon' => 'article',
        'order' => 55,
        'children' => [
            'contents' => ['label' => 'İçerikler', 'icon' => 'article', 'route' => 'admin.blog.index', 'active' => ['admin.blog.index', 'admin.blog.create', 'admin.blog.edit'], 'permission' => 'blog.view', 'menu_permission' => 'blog.menu', 'order' => 5],
            'categories' => ['label' => 'Kategoriler', 'icon' => 'category', 'route' => 'admin.blog.categories.index', 'active' => 'admin.blog.categories.*', 'permission' => 'blog.categories.view', 'menu_permission' => 'blog.categories.menu', 'order' => 10],
            'tags' => ['label' => 'Etiketler', 'icon' => 'tag', 'route' => 'admin.blog.tags.index', 'active' => 'admin.blog.tags.*', 'permission' => 'blog.tags.view', 'menu_permission' => 'blog.tags.menu', 'order' => 15],
        ],
    ],
];
