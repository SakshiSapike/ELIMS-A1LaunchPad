# 🔌 ELIMS – Electronics Lab Inventory Management System

A full-stack web-based application designed to manage, track, and visualize inventory for electronics R&D and manufacturing laboratories.

> 💼 Submission for the **A-1 Launchpad Software Challenge**

---

## 🌐 Live Preview (optional)
> Add your Vercel/Netlify or Render link here if deployed  
> Example: [Visit Live](https://elims.vercel.app/)

---

## 🚀 Features

- 📦 **Component Management**  
  Add, view, edit, delete electronic components.

- 🔍 **Search & Filter**  
  Filter components by name, category, or part number.

- 📈 **Dashboard Visualizations**  
  Monthly Inward/Outward summaries using Chart.js.

- ⚠️ **Low Stock & Idle Alerts**  
  Automated detection of critical low stock and components not used for 3+ months.

- 🧑‍💼 **User Roles**  
  - **Admin**: Full access (CRUD + User Management)
  - **Technician**: Can log inward/outward
  - **Researcher**: View-only access

---

## 🧱 Tech Stack

| Layer       | Technology           |
|-------------|----------------------|
| Frontend    | HTML, CSS, Bootstrap, JavaScript |
| Backend     | Node.js, Express     |
| Database    | MongoDB (Mongoose)   |
| Charts      | Chart.js             |
| Hosting     | GitHub + Vercel / Render (optional) |

---

## 📁 Project Structure

ELIMS-A1LaunchPad/
├── backend/
│ ├── server.js
│ ├── models/
│ ├── routes/
│ └── middleware/
├── frontend/
│ ├── index.html
│ └── dashboard.html
└── README.md
