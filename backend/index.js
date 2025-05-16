require('dotenv').config();
const express = require('express');
const mysql = require('mysql2');
const app = express();
const PORT = process.env.PORT || 3000;

const db = mysql.createConnection({
  host: process.env.MYSQL_HOST,
  user: process.env.MYSQL_USER,
  password: process.env.MYSQL_PASSWORD,
  database: process.env.MYSQL_DATABASE
});

app.get('/api/test', (req, res) => {
  db.query('SELECT 1 + 1 AS result', (err, result) => {
    if (err) return res.status(500).json(err);
    res.json(result);
  });
});

app.listen(PORT, () => {
  console.log(`Servidor backend corriendo en http://localhost:${PORT}`);
});
