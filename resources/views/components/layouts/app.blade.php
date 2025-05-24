<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? 'Page Title' }}</title>
                <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles

    </head>
    <body>
        <div>
            <livewire:layout.navigation />
            {{ $slot }}
        </div>
        @livewireScripts
<!-- <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script> -->
<script src="https://cdn.jsdelivr.net/gh/livewire/sortable@v1.x.x/dist/livewire-sortable.js"></script>

<script>
    // new Sortable(document.getElementById('task-list'), {handle:'.cursor-move'});

    // function initSortable() {
    //     const el = document.getElementById('task-list');
    //     if (!el) return;

    //     // Destroy previous Sortable instance if it exists
    //     if (el.sortableInstance) {
    //         el.sortableInstance.destroy();
    //     }

    //     // Create new Sortable instance and store reference on DOM
    //     el.sortableInstance = new Sortable(el, {
    //         animation: 150,
    //         handle: '.cursor-move',
    //         onEnd: function () {
    //             let ids = Array.from(el.children)
    //                 .filter(row => row.dataset.id)
    //                 .map(item => item.dataset.id);
    //             Livewire.emit('updateOrder', ids);
    //         }
    //     });
    // }

    // document.addEventListener('livewire:load', function () {
    //     initSortable();
    // });

    // // Always re-init after Livewire updates the DOM
    // document.addEventListener('DOMContentLoaded', function () {
    //     if (window.Livewire) {
    //         window.Livewire.hook('message.processed', () => {
    //             initSortable();
    //         });
    //     }
    // });
</script>
    </body>
</html>
