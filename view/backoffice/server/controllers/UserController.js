const bcrypt = require('bcryptjs');
const UserModel = require('../models/User');
const ProfileModel = require('../models/Profile');
const {
  validateCreateUserPayload,
  validateUpdateUserPayload,
} = require('../middleware/userValidation');

function toPublicUser(user, profile) {
  const safeUser = UserModel.sanitize(user);
  const safeProfile = profile || {};
  const fullName = `${safeProfile.nom || ''} ${safeProfile.prenom || ''}`.trim();

  return {
    ...safeUser,
    nom: safeProfile.nom || '',
    prenom: safeProfile.prenom || '',
    telephone: safeProfile.telephone || '',
    photo: safeProfile.photo || '',
    name: fullName || 'N/A',
  };
}

function buildStats(users) {
  const totalUsers = users.length;
  const activeUsers = users.filter((item) => item.status === 'active').length;
  const pendingUsers = users.filter((item) => item.status === 'pending').length;
  const suspendedUsers = users.filter((item) => item.status === 'suspended').length;
  const pendingTeacherValidations = users.filter(
    (item) => item.role === 'teacher' && item.status === 'pending'
  ).length;

  return {
    totalUsers,
    activeUsers,
    pendingUsers,
    suspendedUsers,
    pendingTeacherValidations,
  };
}

function parseUserId(req, res) {
  const userId = Number(req.params.id);

  if (!Number.isInteger(userId) || userId <= 0) {
    res.status(400).json({ message: 'User id must be a positive integer.' });
    return null;
  }

  return userId;
}

const UserController = {
  async listUsers(req, res) {
    try {
      const search = String(req.query.search || '').toLowerCase().trim();
      const role = String(req.query.role || '').toLowerCase().trim();
      const status = String(req.query.status || '').toLowerCase().trim();

      const users = await UserModel.findAll();
      const profiles = await ProfileModel.findAll();
      const profileByUserId = new Map(profiles.map((profile) => [profile.user_id, profile]));

      const merged = users.map((user) => toPublicUser(user, profileByUserId.get(user.id)));

      const filtered = merged.filter((item) => {
        const matchesSearch =
          !search ||
          item.email.toLowerCase().includes(search) ||
          item.nom.toLowerCase().includes(search) ||
          item.prenom.toLowerCase().includes(search) ||
          item.name.toLowerCase().includes(search);

        const matchesRole = !role || item.role === role;
        const matchesStatus = !status || item.status === status;

        return matchesSearch && matchesRole && matchesStatus;
      });

      return res.json({
        data: filtered,
        stats: buildStats(users),
      });
    } catch (error) {
      return res.status(500).json({ message: 'Failed to fetch users.' });
    }
  },

  async getUserById(req, res) {
    try {
      const userId = parseUserId(req, res);

      if (!userId) {
        return;
      }

      const user = await UserModel.findById(userId);

      if (!user) {
        return res.status(404).json({ message: 'User not found.' });
      }

      const profile = await ProfileModel.findByUserId(userId);

      return res.json({
        data: toPublicUser(user, profile),
      });
    } catch (error) {
      return res.status(500).json({ message: 'Failed to fetch user details.' });
    }
  },

  async createUser(req, res) {
    try {
      const { cleaned, errors } = validateCreateUserPayload(req.body);

      if (Object.keys(errors).length) {
        return res.status(400).json({
          message: 'Validation failed.',
          errors,
        });
      }

      const existingUser = await UserModel.findByEmail(cleaned.email);

      if (existingUser) {
        return res.status(409).json({
          message: 'A user with this email already exists.',
          errors: {
            email: 'Email already exists.',
          },
        });
      }

      const hashedPassword = await bcrypt.hash(cleaned.password, 10);

      const createdUser = await UserModel.create({
        email: cleaned.email,
        password: hashedPassword,
        role: cleaned.role,
        status: cleaned.status,
        created_at: new Date().toISOString(),
      });

      const createdProfile = await ProfileModel.create({
        user_id: createdUser.id,
        nom: cleaned.nom,
        prenom: cleaned.prenom,
        telephone: cleaned.telephone,
        photo: cleaned.photo,
      });

      return res.status(201).json({
        message: 'User created successfully.',
        data: toPublicUser(createdUser, createdProfile),
      });
    } catch (error) {
      return res.status(500).json({ message: 'Failed to create user.' });
    }
  },

  async updateUser(req, res) {
    try {
      const userId = parseUserId(req, res);

      if (!userId) {
        return;
      }

      const existingUser = await UserModel.findById(userId);

      if (!existingUser) {
        return res.status(404).json({ message: 'User not found.' });
      }

      const { cleaned, errors } = validateUpdateUserPayload(req.body);

      if (Object.keys(errors).length) {
        return res.status(400).json({
          message: 'Validation failed.',
          errors,
        });
      }

      if (cleaned.email && cleaned.email !== existingUser.email.toLowerCase()) {
        const duplicate = await UserModel.findByEmail(cleaned.email);

        if (duplicate && duplicate.id !== userId) {
          return res.status(409).json({
            message: 'A user with this email already exists.',
            errors: {
              email: 'Email already exists.',
            },
          });
        }
      }

      const userUpdates = {};

      if (cleaned.email) {
        userUpdates.email = cleaned.email;
      }

      if (cleaned.role) {
        userUpdates.role = cleaned.role;
      }

      if (cleaned.status) {
        userUpdates.status = cleaned.status;
      }

      if (cleaned.password) {
        userUpdates.password = await bcrypt.hash(cleaned.password, 10);
      }

      if (Object.keys(userUpdates).length) {
        await UserModel.update(userId, userUpdates);
      }

      const profileUpdates = {};

      ['nom', 'prenom', 'telephone', 'photo'].forEach((field) => {
        if (Object.prototype.hasOwnProperty.call(cleaned, field)) {
          profileUpdates[field] = cleaned[field];
        }
      });

      if (Object.keys(profileUpdates).length) {
        await ProfileModel.updateByUserId(userId, profileUpdates);
      }

      const updatedUser = await UserModel.findById(userId);
      const updatedProfile = await ProfileModel.findByUserId(userId);

      return res.json({
        message: 'User updated successfully.',
        data: toPublicUser(updatedUser, updatedProfile),
      });
    } catch (error) {
      return res.status(500).json({ message: 'Failed to update user.' });
    }
  },

  async deleteUser(req, res) {
    try {
      const userId = parseUserId(req, res);

      if (!userId) {
        return;
      }

      const deleted = await UserModel.remove(userId);

      if (!deleted) {
        return res.status(404).json({ message: 'User not found.' });
      }

      await ProfileModel.deleteByUserId(userId);

      return res.json({
        message: 'User deleted successfully.',
      });
    } catch (error) {
      return res.status(500).json({ message: 'Failed to delete user.' });
    }
  },

  async activateUser(req, res) {
    try {
      const userId = parseUserId(req, res);

      if (!userId) {
        return;
      }

      const user = await UserModel.findById(userId);

      if (!user) {
        return res.status(404).json({ message: 'User not found.' });
      }

      const updatedUser = await UserModel.update(userId, { status: 'active' });
      const profile = await ProfileModel.findByUserId(userId);

      return res.json({
        message: 'User activated successfully.',
        data: toPublicUser(updatedUser, profile),
      });
    } catch (error) {
      return res.status(500).json({ message: 'Failed to activate user.' });
    }
  },

  async deactivateUser(req, res) {
    try {
      const userId = parseUserId(req, res);

      if (!userId) {
        return;
      }

      const user = await UserModel.findById(userId);

      if (!user) {
        return res.status(404).json({ message: 'User not found.' });
      }

      const updatedUser = await UserModel.update(userId, { status: 'pending' });
      const profile = await ProfileModel.findByUserId(userId);

      return res.json({
        message: 'User deactivated successfully.',
        data: toPublicUser(updatedUser, profile),
      });
    } catch (error) {
      return res.status(500).json({ message: 'Failed to deactivate user.' });
    }
  },

  async suspendUser(req, res) {
    try {
      const userId = parseUserId(req, res);

      if (!userId) {
        return;
      }

      const user = await UserModel.findById(userId);

      if (!user) {
        return res.status(404).json({ message: 'User not found.' });
      }

      const updatedUser = await UserModel.update(userId, { status: 'suspended' });
      const profile = await ProfileModel.findByUserId(userId);

      return res.json({
        message: 'User suspended successfully.',
        data: toPublicUser(updatedUser, profile),
      });
    } catch (error) {
      return res.status(500).json({ message: 'Failed to suspend user.' });
    }
  },

  async validateTeacher(req, res) {
    try {
      const userId = parseUserId(req, res);

      if (!userId) {
        return;
      }

      const user = await UserModel.findById(userId);

      if (!user) {
        return res.status(404).json({ message: 'User not found.' });
      }

      if (user.role !== 'teacher') {
        return res.status(400).json({ message: 'Only teachers can be validated.' });
      }

      const updatedUser = await UserModel.update(userId, { status: 'active' });
      const profile = await ProfileModel.findByUserId(userId);

      return res.json({
        message: 'Teacher account validated successfully.',
        data: toPublicUser(updatedUser, profile),
      });
    } catch (error) {
      return res.status(500).json({ message: 'Failed to validate teacher account.' });
    }
  },
};

module.exports = UserController;
