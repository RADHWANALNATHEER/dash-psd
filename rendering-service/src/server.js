require('dotenv').config();
const express = require('express');
const cors = require('cors');
const renderRoutes = require('./routes/render.routes');
const authToken = require('./middleware/authToken');
const { closeBrowser } = require('./utils/browserPool');

const app = express();

app.use(cors());
app.use(express.json({ limit: '10mb' }));

app.get('/health', (req, res) => res.json({ status: 'ok' }));

app.use(authToken);
app.use('/', renderRoutes);

const PORT = process.env.PORT || 4000;
const server = app.listen(PORT, () => {
  console.log(`Rendering service running on port ${PORT}`);
});

process.on('SIGTERM', async () => {
  await closeBrowser();
  server.close(() => process.exit(0));
});
