const express = require('express');
const UserController = require('../controllers/UserController');
const requireAdmin = require('../middleware/adminMiddleware');

const router = express.Router();

router.use(requireAdmin);

router.get('/users', UserController.listUsers);
router.get('/users/:id', UserController.getUserById);
router.post('/users', UserController.createUser);
router.put('/users/:id', UserController.updateUser);
router.delete('/users/:id', UserController.deleteUser);

router.patch('/users/:id/activate', UserController.activateUser);
router.patch('/users/:id/deactivate', UserController.deactivateUser);
router.patch('/users/:id/suspend', UserController.suspendUser);
router.patch('/users/:id/validate-teacher', UserController.validateTeacher);

module.exports = router;
