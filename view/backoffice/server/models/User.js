const DataStore = require('../services/dataStore');

class UserModel {
  static roles = ['student', 'teacher', 'admin'];

  static statuses = ['active', 'pending', 'suspended'];

  static nextId(users) {
    if (!users.length) {
      return 1;
    }

    return Math.max(...users.map((item) => item.id)) + 1;
  }

  static sanitize(user) {
    if (!user) {
      return null;
    }

    const { password, ...safeUser } = user;
    return safeUser;
  }

  static async findAll() {
    const db = await DataStore.read();
    return db.users;
  }

  static async findById(id) {
    const db = await DataStore.read();
    return db.users.find((item) => item.id === Number(id)) || null;
  }

  static async findByEmail(email) {
    const normalized = String(email || '').toLowerCase().trim();
    const db = await DataStore.read();

    return db.users.find((item) => item.email.toLowerCase() === normalized) || null;
  }

  static async create(payload) {
    const db = await DataStore.read();

    const newUser = {
      id: UserModel.nextId(db.users),
      email: payload.email,
      password: payload.password,
      role: payload.role,
      status: payload.status,
      created_at: payload.created_at || new Date().toISOString(),
    };

    db.users.push(newUser);
    await DataStore.write(db);

    return newUser;
  }

  static async update(id, updates) {
    const db = await DataStore.read();
    const index = db.users.findIndex((item) => item.id === Number(id));

    if (index === -1) {
      return null;
    }

    db.users[index] = {
      ...db.users[index],
      ...updates,
    };

    await DataStore.write(db);

    return db.users[index];
  }

  static async remove(id) {
    const db = await DataStore.read();
    const index = db.users.findIndex((item) => item.id === Number(id));

    if (index === -1) {
      return false;
    }

    db.users.splice(index, 1);
    await DataStore.write(db);

    return true;
  }
}

module.exports = UserModel;
