# 🌟 Glowi App — Rhythmic Gymnastics Portal  

<p align="center">
  <img src="/image/main-glowi.png" width="800" alt="Glowi App Dashboard Preview"/>
</p>

> 🩰 A modern web platform for parents of young gymnasts — combining beauty, structure, and simplicity.  

---

## ✨ Introduction  

**Glowi** is an intuitive web platform that connects parents and the rhythmic gymnastics school in one unified system.  
It provides parents with quick and easy access to:
- their child’s training schedule,  
- competition calendar,  
- payment history,  
- and a showcase of achievements (medals, diplomas, and progress).

The app was designed to reflect the **grace and glow** of gymnastics — using a **neon color palette**, **soft gradients**, and **responsive animations**.

---

## 🧩 Features  

### 👨‍👩‍👧 For Parents
- 🖼️ **Child profile** — photo, age, and group  
- 📅 **Training & competition calendar**  
- 🏅 **Achievements** — medals, diplomas, and ratings  
- 💸 **Monthly payments** — status tracking (paid/unpaid)  
- 📝 **Request private lessons** directly from the dashboard  
- 💬 **View coach feedback** on requests  

### 🧑‍🏫 For Coaches & Administrators
- 👥 Manage users and children profiles  
- 🗓️ Add and edit calendar events  
- 🏆 Upload achievements and update progress  
- 💳 Change payment status per child  
- 📬 Review and respond to parent requests  

---

## 🖼️ UI Preview  

| Dashboard | Child Profile | Calendar | Achievements |
|------------|----------------|-----------|---------------|
| ![Dashboard](/image/2.png) | ![Child Profile](/image/9.png) | ![Calendar](/image/4.png) | ![Achievements](/image/7.png) |

---

## 🧠 Architecture  

The project follows a modular PHP structure and a clean database design with relational tables:  

- **parents** – account data  
- **children** – linked via parent ID  
- **events** – training & competitions  
- **child_event** – relation between children and events  
- **payments** – monthly payment status  
- **achievements** – uploaded by coaches  

---

## 🛠️ Tech Stack  

- **Frontend:** HTML5, CSS3, JavaScript  
- **Backend:** PHP 8, MySQL  
- **Database:** `students_directory`  
- **Design:** Figma + Neon/Glassmorphism UI  
- **Frameworks:** none (pure PHP modular architecture)  
- **Responsive:** Fully optimized for mobile and desktop  

---

## 🎨 Design Style  

> A balance between **modern elegance for parents** and **playful energy for kids**  

- Neon gradients (💜 pink → 💙 blue → 💜 violet)  
- Rounded cards with glass effect  
- Soft shadows, glowing borders  
- Smooth hover and button animations  
- Unified layout for all pages (`header.php`, `footer.php`, `db.php`)  

---

## 🚀 Installation  

1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/Glowi_app.git
