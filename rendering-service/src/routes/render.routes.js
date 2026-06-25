const express = require('express');
const path = require('path');
const { renderDesign, OUTPUT_DIR } = require('../controllers/render.controller');

const router = express.Router();

router.post('/render', renderDesign);

// تنزيل الملف الناتج مباشرة (يستخدمه Laravel لجلب الصورة بعد إنتاجها)
router.get('/files/:fileName', (req, res) => {
  const fileName = path.basename(req.params.fileName);
  const filePath = path.join(OUTPUT_DIR, fileName);

  if (!filePath.startsWith(OUTPUT_DIR)) {
    return res.status(400).json({ message: 'اسم ملف غير صالح' });
  }

  res.sendFile(filePath, (err) => {
    if (err) res.status(404).json({ message: 'الملف غير موجود' });
  });
});

module.exports = router;
