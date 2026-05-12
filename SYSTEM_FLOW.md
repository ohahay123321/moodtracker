# MoodTrail — System Flow Description

## 1. Architecture Overview

```
┌─────────────────────────────────────────────────────────┐
│                    Client Browser                        │
│  (HTML/CSS/JS - Vanilla, Chart.js for visualizations)   │
└──────────┬──────────────┬────────────────┬──────────────┘
           │              │                │
           ▼              ▼                ▼
    ┌──────────┐   ┌──────────┐   ┌──────────────┐
    │  Pages   │   │   AJAX   │   │  Static      │
    │ (PHP)    │   │  (JSON)  │   │  Assets      │
    └────┬─────┘   └────┬─────┘   │  (CSS/JS)    │
         │              │         └──────────────┘
         ▼              ▼
┌──────────────────────────────────────────┐
│            PHP Backend (Apache)           │
│  config.php | mailer.php | api/*.php     │
└─────────────────┬────────────────────────┘
                  │ (PDO)
                  ▼
┌──────────────────────────────────────────┐
│          MySQL Database (XAMPP)           │
│   Tables: users, mood_entries            │
└──────────────────────────────────────────┘
```

---

## 2. Page Navigation Flow (User Journeys)

### 2.1 Authentication Flow

```
                      ┌─────────────┐
                      │   index.php  │
                      │  (redirect)  │
                      └──────┬──────┘
                             │
              ┌──────────────┴──────────────┐
              │  isLoggedIn()?               │
              ▼                              ▼
       ┌─────────────┐             ┌─────────────┐
       │ dashboard   │             │  landing    │
       │  .php       │             │  .php       │
       └─────────────┘             └──────┬──────┘
                                          │
                    ┌─────────────────────┼─────────────────────┐
                    ▼                     ▼                     ▼
            ┌────────────┐       ┌──────────────┐      ┌───────────────┐
            │ login.php  │       │ register.php │      │ forgot-pass-  │
            │            │       │              │      │ word.php      │
            └──────┬─────┘       └──────┬───────┘      └──────┬────────┘
                   │ POST               │ POST                │ POST
                   ▼                    ▼                     ▼
            ┌────────────┐       ┌──────────────┐      ┌───────────────┐
            │ api/auth   │       │ api/auth     │      │ api/auth      │
            │ .php       │       │ .php         │      │ .php         │
            │ action=    │       │ action=      │      │ action=      │
            │ login      │       │ register     │      │ forgot_      │
            └──────┬─────┘       └──────┬───────┘      │ password     │
                   │                    │              └──────┬────────┘
                   ▼                    ▼                     │
            ┌────────────┐       ┌──────────────┐            │
            │ dashboard  │       │ verify-email │            │
            │ .php       │       │ .php         │            │
            └────────────┘       └──────┬───────┘            │
                                        │ email link          │
                                        ▼                     │
                                 ┌────────────┐              │
                                 │ verify.php │              │
                                 │ ?token=xxx │              │
                                 └──────┬─────┘              │
                                        │                    │
                                        ▼                    │
                                 ┌────────────┐              │
                                 │ login.php  │              │
                                 │ (success)  │              │
                                 └────────────┘              │
                                                            │
                                        ┌────────────────────┘
                                        ▼
                                 ┌──────────────┐
                                 │ reset-pass-  │
                                 │ word.php     │
                                 │ ?token=xxx   │
                                 └──────┬───────┘
                                        │ POST action=reset_password
                                        ▼
                                 ┌──────────────┐
                                 │  login.php   │
                                 │  (success)   │
                                 └──────────────┘
```

### 2.2 Authenticated User Flow (Post-Login)

```
                    ┌─────────────────┐
                    │  dashboard.php  │
                    │  (landing page  │
                    │   after login)  │
                    └────────┬────────┘
                             │
     ┌───────────────────────┼───────────────────────┐
     │                       │                       │
     ▼                       ▼                       ▼
┌────────────┐     ┌──────────────┐           ┌──────────────┐
│ add-mood   │     │  history.php │           │ reports      │
│ .php       │     │  (calendar)  │           │ .php         │
└──────┬─────┘     └──────────────┘           └──────┬───────┘
       │ AJAX POST                                   │
       ▼                                             │
┌────────────┐                             ┌─────────┴─────────┐
│ api/mood   │                             │                   │
│ .php       │                   ┌──────────────┐   ┌──────────────┐
│ action=    │                   │  reports     │   │  profile     │
│ add_mood   │                   │  .php        │   │  .php        │
└──────┬─────┘                   └──────┬───────┘   └──────┬───────┘
       │ redirect                CSV export │              │ AJAX
       ▼                         ?export=csv              ▼
┌────────────┐                             ┌──────────────┐
│ dashboard  │                             │  api/user    │
│ .php       │                             │  .php        │
└────────────┘                             └──────────────┘
```

---

## 3. Authentication Data Flow

### 3.1 Registration

```
register.php                    api/auth.php                    MySQL              Gmail SMTP
    │                               │                           │                    │
    │ POST (name,email,             │                           │                    │
    │  password,confirm_password)   │                           │                    │
    ├──────────────────────────────>│                           │                    │
    │                               │                           │                    │
    │                               │ Validate fields           │                    │
    │                               │ (all required,            │                    │
    │                               │  password >= 8,           │                    │
    │                               │  passwords match,         │                    │
    │                               │  email format)            │                    │
    │                               │                           │                    │
    │                               │ SELECT email FROM users   │                    │
    │                               │ WHERE email = ?           │                    │
    │                               ├──────────────────────────>│                    │
    │                               │ ◄── (exists or not) ─────┤                    │
    │                               │                           │                    │
    │                               │ Generate:                 │                    │
    │                               │  - password_hash()        │                    │
    │                               │  - verification_token =   │                    │
    │                               │    bin2hex(random_bytes)  │                    │
    │                               │                           │                    │
    │                               │ INSERT INTO users         │                    │
    │                               │ (name,email,password,     │                    │
    │                               │  verification_token)      │                    │
    │                               ├──────────────────────────>│                    │
    │                               │ ◄── (insert success) ────┤                    │
    │                               │                           │                    │
    │                               │ getMailer()->send()       │                    │
    │                               │ (verification link)       │                    │
    │                               ├──────────────────────────────────────────────>│
    │                               │                           │                    │
    │ ◄── redirect(verify-email.php)│                           │                    │
    │                               │                           │                    │
```

### 3.2 Login

```
login.php                       api/auth.php                    MySQL
    │                               │                           │
    │ POST (email,password)         │                           │
    ├──────────────────────────────>│                           │
    │                               │                           │
    │                               │ SELECT * FROM users       │
    │                               │ WHERE email = ?           │
    │                               ├──────────────────────────>│
    │                               │ ◄── (user row) ──────────┤
    │                               │                           │
    │                               │ password_verify(          │
    │                               │   password, hash)         │
    │                               │                           │
    │                               │ Check email_verified == 1 │
    │                               │                           │
    │                               │ Set $_SESSION:            │
    │                               │  - user_id                │
    │                               │  - user_name              │
    │                               │  - user_email             │
    │                               │                           │
    │ ◄── redirect(dashboard.php) ──┤                           │
    │                               │                           │
```

### 3.3 Email Verification

```
User Email                   verify.php                       MySQL
    │                           │                              │
    │ click link with           │                              │
    │ ?token=xxx                │                              │
    ├──────────────────────────>│                              │
    │                           │                              │
    │                           │ SELECT * FROM users          │
    │                           │ WHERE verification_token = ? │
    │                           ├─────────────────────────────>│
    │                           │ ◄── (user row or null) ─────┤
    │                           │                              │
    │                           │ UPDATE users SET             │
    │                           │  email_verified = 1,         │
    │                           │  verification_token = NULL   │
    │                           │ WHERE id = ?                 │
    │                           ├─────────────────────────────>│
    │                           │                              │
    │ ◄── redirect(login.php    │                              │
    │      ?success=verified)───┤                              │
```

### 3.4 Password Reset

```
forgot-password.php          api/auth.php                    MySQL              Gmail SMTP
    │                            │                              │                    │
    │ POST (email)               │                              │                    │
    ├───────────────────────────>│                              │                    │
    │                            │                              │                    │
    │                            │ SELECT * FROM users          │                    │
    │                            │ WHERE email = ?              │                    │
    │                            ├─────────────────────────────>│                    │
    │                            │ ◄── (user row or null) ─────┤                    │
    │                            │                              │                    │
    │                            │ Generate reset_token (64 hex)│                    │
    │                            │ reset_token_expires = NOW+24h│                    │
    │                            │                              │                    │
    │                            │ UPDATE users SET             │                    │
    │                            │  reset_token=?,              │                    │
    │                            │  reset_token_expires=?       │                    │
    │                            │ WHERE id = ?                 │                    │
    │                            ├─────────────────────────────>│                    │
    │                            │                              │                    │
    │                            │ getMailer()->send()          │                    │
    │                            │ (reset link)                 │                    │
    │                            ├──────────────────────────────────────────────────>│
    │                            │                              │                    │
    │ ◄── redirect(             │                              │                    │
    │   forgot-password.php     │                              │                    │
    │   ?success=check_email)───┤                              │                    │
```

```
User Email                reset-password.php                  MySQL
    │                            │                              │
    │ click link with            │                              │
    │ ?token=xxx                 │                              │
    ├───────────────────────────>│                              │
    │                            │ SELECT * FROM users          │
    │                            │ WHERE reset_token = ?        │
    │                            │ AND reset_token_expires > NOW│
    │                            ├─────────────────────────────>│
    │                            │ ◄── (user row or null) ─────┤
    │                            │                              │
    │                            │ Show password form           │
    │                            │                              │
    │ POST (password,            │                              │
    │  confirm_password,         │                              │
    │  action=reset_password)    │                              │
    ├───────────────────────────>│                              │
    │                            │ Validate password >= 8,      │
    │                            │ passwords match              │
    │                            │                              │
    │                            │ UPDATE users SET             │
    │                            │  password = ?,               │
    │                            │  reset_token = NULL,         │
    │                            │  reset_token_expires = NULL  │
    │                            │ WHERE id = ?                 │
    │                            ├─────────────────────────────>│
    │                            │                              │
    │ ◄── redirect(login.php     │                              │
    │      ?success=reset)───────┤                              │
```

---

## 4. Mood Entry Data Flow

```
add-mood.php                   api/mood.php                    MySQL
    │                               │                           │
    │ (page load)                   │                           │
    │ requireLogin()                │                           │
    │                               │                           │
    │ User selects:                 │                           │
    │  - Mood (emoji card)          │                           │
    │  - Intensity (slider 1-10)    │                           │
    │  - Notes (optional)           │                           │
    │  - Date/Time (custom)         │                           │
    │                               │                           │
    │ AJAX POST                     │                           │
    │ (mood,intensity,notes,        │                           │
    │  mood_date,action=add_mood)   │                           │
    ├──────────────────────────────>│                           │
    │                               │                           │
    │                               │ Validate mood in          │
    │                               │ ["Happy","Sad","Angry",   │
    │                               │  "Calm","Anxious",        │
    │                               │  "Excited","Tired",       │
    │                               │  "Loved"]                 │
    │                               │                           │
    │                               │ Validate intensity 1-10   │
    │                               │                           │
    │                               │ INSERT INTO mood_entries  │
    │                               │ (user_id, mood, intensity,│
    │                               │  notes, created_at)       │
    │                               ├──────────────────────────>│
    │                               │ ◄── (insert success) ────┤
    │                               │                           │
    │ ◄── {success: true,           │                           │
    │       message: "Mood logged"}─┤                           │
    │                               │                           │
    │ Show toast notification       │                           │
    │ redirect(dashboard.php)       │                           │
```

---

## 5. Reports Data Flow (with CSV Export)

```
reports.php                                           MySQL
    │                                                    │
    │ requireLogin()                                     │
    │ Read ?range=(7|30|90|365|custom)                   │
    │ Read ?from= and ?to= (custom range)                │
    │                                                    │
    │ Calculate date range                               │
    │                                                    │
    │ ── Queries: stats & aggregation ──                  │
    │                                                    │
    │ 1. Total entries, avg intensity, positive %         │
    │ 2. Current streak calculation:                     │
    │    (query consecutive days from latest)            │
    │ 3. All entries for daily log table                  │
    │                                                    │
    │ ┌── If ?export=csv ──────────────────────┐         │
    │ │ SELECT mood, intensity, notes,         │         │
    │ │  created_at FROM mood_entries          │         │
    │ │ WHERE user_id=? AND created_at BETWEEN │         │
    │ │ ? AND ? ORDER BY created_at DESC       │         │
    │ ├───────────────────────────────────────>│         │
    │ │ ◄── all_entries ──────────────────────┤         │
    │ │                                        │         │
    │ │ Set headers:                           │         │
    │ │  Content-Type: text/csv               │         │
    │ │  Content-Disposition: attachment;      │         │
    │ │   filename="mood_report.csv"          │         │
    │ │                                        │         │
    │ │ Output CSV with BOM:                   │         │
    │ │  Date,Mood,Intensity,Notes,Time        │         │
    │ │  2024-01-15,Happy,8,Great day!,14:30   │         │
    │ └────────────────────────────────────────┘         │
```

---

## 6. Profile Management Data Flow

```
profile.php                  api/user.php                   MySQL
    │                            │                           │
    │ requireLogin()             │                           │
    │                            │                           │
    │ ── Update Profile ──       │                           │
    │ AJAX POST                   │                           │
    │ (name,email,               │                           │
    │  action=update_profile)    │                           │
    ├───────────────────────────>│                           │
    │                            │ Validate name, email      │
    │                            │ Check email uniqueness    │
    │                            │                           │
    │                            │ UPDATE users SET          │
    │                            │  name=?, email=?          │
    │                            │ WHERE id=?                │
    │                            ├──────────────────────────>│
    │                            │                           │
    │                            │ Update $_SESSION vars     │
    │ ◄── {success: true} ──────┤                           │
    │                            │                           │
    │ ── Change Password ──      │                           │
    │ AJAX POST                   │                           │
    │ (current_password,         │                           │
    │  new_password,             │                           │
    │  confirm_password,         │                           │
    │  action=change_password)   │                           │
    ├───────────────────────────>│                           │
    │                            │ SELECT password FROM users│
    │                            │ WHERE id=?                │
    │                            ├──────────────────────────>│
    │                            │ ◄── (current hash) ──────┤
    │                            │                           │
    │                            │ password_verify(          │
    │                            │  current_password, hash)  │
    │                            │ Validate new >= 8 chars,  │
    │                            │ new == confirm            │
    │                            │                           │
    │                            │ UPDATE users SET          │
    │                            │  password = new_hash      │
    │                            │ WHERE id=?                │
    │                            ├──────────────────────────>│
    │ ◄── {success: true} ──────┤                           │
    │                            │                           │
    │ ── Update Reminders ──     │                           │
    │ AJAX POST                   │                           │
    │ (enabled=0|1, time=HH:MM, │                           │
    │  action=update_reminders)  │                           │
    ├───────────────────────────>│                           │
    │                            │ UPDATE users SET          │
    │                            │  reminder_enabled=?,      │
    │                            │  reminder_time=?          │
    │                            │ WHERE id=?                │
    │                            ├──────────────────────────>│
    │ ◄── {success: true} ──────┤                           │
    │                            │                           │
    │ ── Delete Account ──       │                           │
    │ AJAX POST                   │                           │
    │ (action=delete_account)    │                           │
    ├───────────────────────────>│                           │
    │                            │ DELETE FROM mood_entries  │
    │                            │ WHERE user_id=?           │
    │                            ├──────────────────────────>│
    │                            │                           │
    │                            │ DELETE FROM users         │
    │                            │ WHERE id=?                │
    │                            ├──────────────────────────>│
    │                            │                           │
    │                            │ session_destroy()         │
    │ ◄── {success: true,       │                           │
    │       redirect: login} ────┤                           │
```

---

## 7. Database Relationship Diagram

```
┌──────────────────────────────────────────────┐
│                  users                        │
├──────────────────────────────────────────────┤
│  id                  INT (PK, AUTO_INCREMENT) │────┐
│  name                VARCHAR(255)             │    │
│  email               VARCHAR(255) (UNIQUE)    │    │
│  password            VARCHAR(255)             │    │
│  email_verified      TINYINT(1) DEFAULT 0    │    │
│  verification_token  VARCHAR(64) (NULLABLE)   │    │
│  reset_token         VARCHAR(64) (NULLABLE)   │    │
│  reset_token_expires DATETIME (NULLABLE)      │    │
│  reminder_enabled    TINYINT(1) DEFAULT 1    │    │
│  reminder_time       TIME DEFAULT '21:00:00'  │    │
│  created_at          DATETIME                 │    │
└──────────────────────────────────────────────┘    │
                                                    │ 1
                                                    │
                                                    │ N
┌──────────────────────────────────────────────┐    │
│               mood_entries                    │    │
├──────────────────────────────────────────────┤    │
│  id                  INT (PK, AUTO_INCREMENT) │    │
│  user_id             INT (FK -> users.id)     │<───┘
│  mood                VARCHAR(50)              │
│  intensity           INT (1-10)               │
│  notes               TEXT (NULLABLE)          │
│  created_at          DATETIME                 │
└──────────────────────────────────────────────┘
```

---

## 8. Session State Machine

```
┌──────────┐
│  PUBLIC  │
│ (no ses- │
│  sion)   │
└────┬─────┘
     │ User registers / logs in
     ▼
┌──────────┐     POST api/auth.php
│ LOGGED   │─────────────────────> Set session:
│  OUT     │                       user_id
└────┬─────┘                       user_name
     │ Login                       user_email
     ▼                        ┌──────────┐
┌──────────┐   session        │ LOGGED   │
│ LOGGED   │<── vars set ────┤   IN     │
│  IN      │                  └──────────┘
└────┬─────┘                       │
     │ User clicks Logout          │
     ├─────────────────────────────┤
     │                             │
     ▼                             ▼
┌──────────┐               ┌──────────────┐
│ LOGGED   │               │  SESSION     │
│  OUT     │               │  DESTROYED   │
└──────────┘               └──────────────┘
     │                           │
     └── redirect(login.php) <───┘
```

### Session Variables
| Variable | Set By | Used By |
|---|---|---|
| `user_id` | api/auth.php (login) | All authenticated pages |
| `user_name` | api/auth.php (login) | Header/nav display |
| `user_email` | api/auth.php (login) | Profile page |

### Auth Guard (`requireLogin()`)
```
request
  │
  ▼
┌────────────────┐ NO    ┌──────────────┐
│ $_SESSION[     │──────>│ redirect to  │
│ 'user_id']     │       │ login.php    │
│ exists?        │       └──────────────┘
└───────┬────────┘
        │ YES
        ▼
  ┌────────────┐
  │ Continue   │
  │ to page    │
  └────────────┘
```

---

## 9. API Endpoint Summary

### api/auth.php (Redirect-based responses)
```
POST /api/auth.php
  action=login          -> redirect to dashboard.php (success) or login.php (error)
  action=register       -> redirect to verify-email.php (success) or register.php (error)
  action=forgot_password -> redirect to forgot-password.php with message
```

### api/mood.php (JSON responses)
```
POST /api/mood.php   (requires login)
  action=add_mood    -> { success: true/false, message: "..." }
  action=delete_mood -> { success: true/false, message: "..." }

GET /api/mood.php    (requires login)
  ?limit=10&offset=0 -> { success: true, data: [...] }
```

### api/user.php (JSON responses)
```
POST /api/user.php   (requires login)
  action=update_profile   -> { success: true/false, message: "..." }
  action=change_password  -> { success: true/false, message: "..." }
  action=update_reminders -> { success: true/false, message: "..." }
  action=delete_account   -> { success: true/false, message: "..." }
```

### api/logout.php
```
GET or POST /api/logout.php -> session_destroy() -> redirect to login.php
```
