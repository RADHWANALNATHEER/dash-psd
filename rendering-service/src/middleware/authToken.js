function authToken(req, res, next) {
  const header = req.headers['authorization'] || '';
  const token = header.startsWith('Bearer ') ? header.slice(7) : null;

  if (!token || token !== process.env.RENDER_SERVICE_TOKEN) {
    return res.status(401).json({ message: 'غير مصرح' });
  }

  next();
}

module.exports = authToken;
