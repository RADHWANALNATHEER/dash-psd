const { v4: uuidv4 } = require('uuid');
const path = require('path');
const fs = require('fs');
const { getBrowser } = require('../utils/browserPool');
const { buildHtml } = require('../utils/htmlBuilder');

const OUTPUT_DIR = path.resolve(process.env.OUTPUT_DIR || 'storage/output');

if (!fs.existsSync(OUTPUT_DIR)) {
  fs.mkdirSync(OUTPUT_DIR, { recursive: true });
}

async function renderDesign(req, res) {
  const payload = req.body;
  const { template, format = 'png', quality = 90 } = payload;

  if (!template || !template.width || !template.height || !template.background_url) {
    return res.status(422).json({ message: 'بيانات القالب غير مكتملة (width, height, background_url مطلوبة)' });
  }

  if (!['png', 'jpeg', 'jpg'].includes(format)) {
    return res.status(422).json({ message: 'الصيغة المطلوبة غير مدعومة، استخدم png أو jpeg' });
  }

  const html = buildHtml(payload);
  const browser = await getBrowser();
  const page = await browser.newPage();

  try {
    await page.setViewport({
      width: template.width,
      height: template.height,
      deviceScaleFactor: payload.scale || 2,
    });

    await page.setContent(html, { waitUntil: 'networkidle0' });

    const ext = format === 'jpg' ? 'jpeg' : format;
    const fileName = `${uuidv4()}.${ext === 'jpeg' ? 'jpg' : 'png'}`;
    const filePath = path.join(OUTPUT_DIR, fileName);

    const screenshotOptions = {
      path: filePath,
      type: ext,
      clip: { x: 0, y: 0, width: template.width, height: template.height },
    };

    if (ext === 'jpeg') {
      screenshotOptions.quality = quality;
    }

    await page.screenshot(screenshotOptions);

    res.json({
      success: true,
      file_name: fileName,
      file_path: filePath,
      width: template.width,
      height: template.height,
    });
  } catch (error) {
    res.status(500).json({ message: 'فشل إنتاج الصورة', error: error.message });
  } finally {
    await page.close();
  }
}

module.exports = { renderDesign, OUTPUT_DIR };
