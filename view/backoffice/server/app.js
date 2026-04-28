const path = require('path');
const express = require('express');
const cors = require('cors');
const userRoutes = require('./routes/userRoutes');
const DataStore = require('./services/dataStore');

const app = express();
const srcRoot = path.join(__dirname, '..', 'src');

DataStore.ensureDb().catch((error) => {
  console.error('Unable to initialize datastore.', error);
});

app.use(
  cors({
    origin: true,
    methods: ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'],
    allowedHeaders: ['Content-Type', 'x-user-role'],
  })
);

app.use(express.json());
app.use(express.urlencoded({ extended: true }));

app.get('/health', (req, res) => {
  res.json({ status: 'ok' });
});

app.use(userRoutes);
app.use(express.static(srcRoot));

app.get('/', (req, res) => {
  res.sendFile(path.join(srcRoot, 'index.html'));
});

app.use((req, res) => {
  if (req.path.startsWith('/users')) {
    return res.status(404).json({ message: 'Route not found.' });
  }

  return res.status(404).send('Not Found');
});

app.use((error, req, res, next) => {
  console.error('Server error:', error);
  res.status(500).json({ message: 'Unexpected server error.' });
});

module.exports = app;
