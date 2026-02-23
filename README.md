# Glowi — Rhythmic Gymnastics Management Platform

Full-stack web application for managing training schedules, competitions, payments, and achievements for rhythmic gymnastics schools.

---

## 🚀 Overview

Glowi is a role-based web platform that connects parents and coaches in a unified system.

The application provides:

- Real-time access to training and competition schedules
- Payment status tracking
- Achievement management
- Structured parent–coach communication

The project focuses on clean modular architecture, relational database design, and clear UI/UX separation.

---

## 🏗 Architecture Overview

Frontend: HTML5, CSS3, JavaScript  
Backend: PHP 8 (modular architecture)  
Database: MySQL (relational schema)  
Authentication: Session-based login system  
Deployment: Local LAMP environment  

### Database Structure

- `parents` — user accounts  
- `children` — linked via parent_id (1-to-many relationship)  
- `events` — training sessions & competitions  
- `child_event` — many-to-many relation table  
- `payments` — monthly payment tracking  
- `achievements` — uploaded and managed by coaches  

---

## 🔐 Core Functionalities

### Parent Role

- View child profile & group
- Access training & competition calendar
- Track monthly payment status
- Submit private lesson requests
- View coach responses
- View achievements and progress updates

### Coach / Admin Role

- Manage parent & child accounts
- Create and edit calendar events
- Update payment status
- Upload achievements
- Review and respond to requests

Role-based access control ensures that users only access permitted features.

---

## 🧠 Engineering Highlights

- Designed normalized relational database schema
- Implemented parent-child relationship logic (1-to-many)
- Built modular PHP structure (`header.php`, `footer.php`, `db.php`)
- Implemented session-based authentication
- Created structured UI component layout for consistency
- Implemented responsive layout without external frameworks

---

## 🖼 UI Preview

<p align="center">
  <img src="/image/main-glowi.png" width="750"/>
</p>

| Dashboard | Child Profile | Calendar | Achievements |
|------------|----------------|-----------|---------------|
| ![Dashboard](/image/2.png) | ![Child Profile](/image/9.png) | ![Calendar](/image/4.png) | ![Achievements](/image/7.png) |

---

## 🛠 Tech Stack

- PHP 8
- MySQL
- HTML5
- CSS3
- JavaScript
- Figma (UI Design)
- Responsive Layout (mobile + desktop)

---

## 📌 Design Approach

The interface was designed to balance clarity for parents with engaging visual identity for children.

Key principles:
- Modular layout
- Clean card-based structure
- Clear data hierarchy
- Consistent navigation patterns
- Responsive design

---

## 👩‍💻 Author

Irina Safronova  
Mobile & Web Developer  
Toronto, Canada
