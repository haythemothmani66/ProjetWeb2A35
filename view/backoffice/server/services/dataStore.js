const fs = require('fs/promises');
const path = require('path');

const DB_FILE = path.join(__dirname, '..', 'data', 'database.json');
const EMPTY_DB = {
  users: [],
  profiles: [],
};

class DataStore {
  static async ensureDb() {
    try {
      await fs.access(DB_FILE);
    } catch (error) {
      await fs.mkdir(path.dirname(DB_FILE), { recursive: true });
      await fs.writeFile(DB_FILE, JSON.stringify(EMPTY_DB, null, 2), 'utf-8');
    }
  }

  static async read() {
    await DataStore.ensureDb();

    const raw = await fs.readFile(DB_FILE, 'utf-8');

    try {
      const parsed = JSON.parse(raw);

      return {
        users: Array.isArray(parsed.users) ? parsed.users : [],
        profiles: Array.isArray(parsed.profiles) ? parsed.profiles : [],
      };
    } catch (error) {
      return { ...EMPTY_DB };
    }
  }

  static async write(nextData) {
    await DataStore.ensureDb();

    const safeData = {
      users: Array.isArray(nextData.users) ? nextData.users : [],
      profiles: Array.isArray(nextData.profiles) ? nextData.profiles : [],
    };

    await fs.writeFile(DB_FILE, JSON.stringify(safeData, null, 2), 'utf-8');

    return safeData;
  }
}

module.exports = DataStore;
