export class DeleteTableRows {
    public static go(ids: number[]) {
        ids.forEach(id => DeleteTableRows.deleteRow(id));
    }

    private static deleteRow(id: number) {
        const el_selector = 'tr#post-' + id;
        const el = document.querySelector(el_selector);
        if (el) {
            el.remove();
        }
    }
}