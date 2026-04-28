const app = require('./server/app');

const PORT = Number(process.env.PORT) || 4000;

app.listen(PORT, () => {
  console.log(`EduMatch User Management API is running at http://localhost:${PORT}`);
});
