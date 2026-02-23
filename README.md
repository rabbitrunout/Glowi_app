# Glowi — Role-Based Gymnastics Management Platform

Full-stack product-style web application with structured data model, role-based dashboards, and clean UI workflows.

Built as a real-world management system for rhythmic gymnastics schools.

---

## 🚀 Product Overview

Glowi connects parents and coaches in a unified platform that manages:

- Training & competition schedules
- Monthly payments tracking
- Achievements & progress updates
- Structured parent–coach requests

The system focuses on clear user flows, state-driven UI, and relational data modeling.

---

## 🎯 Why This Project Matters for React / iOS Roles

Glowi demonstrates transferable skills directly relevant to modern mobile and frontend development:

- UI state handling (loading / empty / success / error)
- Role-based access control (Parents vs Coaches/Admin)
- Structured CRUD workflows
- Data-driven screens backed by relational models
- Authentication & protected routes
- Component-style layout structure
- Responsive design principles

---

## 🏗 Architecture Overview

**Frontend:** HTML5, CSS3, JavaScript  
**Backend:** PHP 8 (modular structure)  
**Database:** MySQL (normalized relational schema)  
**Authentication:** Session-based login  
**Access Control:** Role-based permissions  

---

## 🧠 Engineering Highlights

- Designed normalized relational database schema
- Implemented parent → children 1-to-many relationship
- Created many-to-many relation for events (`child_event`)
- Built structured CRUD logic for:
  - Events
  - Achievements
  - Payments
- Implemented session authentication & protected actions
- Designed reusable layout structure (`header.php`, `footer.php`, `db.php`)
- Structured UI into consistent page-level components
- Ensured responsive layout without external UI frameworks

---

## 🗄 Database Schema

- `parents`
- `children`
- `events`
- `child_event`
- `payments`
- `achievements`

Relational integrity and role separation enforced through backend logic.

---

## 🔄 Core Workflows

### Parent Flow
Login → Dashboard → View child → Track payments → Submit request → View response

### Coach/Admin Flow
Login → Manage users → Add events → Upload achievements → Update payment status

Each workflow maintains consistent navigation and protected state transitions.

---

## 📱 UI Preview

<p align="center">
  <img src="/image/main-glowi.png" width="750"/>
</p>

| Dashboard | Child Profile | Calendar | Achievements |
|------------|----------------|-----------|---------------|
| ![Dashboard](/image/2.png) | ![Child Profile](/image/9.png) | ![Calendar](/image/4.png) | ![Achievements](/image/7.png) |

---

## 🔜 Planned React / iOS Evolution

Future iteration roadmap:

- Refactor UI into reusable React components
- Convert backend into REST API with JSON responses
- Implement client-side state management (React)
- Introduce typed models (TypeScript / Swift structs)
- Replace session auth with JWT-based auth
- Add unit & UI testing
- Migrate to React Native or SwiftUI for mobile experience

This roadmap reflects how the current architecture can scale into a modern mobile-first stack.

---

## 🛠 Tech Stack

- PHP 8
- MySQL
- HTML5
- CSS3
- JavaScript
- Responsive design
- Figma (UI prototyping)

---

## 👩‍💻 Author

Irina Safronova  
Mobile & Frontend Developer  
Toronto, Canada
