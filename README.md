# GH-timeline

A PHP-based email subscription and verification system that allows users to register using their email, receive GitHub timeline updates every 5 minutes, and optionally unsubscribe. This project demonstrates email verification, CRON job scheduling, and email formatting — all built using pure PHP and file-based storage.

---

## 🚀 Features

### ✅ Email Verification System
- Users register using an email form.
- A 6-digit verification code is emailed to them.
- Users enter the code to confirm and register.
- Verified emails are stored in `registered_emails.txt`.

### ✅ GitHub Timeline Updates
- A CRON job runs every 5 minutes to fetch GitHub timeline data.
- The data is converted to HTML and emailed to all verified users.
- Each email includes an unsubscribe link.

### ✅ Unsubscribe Feature
- Clicking the unsubscribe link takes the user to an unsubscribe page.
- Users verify again via code to confirm unsubscription.
- Their email is then removed from the file-based registry.

### ✅ Pure PHP
- No external libraries or frameworks used.
- No database; uses a plain text file for email storage.

---

## 🧱 Folder Structure

```bash
src/
├── index.php                # Email submission and verification
├── unsubscribe.php          # Unsubscription via email + code
├── functions.php            # Core helper functions
├── cron.php                 # Sends GitHub timeline updates
├── setup_cron.sh            # Shell script to install CRON job
├── registered_emails.txt    # Email list (file-based DB)
⚙️ Setup Instructions
1. Clone the Repository
bash
Copy
Edit
git clone https://github.com/your-username/GH-timeline.git
cd GH-timeline/src/
2. Run PHP Server Locally
bash
Copy
Edit
php -S localhost:8000
3. Configure the CRON Job
Make the shell script executable:

bash
Copy
Edit
chmod +x setup_cron.sh
./setup_cron.sh
This will register a CRON job that runs every 5 minutes and executes cron.php to email all users.

4. Email Testing
You can use a local SMTP server such as Mailpit or MailHog for testing email functionality.

📩 Email Formats
🔐 Verification Email
Subject: Your Verification Code

Body:

html
Copy
Edit
<p>Your verification code is: <strong>123456</strong></p>
From: no-reply@example.com

📥 GitHub Timeline Email
Subject: Latest GitHub Updates

Body:

html
Copy
Edit
<h2>GitHub Timeline Updates</h2>
<table border="1">
  <tr><th>Event</th><th>User</th></tr>
  <tr><td>Push</td><td>testuser</td></tr>
</table>
<p><a href="unsubscribe_url" id="unsubscribe-button">Unsubscribe</a></p>
❌ Unsubscribe Confirmation Email
Subject: Confirm Unsubscription

Body:

html
Copy
Edit
<p>To confirm unsubscription, use this code: <strong>654321</strong></p>
❗ Guidelines Followed
 All features implemented inside src/ folder

 Verification and unsubscription codes generated dynamically

 No third-party libraries used

 CRON job automated via setup_cron.sh

 Emails formatted using HTML

 Used PHP mail() function

 Form elements always visible as required

📎 Important Notes
⛔ No hardcoded codes or emails

⛔ No use of database

⛔ No editing outside src/ folder

⛔ No renaming function stubs

✅ All forms follow required HTML structure

✅ Pull request includes screenshots and test results

🧪 Sample Screenshots
(Include images or videos demonstrating each step — email registration, verification, update email, and unsubscription.)

👨‍💻 Author
Made with ❤️ by Marthala Nithin Reddy
🔗 GitHub: Nithinreddy3093
📧 Email: marthalanithinreddy3093@gmail.com
🎓 B.Tech CSE (Cybersecurity), SRMIST – 2026

📜 License
This project is for evaluation/demo purposes only. All rights reserved by Kalvium.

vbnet
Copy
Edit

Let me know if you'd like this in a downloadable .md file or need help embedding screenshots and video links.









Ask ChatGPT
