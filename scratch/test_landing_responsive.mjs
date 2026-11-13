async function testViewport() {
    const tabsRes = await fetch('http://127.0.0.1:9222/json/list');
    const tabs = await tabsRes.json();
    const pageTab = tabs.find(t => t.type === 'page');
    if (!pageTab) {
        console.log("No page tab");
        return;
    }
    const ws = new WebSocket(pageTab.webSocketDebuggerUrl);

    ws.onopen = () => {
        ws.send(JSON.stringify({ id: 1, method: 'Page.navigate', params: { url: 'http://127.0.0.1:8000/' } }));
        setTimeout(() => {
            // Test 375px mobile
            ws.send(JSON.stringify({
                id: 2,
                method: 'Emulation.setDeviceMetricsOverride',
                params: { width: 375, height: 667, deviceScaleFactor: 1, mobile: true }
            }));
            setTimeout(() => {
                ws.send(JSON.stringify({
                    id: 3,
                    method: 'Runtime.evaluate',
                    params: {
                        expression: `(() => {
                            const heroArt = document.querySelector(".hero-art");
                            const img = document.querySelector(".hero-art-image");
                            const note = document.querySelector(".hero-note");
                            return {
                                heroArtRect: heroArt ? heroArt.getBoundingClientRect() : null,
                                imgRect: img ? img.getBoundingClientRect() : null,
                                noteRect: note ? note.getBoundingClientRect() : null,
                                scrollWidth: document.documentElement.scrollWidth,
                                clientWidth: document.documentElement.clientWidth,
                                hasHorizontalOverflow: document.documentElement.scrollWidth > document.documentElement.clientWidth
                            };
                        })()`,
                        returnByValue: true
                    }
                }));
            }, 1500);
        }, 2000);
    };

    ws.onmessage = (e) => {
        const msg = JSON.parse(e.data);
        if (msg.id === 3) {
            console.log('Metrics at 375px width:\n', JSON.stringify(msg.result.result.value, null, 2));
            ws.close();
        }
    };
}

testViewport();

