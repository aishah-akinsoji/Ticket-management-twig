# README for Twig Implementation


## Overview
This is the **Twig version** of the Ticket Management System**, built with **PHP** and **Twig templating engine**. 
It focuses on server-side rendering and uses json server for managing authenticated users.


---


## Features
- User authentication using PHP sessions.
- Dashboard displaying ticket statistics.
- Ticket management: add, edit, and delete.
- Twig templates for modular and reusable UI.
- Bootstrap for layout and styling.


---


## Tech Stack
- **PHP** – Backend
- **Twig** – Template engine
- **Bootstrap** – UI Styling
- **FontAwesome** – Icons


---


## 📁 Folder Structure
```
TICKET MANAGEMENT TWIG/
│
├── public/
│ ├── css/
│ ├── js/
│ └── index.php
├── src/
│ templates/
│ ├── base.twig
│ ├── auth.twig
│ ├── dashboard.twig
│ ├── tickets.twig
│ └── navbar.twig
└── composer.json
```


---


## Setup Instructions
1. **Clone the Repository:**
```bash
git clone <[repo-link](https://github.com/aishah-akinsoji/Ticket-management-twig)>
```
2. **Install Dependencies:**
```bash
composer install
```
3. **Run a Local Server:**
```bash
php -S localhost:8000 -t public
```
4. **Access the App:**
Open your browser at [http://localhost:8000](http://localhost:8000)


---


## Configuration
- The Twig environment is initialized in `public/index.php`.


---


## License
This project is open-source and available under the [MIT License](LICENSE).
