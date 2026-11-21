<<<<<<< HEAD
# NagaraLive
NagaraLive is a island wide real-time neighborhood intelligence and community coordination platform divided to each district that brings Events (tailored for Elderly, Children, and Youth), Public Notices, Weather Emergencies, and Power/Water Cutout updates into one clean feed.
To prevent misinformation and unauthorized tampering, any resident an post a new note or verify an alert, while public users cannot edit or delete existing posts-only Admins have full moderation rights to edit, resolve, or delete entries.
Al & Bonus Architecture:
* Google Gemini API (gemini-3.8-flash): Automatically translates
Sinhala/Singlish notes into clear English and generates a live Community Situation Brief summarizing active hazards and neighborhood events.
* cuCompanion ( cli.php): Allows low-bandwidth terminal queries ( php cli.php status /php cli.php brief) alongside the web dashboard.
Al Disclosure: Google Gemini was used for development assistance, and the Gemini API (gemini-3.8-fLash) runs live inside gemini php for translation and briefing generation. Google Cloud Project Number: [599221554770]
=======
# NagaraLive — Real-Time Neighborhood Community Pulse & Alerts

**NagaraLive** is a production-grade, real-time neighborhood intelligence and citizen coordination platform designed for Sri Lankan communities (showcasing the Gampaha District: Gampaha Town, Yakkala, Miriswatta, Ganemulla, Ja-Ela). It unifies Power/Water outages, Weather Emergencies, Public Notices, and Community Events (tailored for Elderly, Children, and Youth) into an accessible, tamper-resistant live pulse feed.

---

## 🌟 Key Highlights & Architecture

- **Public Append-Only Policy:** Any neighborhood resident can post a new note and tap **"+1 Confirm"** on existing reports without needing an account. To eliminate vandalism, public residents cannot edit or delete any post.
- **Admin Full Moderation:** A 1-click **Admin Moderation Mode** (passcode-protected, default: `admin123`) unlocks inline post editing, status resolution toggling, and deletion.
- **Google Gemini 3.8 Flash (`gemini-3.8-flash`):**
  - **`analyzeReport()`:** Real-time translation of local citizen reports in Sinhala, Singlish, Tamil, or English into concise English, while automatically flagging direct eyewitness accounts.
  - **`generateSituationBrief()`:** Synthesizes the 15 most recent active neighborhood reports into an executive community situation brief with automated hazard level evaluation (`Normal`, `Caution`, or `Critical`).
  - **Resilient Fallback:** Includes smart offline rule-based fallbacks so the application functions seamlessly even without an API key or when offline.
- **Dual-Surface Interface:**
  - **Web Dashboard (`index.php`):** Modern dark-mode (`bg-slate-950`) UI styled with Tailwind CSS, animated live radar indicators, category filter tabs, and conditional age-group pills.
  - **CLI Companion (`cli.php`):** High-speed, terminal-native command-line interface for low-bandwidth queries, brief synthesis, and direct alert creation.

---

## 🛠️ Tech Stack

| Layer | Technologies |
|---|---|
| **Backend** | PHP 8.2+ (Native PDO, Strict Typing, cURL, RESTful JSON) |
| **Database** | MySQL 8.0+ / MariaDB (`utf8mb4_unicode_ci`) |
| **AI Engine** | Google Gemini API (`gemini-3.8-flash:generateContent`) |
| **Frontend** | Vanilla JavaScript (ES6+ `fetch` API), Tailwind CSS (CDN), Responsive Dark Mode |
| **Terminal CLI** | Standalone PHP CLI (`cli.php`) with ANSI terminal formatting |

---

## 📁 Project & File Structure

```text
NagaraLive/
├── schema.sql         # MySQL database schema (nagaralive) with 6 realistic Sri Lankan seed rows
├── .gitignore         # Prevents committing .env and vendor directories
├── .env.example       # Template environment variables (DB host, port, credentials, Gemini key)
├── .env               # Active local environment configuration
├── db.php             # Lightweight .env loader and singleton PDO MySQL connection
├── gemini.php         # Gemini 3.8 Flash integration (analyzeReport & generateSituationBrief)
├── api.php            # JSON REST backend handling listing, posting, +1 confirm, AI brief, admin
├── index.php          # Single-page dark-mode web dashboard
├── cli.php            # Standalone terminal CLI companion tool
├── LICENSE            # Full MIT License text
└── README.md          # Comprehensive documentation, setup guide, and guild contributions
```

---

## 📊 Database Schema (`schema.sql`)

The database `nagaralive` contains the `posts` table:

```sql
CREATE TABLE `posts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `area` VARCHAR(100) NOT NULL,
  `category` ENUM('EVENT', 'NOTICE', 'WEATHER_EMERGENCY', 'POWER_WATER') NOT NULL,
  `target_group` ENUM('ALL', 'ELDERLY', 'CHILDREN', 'YOUTH') DEFAULT 'ALL',
  `raw_message` TEXT NOT NULL,
  `translated_message` TEXT,
  `is_eyewitness` TINYINT(1) DEFAULT 1,
  `confirmations` INT DEFAULT 1,
  `status` ENUM('ACTIVE', 'RESOLVED') DEFAULT 'ACTIVE',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Realistic Seed Data Included:
1. **`POWER_WATER` (Yakkala):** Transformer explosion at Yakkala junction with CEB breakdown crew alert (Singlish/English).
2. **`WEATHER_EMERGENCY` (Miriswatta):** Large tree collapse blocking Colombo-Kandy road with flash flood risks (Sinhala).
3. **`NOTICE` (Gampaha Town):** MOH Gampaha urgent Dengue fogging campaign advisory for Wards 4 & 5.
4. **`EVENT` - `ELDERLY` (Ganemulla):** Free comprehensive eye screening clinic and reading glasses distribution at Ganemulla Community Hall.
5. **`EVENT` - `CHILDREN` (Ja-Ela):** Kids Art & Drawing workshop and interactive Puppet Show at Ja-Ela Town Hall.
6. **`EVENT` - `YOUTH` (Gampaha Town):** Youth 3x3 Street Basketball Tournament and Open Coding Meetup at Urban Council Grounds.

---

## 🚀 Setup & Installation Guide

### Prerequisites
- **XAMPP / WAMP / LAMP** or standalone **PHP 8.2+** with `pdo_mysql` and `curl` extensions enabled.
- **MySQL 8.0+** or MariaDB server.

### Step 1: Clone or Open Workspace
Place the project inside your web server directory (e.g., `C:\xampp\htdocs\NagaraLive`).

### Step 2: Import Database Schema
Open phpMyAdmin or use the MySQL terminal to import `schema.sql`:

```bash
# Via MySQL command line:
mysql -u root -p < schema.sql
```
*Or open `http://localhost/phpmyadmin`, create database `nagaralive`, and import `schema.sql`.*

### Step 3: Configure `.env`
Copy `.env.example` to `.env` (or edit the existing `.env`):

```ini
DB_HOST=localhost
DB_PORT=3306
DB_NAME=nagaralive
DB_USER=root
DB_PASS=

# Google Gemini API Key (gemini-3.8-flash)
# Get a key at: https://aistudio.google.com/app/apikey
GEMINI_API_KEY=your_gemini_api_key_here

# Administrator Passcode for Moderation Mode
ADMIN_PASSCODE=admin123
```

> **Note:** If `GEMINI_API_KEY` is not set or offline, NagaraLive automatically uses its built-in rule-based fallback system without crashing.

### Step 4: Run the Web Dashboard
- **Via Apache (XAMPP):** Start Apache in XAMPP Control Panel and navigate to:
  ```text
  http://localhost/NagaraLive/
  ```
- **Via PHP Built-in Server (Alternative):**
  ```bash
  php -S localhost:8000
  ```
  Open `http://localhost:8000` in your web browser.

---

## 💻 CLI Companion Usage (`cli.php`)

NagaraLive includes a standalone command-line tool interacting with the same MySQL database and Gemini models.

### 1. View Latest Community Status
```bash
php cli.php status
```
*Outputs the 8 most recent posts with verification tags, category badges, and bilingual messages.*

### 2. Generate Live Gemini Situation Brief
```bash
php cli.php brief
```
*Fetches active reports, sends them to `gemini-3.8-flash`, and prints the executive Hazard Level (`Normal`, `Caution`, or `Critical`) with summary bullet points.*

### 3. Post a Citizen Alert / Event
```bash
# Post a Power & Water outage:
php cli.php post "Transformer exploded near petrol shed, power cut" "Yakkala" POWER_WATER

# Post a Community Event for Elderly:
php cli.php post "Free cataract screening clinic for elders at community hall" "Ganemulla" EVENT ELDERLY

# Post a Weather Emergency:
php cli.php post "Flash flood covering Kandy road near Miriswatta junction" "Miriswatta" WEATHER_EMERGENCY
```

### 4. Display Help & Command Manual
```bash
php cli.php help
```

---

## 🛡️ Permission & Moderation Model

- **Resident Mode (Default):**
  - **Submit Reports:** Any citizen can submit new notes with automatic AI translation.
  - **Verify (+1):** Any citizen can confirm an alert. When a post reaches **3 confirmations**, it automatically gains the **`✅ Community Verified`** badge.
  - **No Tampering:** Edit, Resolve, and Delete controls are completely hidden and rejected server-side (HTTP 403) for non-admin requests.
- **Administrator Mode:**
  - Click **"Resident Mode"** in the top navigation bar and enter the admin passcode (`admin123`).
  - Unlocks inline **Edit**, **Mark Resolved / Reactivate**, and **Delete** actions on every card.

---

## 🤖 AI Usage Disclosure

Google Gemini (`gemini-3.8-flash`) is utilized live inside `gemini.php` via direct Google AI REST APIs (`https://generativelanguage.googleapis.com/v1beta/models/gemini-3.8-flash:generateContent`):
1. **Report Analysis (`analyzeReport`):** Translates Sinhala, Singlish, Tamil, or casual English into standard English and classifies whether the content represents an eyewitness observation.
2. **Situational Intelligence Briefing (`generateSituationBrief`):** Aggregates neighborhood reports to evaluate hazard severity and provide executive situational summaries.
3. **Resilient Design:** If network connectivity to Gemini is interrupted or the API key is not yet configured, the system uses rule-based fallbacks to ensure continuous uptime.

---

## 👥 Guild Member Contributions

This section recognizes project contributors and team guild members:

| Member Name | Role / Focus Area | Contributions |
|---|---|---|
| **Dinindu-dotcom** | Project Lead & Full-Stack Architect | Core architecture, UI design, and database schema |
| *Guild Member 1* | *Backend & API Engineering* | *Database optimization and API endpoint hardening* |
| *Guild Member 2* | *AI & NLP Specialist* | *Gemini prompt tuning and Sinhala/Tamil evaluation* |
| *Guild Member 3* | *QA & Community Testing* | *Field testing in Gampaha District neighborhoods* |

---

## 📄 License

This project is licensed under the **MIT License** — see the [LICENSE](LICENSE) file for complete details.
>>>>>>> 188f956 (Update application entry point and documentation)
