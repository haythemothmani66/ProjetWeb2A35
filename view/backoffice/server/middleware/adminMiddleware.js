function requireAdmin(req, res, next) {
  const userRole = String(req.header('x-user-role') || '').toLowerCase().trim();

  if (userRole !== 'admin') {
    return res.status(403).json({
      message: 'Only admin users can access this module.',
    });
  }

  return next();
}

module.exports = requireAdmin;
