# MLBB Trivia Quiz (PHP + MySQL)

Questions and the leaderboard are stored in a MySQL database that you manage in phpMyAdmin.

## Files

- `index.html`, `style.css`, `script.js`: the page the player sees
- `api.php`: PHP that reads questions and saves/loads scores
- `config.php`: database login (XAMPP defaults already set)
- `database.sql`: creates the database, tables and 22 starter questions

## Run it on your computer (XAMPP)

1. Install XAMPP (apachefriends.org) and start **Apache** and **MySQL** in its control panel.
2. Copy this whole folder into XAMPP's `htdocs` folder (for example `C:\xampp\htdocs\mlbb-quiz-php`).
3. Open http://localhost/phpmyadmin, click the **Import** tab, choose `database.sql`, and click **Import**. Do this only once, or the questions will be added twice.
4. Open http://localhost/mlbb-quiz-php/ and play.

## Add or edit questions

In phpMyAdmin: database `mlbb_quiz` → table `questions` → **Insert** tab. Fill in the category, the question, the four options, and `answer`, which is the position of the correct option counting from 0 (0 = option_a, 1 = option_b, 2 = option_c, 3 = option_d).

Player scores are in the `scores` table. You can delete rows there if someone enters a silly name.

## Put it online

Vercel cannot run PHP, so use a free PHP host that includes MySQL and phpMyAdmin (for example InfinityFree):

1. In the host's control panel, create a MySQL database and note the host name, database name, user and password.
2. Open phpMyAdmin from the panel, select your new database, and import `database.sql`. If the import complains about `CREATE DATABASE` or `USE`, delete those two lines from the file first, because shared hosts create the database for you.
3. Edit `config.php` with the host's database details.
4. Upload all the files to the host's `htdocs` (or `public_html`) folder.

## Known limits

- The score is sent from the browser, so a determined player could fake one. Fine for a fun quiz; a stricter version would check answers on the server.
