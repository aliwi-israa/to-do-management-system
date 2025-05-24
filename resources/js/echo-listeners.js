const userMeta = document.head.querySelector('meta[name="user-id"]');
if (userMeta) {
    const userId = userMeta.content;

    window.Echo.private('tasks.' + userId)
        .listen('TaskUpdated', (e) => {
            console.log('Task updated:', e.task);
        });
}
