const DataStore = require('../services/dataStore');

class ProfileModel {
  static nextId(profiles) {
    if (!profiles.length) {
      return 1;
    }

    return Math.max(...profiles.map((item) => item.id)) + 1;
  }

  static async findAll() {
    const db = await DataStore.read();
    return db.profiles;
  }

  static async findByUserId(userId) {
    const db = await DataStore.read();
    return db.profiles.find((item) => item.user_id === Number(userId)) || null;
  }

  static async create(payload) {
    const db = await DataStore.read();

    const newProfile = {
      id: ProfileModel.nextId(db.profiles),
      user_id: Number(payload.user_id),
      nom: payload.nom,
      prenom: payload.prenom,
      telephone: payload.telephone,
      photo: payload.photo || '',
    };

    db.profiles.push(newProfile);
    await DataStore.write(db);

    return newProfile;
  }

  static async updateByUserId(userId, updates) {
    const db = await DataStore.read();
    const index = db.profiles.findIndex((item) => item.user_id === Number(userId));

    if (index === -1) {
      const fallbackProfile = {
        id: ProfileModel.nextId(db.profiles),
        user_id: Number(userId),
        nom: updates.nom || '',
        prenom: updates.prenom || '',
        telephone: updates.telephone || '',
        photo: updates.photo || '',
      };

      db.profiles.push(fallbackProfile);
      await DataStore.write(db);

      return fallbackProfile;
    }

    db.profiles[index] = {
      ...db.profiles[index],
      ...updates,
    };

    await DataStore.write(db);

    return db.profiles[index];
  }

  static async deleteByUserId(userId) {
    const db = await DataStore.read();
    const index = db.profiles.findIndex((item) => item.user_id === Number(userId));

    if (index === -1) {
      return false;
    }

    db.profiles.splice(index, 1);
    await DataStore.write(db);

    return true;
  }
}

module.exports = ProfileModel;
