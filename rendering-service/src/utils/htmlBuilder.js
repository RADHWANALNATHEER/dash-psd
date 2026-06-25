/**
 * يبني صفحة HTML/CSS كاملة من وصف القالب (خلفية + طبقات نصية/صورية)
 * الإدخال المتوقع (payload.template):
 * {
 *   width: 1080, height: 1080,
 *   background_url: "https://.../bg.png",
 *   layers: [
 *     { type: "text", key: "title", x, y, width, font_family, font_size, font_weight,
 *       color, align, line_height, letter_spacing, text_shadow, direction },
 *     { type: "image", key: "photo", x, y, width, height, border_radius, object_fit }
 *   ]
 * }
 * والإدخال payload.values: { title: "...", photo: "https://.../uploaded.jpg" }
 */

function escapeHtml(str = '') {
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

function buildTextLayerStyle(layer) {
  const styles = [
    `position:absolute`,
    `left:${layer.x}px`,
    `top:${layer.y}px`,
    `width:${layer.width}px`,
    `font-family:'${layer.font_family || 'Cairo'}', sans-serif`,
    `font-size:${layer.font_size || 24}px`,
    `font-weight:${layer.font_weight || 400}`,
    `color:${layer.color || '#000000'}`,
    `text-align:${layer.align || 'right'}`,
    `direction:${layer.direction || 'rtl'}`,
    `line-height:${layer.line_height || 1.3}`,
    `letter-spacing:${layer.letter_spacing || 0}px`,
    `white-space:pre-wrap`,
    `word-wrap:break-word`,
  ];

  if (layer.text_shadow) {
    styles.push(`text-shadow:${layer.text_shadow}`);
  }

  return styles.join(';');
}

function buildImageLayerStyle(layer) {
  const styles = [
    `position:absolute`,
    `left:${layer.x}px`,
    `top:${layer.y}px`,
    `width:${layer.width}px`,
    `height:${layer.height}px`,
    `object-fit:${layer.object_fit || 'cover'}`,
    `border-radius:${layer.border_radius || 0}px`,
    `overflow:hidden`,
  ];

  return styles.join(';');
}

function buildLayerHtml(layer, values) {
  const value = values[layer.key];

  if (layer.type === 'text') {
    const text = value !== undefined && value !== null ? value : (layer.placeholder || '');
    return `<div style="${buildTextLayerStyle(layer)}">${escapeHtml(text).replace(/\n/g, '<br>')}</div>`;
  }

  if (layer.type === 'image') {
    const src = value || layer.placeholder_url;
    if (!src) return '';
    return `<img src="${src}" style="${buildImageLayerStyle(layer)}" />`;
  }

  return '';
}

function buildHtml(payload) {
  const { template, values = {} } = payload;
  const { width, height, background_url, layers = [] } = template;

  const layersHtml = layers.map((layer) => buildLayerHtml(layer, values)).join('\n');
  const fontsBaseUrl = `file://${require('path').resolve(__dirname, '../../public/fonts')}`;

  return `<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <style>
    @font-face {
      font-family: 'Cairo';
      src: url('${fontsBaseUrl}/Cairo-Regular.ttf') format('truetype');
      font-weight: 200 1000;
    }
    @font-face {
      font-family: 'Tajawal';
      src: url('${fontsBaseUrl}/Tajawal-Regular.ttf') format('truetype');
      font-weight: 400;
    }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    html, body { width: ${width}px; height: ${height}px; overflow: hidden; }
    .canvas {
      position: relative;
      width: ${width}px;
      height: ${height}px;
      background-image: url('${background_url}');
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
    }
  </style>
</head>
<body>
  <div class="canvas">
    ${layersHtml}
  </div>
</body>
</html>`;
}

module.exports = { buildHtml };
