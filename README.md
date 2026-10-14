# MLBB Trivia Quiz (PHP + MySQL)

Questions and the leaderboard are stored in a MySQL database that you manage in phpMyAdmin.

## Files

- `index.html`, `style.css`, `script.js`: the page the player sees (includes the top nav bar)
- `api.php`: PHP that reads questions and saves/loads scores (saving needs a logged-in player)
- `auth_api.php`: player register / login / logout
- `auth.php`: shared session and security helpers
- `create_admin.php`: run once to create the first admin, then delete it
- `admin/`: the admin panel (questions, users and scores CRUD)
- `config.php`: database login (XAMPP defaults already set)
- `database.sql`: creates the database, tables and 22 starter questions
- `images/`: your pictures (front page image and question images)

## Run it on your computer (XAMPP)

1. Install XAMPP (apachefriends.org) and start **Apache** and **MySQL** in its control panel.
2. Copy this whole folder into XAMPP's `htdocs` folder (for example `C:\xampp\htdocs\mlbb-quiz-php`).
3. Open http://localhost/phpmyadmin, click the **Import** tab, choose `database.sql`, and click **Import**. Do this only once, or the questions will be added twice.
4. Open http://localhost/mlbb-quiz-php/ and play.

If you imported an older `database.sql` before images or accounts existed, don't import again. Open the `mlbb_quiz` database in phpMyAdmin, go to the **SQL** tab and run these one at a time:

```sql
ALTER TABLE questions ADD COLUMN image VARCHAR(255) NULL;
CREATE TABLE users (id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(20) NOT NULL UNIQUE, password_hash VARCHAR(255) NOT NULL, role ENUM('player','admin') NOT NULL DEFAULT 'player', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
ALTER TABLE scores ADD COLUMN user_id INT NULL, ADD CONSTRAINT fk_scores_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;
```

## Accounts and admin panel

Anyone can play, but players must **sign up / log in** to save a score. The leaderboard name is their username.

1. Make sure the `users` table exists (a fresh `database.sql` import creates it; for an older database run the SQL above).
2. Open http://localhost/mlbb-quiz-php/create_admin.php, create your admin account, then **delete `create_admin.php`**.
3. Log in at http://localhost/mlbb-quiz-php/admin/ . From there you can add, edit and delete questions (with picture upload), manage users (roles, password resets, delete) and delete scores.

Passwords are stored hashed. On a live site use HTTPS, and delete `create_admin.php` and `database.sql` from the server.

## Images

Create a folder called `images` next to `index.html` and put your pictures in it. The `images/.htaccess` file stops PHP files from ever running in that folder.

- **Front page image:** save it as `images/front.png`. To use another name, change the `src` of `#hero-img` in `index.html`. If the file is missing, the image simply doesn't show.
- **Question images:** upload them in the admin panel (Questions → Edit), or save the picture in `images/` yourself and type its file name in the question's `image` field.

Use pictures you own or have permission to use.

## Add or edit questions

Easiest: use the admin panel (Questions). Or in phpMyAdmin: database `mlbb_quiz` → table `questions` → **Insert** tab. Fill in the category, the question, the four options, and `answer`, which is the position of the correct option counting from 0 (0 = option_a, 1 = option_b, 2 = option_c, 3 = option_d). Optionally fill `image` with a file name from the `images` folder.

## Put it online

Vercel cannot run PHP, so use a free PHP host that includes MySQL and phpMyAdmin (for example InfinityFree):

1. In the host's control panel, create a MySQL database and note the host name, database name, user and password.
2. Open phpMyAdmin from the panel, select your new database, and import `database.sql`. If the import complains about `CREATE DATABASE` or `USE`, delete those two lines from the file first, because shared hosts create the database for you.
3. Edit `config.php` with the host's database details.
4. Upload all the files, including the `admin` and `images` folders, to the host's `htdocs` (or `public_html`) folder.
5. Create your admin with `create_admin.php`, then delete that file.

## Known limits

- The score is sent from the browser, so a determined player could fake one. Fine for a fun quiz; a stricter version would check answers on the server.
- There is no "forgot password" flow. An admin can set a new password for any user in the admin panel.
- Login attempts are only slowed down, not locked out after many failures.
