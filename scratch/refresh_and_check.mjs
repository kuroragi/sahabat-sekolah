// Check and refresh page
async function check() {
    const tabsRes = await fetch('http://127.0.0.1:9222/json/list');
    const tabs = await tabsRes.json();
    const pageTab = tabs.find(t => t.type === 'page');
    const ws = new WebSocket(pageTab.webSocketDebuggerUrl);

    ws.onopen = () => {
        ws.send(JSON.stringify({ id: 1, method: 'Page.reload' }));
        setTimeout(() => {
            ws.send(JSON.stringify({
                id: 2,
                method: 'Runtime.evaluate',
                params: {
                    expression: `(() => {
                        const body = document.body.innerText;
                        const idx = body.indexOf('Bukti');
                        return idx !== -1 ? body.substring(idx, idx + 1000) : 'Bagian bukti tidak ditemukan';
                    })()`,
                    returnByValue: true
                }
            }));
        }, 3000);
    };

    ws.onmessage = (e) => {
        const msg = JSON.parse(e.data);
        if (msg.id === 2) {
            console.log('Bukti tertera di Browser:\n', msg.result.result.value);
            ws.close();
        }
    };
}

check();

