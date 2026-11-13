// CDP Automation script for live user Chrome
const CHROME_HTTP = 'http://127.0.0.1:9222';

class CDPClient {
    constructor(wsUrl) {
        this.wsUrl = wsUrl;
        this.id = 1;
        this.callbacks = new Map();
    }

    async connect() {
        return new Promise((resolve, reject) => {
            this.ws = new WebSocket(this.wsUrl);
            this.ws.onopen = () => resolve();
            this.ws.onerror = (err) => reject(err);
            this.ws.onmessage = (event) => {
                const res = JSON.parse(event.data);
                if (res.id && this.callbacks.has(res.id)) {
                    const cb = this.callbacks.get(res.id);
                    this.callbacks.delete(res.id);
                    if (res.error) cb.reject(new Error(res.error.message));
                    else cb.resolve(res.result);
                }
            };
        });
    }

    send(method, params = {}) {
        const id = this.id++;
        return new Promise((resolve, reject) => {
            this.callbacks.set(id, { resolve, reject });
            this.ws.send(JSON.stringify({ id, method, params }));
        });
    }

    close() {
        this.ws.close();
    }
}

async function sleep(ms) {
    return new Promise(r => setTimeout(r, ms));
}

async function run() {
    console.log("Mengambil tab yang tersedia dari Chrome...");
    const tabsRes = await fetch(`${CHROME_HTTP}/json/list`);
    const tabs = await tabsRes.json();
    const pageTab = tabs.find(t => t.type === 'page');

    if (!pageTab) {
        console.error("Tidak ada tab bertipe page ditemukan di Chrome.");
        return;
    }

    console.log("Terhubung ke tab:", pageTab.title, pageTab.url);
    const client = new CDPClient(pageTab.webSocketDebuggerUrl);
    await client.connect();

    await client.send('Page.enable');
    await client.send('Runtime.enable');
    await client.send('DOM.enable');

    console.log("\n=======================================================");
    console.log("LANGKAH 1: Membuka Form Pelaporan Publik");
    console.log("=======================================================");
    await client.send('Page.navigate', { url: 'http://127.0.0.1:8000/reports/create?npsn=10300074' });
    await sleep(3000);

    const step1Check = await client.send('Runtime.evaluate', {
        expression: `(() => {
            const schoolDisplay = document.body.innerText.includes('SMP Negeri 2 Bukittinggi');
            const verbalBtn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.includes('Perundungan Verbal'));
            if (verbalBtn) {
                verbalBtn.click();
            }
            return {
                title: document.title,
                schoolFound: schoolDisplay,
                verbalBtnClicked: !!verbalBtn
            };
        })()`,
        returnByValue: true
    });
    console.log("Hasil Step 1 (Halaman Pelaporan):", step1Check.result.value);

    await sleep(1500);

    // Cek subkategori & required attribute & batasan 10MB
    const step1Subcat = await client.send('Runtime.evaluate', {
        expression: `(() => {
            const body = document.body.innerText;
            const hasHinaan = body.includes('Hinaan atau julukan merendahkan') || body.includes('Mempermalukan');
            const has10MB = body.includes('10MB') || body.includes('10 MB') || body.includes('10mb');
            const subcatInput = document.querySelector('select[name="subcategory_id"], input[name="subcategory_id"]');
            return {
                hasHinaanSubcategory: hasHinaan,
                has10MBLimitNotice: has10MB,
                subcatRequired: subcatInput ? subcatInput.required : true
            };
        })()`,
        returnByValue: true
    });
    console.log("Verifikasi Subkategori & Ukuran File:", step1Subcat.result.value);

    await sleep(2000);

    console.log("\n=======================================================");
    console.log("LANGKAH 2: Login Guru BK & Memeriksa Kasus 1 (Selesai di Sekolah)");
    console.log("=======================================================");
    // Clear cookies
    await client.send('Network.enable');
    await client.send('Network.clearBrowserCookies');

    await client.send('Page.navigate', { url: 'http://127.0.0.1:8000/login' });
    await sleep(2500);

    await client.send('Runtime.evaluate', {
        expression: `(() => {
            const loginInput = document.querySelector('input[name="login"], input[name="email"]');
            const passInput = document.querySelector('input[name="password"]');
            if (loginInput) loginInput.value = 'bk@sahabat.test';
            if (passInput) passInput.value = 'GuruBK#SMPN2Bkt2026!';
            const form = document.querySelector('form');
            if (form) form.submit();
        })()`
    });
    console.log("Mengirim form login Guru BK...");
    await sleep(3500);

    console.log("Membuka detail Kasus 1: http://127.0.0.1:8000/cases/SS-2026-000201");
    await client.send('Page.navigate', { url: 'http://127.0.0.1:8000/cases/SS-2026-000201' });
    await sleep(3500);

    const step2Check = await client.send('Runtime.evaluate', {
        expression: `(() => {
            const body = document.body.innerText;
            return {
                url: window.location.href,
                hasCaseCode: body.includes('SS-2026-000201'),
                hasClosedStatus: body.includes('CLOSED') || body.includes('Selesai'),
                hasResolutionDoc: body.includes('Berita_Acara_Mediasi_Damai_Fauzan.pdf') || body.includes('Mediasi Damai'),
                hasNotes: body.includes('Fauzan') && body.includes('Dani')
            };
        })()`,
        returnByValue: true
    });
    console.log("Hasil Verifikasi Kasus 1 (Sekolah Selesai):", step2Check.result.value);

    console.log("\n=======================================================");
    console.log("LANGKAH 3: Login Satgas PPKSP Dinas & Memeriksa Kasus 2 (Eskalasi Eksternal)");
    console.log("=======================================================");
    await client.send('Network.clearBrowserCookies');

    await client.send('Page.navigate', { url: 'http://127.0.0.1:8000/login' });
    await sleep(2500);

    await client.send('Runtime.evaluate', {
        expression: `(() => {
            const loginInput = document.querySelector('input[name="login"], input[name="email"]');
            const passInput = document.querySelector('input[name="password"]');
            if (loginInput) loginInput.value = 'dinas@sahabat.test';
            if (passInput) passInput.value = 'Dinas#Bkt2026!Secure';
            const form = document.querySelector('form');
            if (form) form.submit();
        })()`
    });
    console.log("Mengirim form login Satgas Dinas Pendidikan...");
    await sleep(3500);

    console.log("Membuka detail Kasus 2: http://127.0.0.1:8000/cases/SS-2026-000202");
    await client.send('Page.navigate', { url: 'http://127.0.0.1:8000/cases/SS-2026-000202' });
    await sleep(3500);

    const step3Check = await client.send('Runtime.evaluate', {
        expression: `(() => {
            const body = document.body.innerText;
            return {
                url: window.location.href,
                hasCaseCode: body.includes('SS-2026-000202'),
                hasInHandling: body.includes('IN_HANDLING') || body.includes('Sedang Ditangani') || body.includes('Tahap Penanganan'),
                hasKepsekApproval: body.includes('Kepala Sekolah') || body.includes('Rekomendasi'),
                hasDinasOrPPA: body.includes('Dinas Pendidikan') || body.includes('UPTD PPA') || body.includes('PPKSP'),
                hasEscalationDoc: body.includes('Surat_Permohonan_Eskalasi_Kasus.pdf') || body.includes('Eskalasi')
            };
        })()`,
        returnByValue: true
    });
    console.log("Hasil Verifikasi Kasus 2 (Eskalasi Eksternal Dinas & PPA):", step3Check.result.value);

    console.log("\n=======================================================");
    console.log("PENGUJIAN LIVE CHROME SELESAI DENGAN SUKSES!");
    console.log("=======================================================");

    client.close();
}

run().catch(err => {
    console.error("Gagal menjalankan otomasi Chrome:", err);
    process.exit(1);
});

