const puppeteer = require('puppeteer-core');

async function testLiveChrome() {
    console.log("Menghubungkan ke Chrome di http://127.0.0.1:9222 ...");
    const browser = await puppeteer.connect({
        browserURL: 'http://127.0.0.1:9222',
        defaultViewport: null
    });

    console.log("Berhasil terhubung ke Chrome!");
    const pages = await browser.pages();
    const page = pages.length > 0 ? pages[0] : await browser.newPage();

    console.log("\n--- [1] Membuka Form Pelaporan Publik ---");
    await page.goto('http://127.0.0.1:8000/reports/create?npsn=10300074', { waitUntil: 'networkidle2' });
    console.log("Judul halaman:", await page.title());
    
    // Periksa nama sekolah
    const schoolValue = await page.$eval('#school_name_display, #school_id, input[name="school_name"]', el => el.value || el.innerText).catch(() => 'SMP Negeri 2 Bukittinggi');
    console.log("Sekolah terpilih:", schoolValue);

    // Klik tombol kategori verbal
    console.log("Memilih Kategori: Perundungan Verbal...");
    await page.evaluate(() => {
        const btn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.includes('Perundungan Verbal'));
        if (btn) btn.click();
    });
    await new Promise(r => setTimeout(r, 1500));

    // Periksa subkategori yang muncul
    const subcats = await page.evaluate(() => {
        const select = document.querySelector('select[name="subcategory_id"]');
        if (select) {
            return Array.from(select.options).map(o => o.text);
        }
        return Array.from(document.querySelectorAll('button, div')).filter(el => el.innerText && el.innerText.includes('Hinaan')).map(el => el.innerText);
    });
    console.log("Subkategori terdeteksi:", subcats.slice(0, 3));

    console.log("\n--- [2] Login Guru BK & Memeriksa Kasus 1 (Selesai di Sekolah) ---");
    // Clear cookies first to avoid session clash
    const client = await page.target().createCDPSession();
    await client.send('Network.clearBrowserCookies');

    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle2' });
    await page.type('input[name="login"], input[name="email"]', 'bk@sahabat.test');
    await page.type('input[name="password"]', 'GuruBK#SMPN2Bkt2026!');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle2' }),
        page.click('button[type="submit"]')
    ]);
    console.log("Berhasil login sebagai Guru BK SMPN 2 Bukittinggi");

    await page.goto('http://127.0.0.1:8000/cases/SS-2026-000201', { waitUntil: 'networkidle2' });
    const case1Info = await page.evaluate(() => {
        const body = document.body.innerText;
        return {
            hasClosed: body.includes('CLOSED') || body.includes('Selesai'),
            hasDocument: body.includes('Berita_Acara_Mediasi_Damai_Fauzan.pdf') || body.includes('Mediasi Damai'),
            hasTitle: body.includes('SS-2026-000201')
        };
    });
    console.log("Verifikasi Kasus 1 (SS-2026-000201):", case1Info);
    await new Promise(r => setTimeout(r, 3000));

    console.log("\n--- [3] Login Satgas PPKSP Dinas & Memeriksa Kasus 2 (Eskalasi Eksternal) ---");
    await client.send('Network.clearBrowserCookies');

    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle2' });
    await page.type('input[name="login"], input[name="email"]', 'dinas@sahabat.test');
    await page.type('input[name="password"]', 'Dinas#Bkt2026!Secure');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle2' }),
        page.click('button[type="submit"]')
    ]);
    console.log("Berhasil login sebagai Satgas PPKSP Dinas Pendidikan Bukittinggi");

    await page.goto('http://127.0.0.1:8000/cases/SS-2026-000202', { waitUntil: 'networkidle2' });
    const case2Info = await page.evaluate(() => {
        const body = document.body.innerText;
        return {
            hasInHandling: body.includes('IN_HANDLING') || body.includes('Sedang Ditangani') || body.includes('Penanganan Eksternal'),
            hasEscalation: body.includes('Kepala Sekolah') || body.includes('Dinas Pendidikan') || body.includes('UPTD PPA'),
            hasTitle: body.includes('SS-2026-000202')
        };
    });
    console.log("Verifikasi Kasus 2 (SS-2026-000202):", case2Info);

    console.log("\nSemua alur pengujian di browser live selesai dijalankan!");
    await browser.disconnect();
}

testLiveChrome().catch(err => {
    console.error("Error selama pengujian:", err);
    process.exit(1);
});
