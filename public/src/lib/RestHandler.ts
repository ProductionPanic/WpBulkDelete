declare global {
    interface Window {
        ppBulkDelete: {
            ajaxUrl: string;
            nonce: string;
        }
    }
}

export class RestHandler {
    private static instance: RestHandler;
    private constructor() { }

    public static get(): RestHandler {
        if (!RestHandler.instance) {
            RestHandler.instance = new RestHandler();
        }

        return RestHandler.instance;
    }

    public stopBulkDelete(id: number): Promise<any> {
        return this.sendRequest('stop', id);
    }

    public pingBulkDelete(id: number): Promise<any> {
        return this.sendRequest('trigger', id);
    }

    private sendRequest(action: string, id: number): Promise<any> {
        const data = new FormData();
        data.append('task_id', id.toString());
        return fetch(`${window.ppBulkDelete.ajaxUrl}/${action}`, {
            method: 'POST',
            headers: {
                'X-WP-Nonce': window.ppBulkDelete.nonce,
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            body: new URLSearchParams(data as any)
        }).then(response => response.json());
    }
}