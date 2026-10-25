{{--
    Row actions for admin tables: one "Actions" button that opens a menu.
    Put the usual links / forms / modal buttons inside — they keep working
    (data-confirm, data-modal-open) and are restyled as menu items:

    <x-admin.actions>
        <a href="..." class="btn btn-outline btn-sm">Edit</a>
        <form method="POST" action="..." data-confirm="Delete?">@csrf @method('DELETE')
            <button type="submit" class="btn btn-danger-outline btn-sm">Delete</button></form>
    </x-admin.actions>

    When a row ends up with a single action, that button is shown directly
    (resources/js/modules/action-menu.js).
--}}
@props(['label' => 'Actions'])
<div {{ $attributes->class('action-menu') }} data-action-menu>
    <button type="button" class="btn btn-outline btn-sm action-menu-toggle" aria-haspopup="true" aria-expanded="false">
        {{ $label }} <span class="action-menu-caret" aria-hidden="true">&#9662;</span>
    </button>
    <div class="action-menu-list" role="menu" hidden>
        {{ $slot }}
    </div>
</div>
