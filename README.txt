Description:
   This project is a web-based Patient Appointment Scheduling System developed by our team as part of our Software Development Capstone. The application allows patients to register, log in, and schedule appointments with available providers, while administrators can manage user accounts and appointment data. The system streamlines the scheduling process through an intuitive calendar interface and real-time availability management. Our goal is to reduce administrative workload and increase accessibility to care through digital convenience. This project showcases our collaborative skills in backend and frontend development, database integration, and user interface design.


Installation:
   Follow these steps to set up the Patient Appointment Scheduling System locally.


Prerequisites:
   XAMPP (PHP 8.2+, Apache, MariaDB)


   A web browser (e.g., Chrome, Firefox)


   Git (optional)


1. Download the Project
   Download the repository as a ZIP from GitHub and extract it into your XAMPP htdocs directory:


   C:\xampp\htdocs\capstone-project-runtime_terrors


   Alternatively, clone the repository into that same location using Git if preferred.


2. Start XAMPP
   Open the XAMPP Control Panel and start both Apache and MySQL.


3. Create the Database
   Access phpMyAdmin using one of these methods:
      - Standard path: http://localhost/phpmyadmin
      - If using a custom port: http://localhost:8080/phpmyadmin (common alternative port)
      - Alternatively: Open XAMPP Control Panel → Click "Admin" button next to MySQL

   Create a new database named:
   kholley_appointment_system


   Import the database_setup.sql file provided in the repository using the Import tab in phpMyAdmin to import the database schema and sample data.


4. Configure Database Connection

   For basic installation with default settings, no changes are needed as the system will use these defaults:
      - Host: localhost
      - Username: root
      - Password: (blank)
      - Database: kholley_appointment_system

   If you need to customize these settings, you can:

      Option 1: Create a .env file in the project root with:
      db_host=localhost
      db_user=your_username
      db_pass=your_password
      db_name=your_database_name

      Option 2: Modify the default values in config/connection_bridge.php



Testing
   The Runtime Terrors capstone project includes four main test files to validate your environment setup. 
   The main file, test_env.php, verifies PHP environment settings, required extensions, and database connectivity. 
   The public_html/env_bridge_test.php checks environment detection and database connection through the configuration bridge, 
   while public_html/bootstrap_test.php validates bootstrap loading and path configurations. 
   The public_html/tech_integration_test.php performs comprehensive testing of all system components, verifying that PHP, MySQL, 
   Bootstrap, and FullCalendar.js are properly integrated and functional. 

   To run these tests, ensure your local web server (Apache, Nginx, etc.) is running with the project files in your document root,
   You can access this through http://localhost/[your-project-path]/index.php/auth
   1. Log in as an administrator
   2. Navigate to the Admin Dashboard
   3. Scroll to the bottom of the dashboard page to find the "Test Container" section
   Select and run the desired test from the dropdown menu
   (e.g., test_env.php, env_bridge_test.php, bootstrap_test.php, tech_integration_test.php).

   These tests will display detailed information about your environment configuration and will help identify any issues that need to be 
   addressed before working with the application.
   Check that all tests pass as shown in the following files:
   - ./screenshots/env_bridge_test.png
   - ./screenshots/bootstrap_test.png
   - ./screenshots/TechValidationKholley.png
   - ./screenshots/Integrated_Tech_Test.png
   - ./screenshots/Integrated_Tech_Test2.png
   - ./screenshots/Integrated_Tech_Test3.png

   A successful test will show "PASS" indicators for all components.


Usage
 
   1. Start local server (XAMPP), ensuring Apache and MySQL are running
   2. Open your browser and go to: http://localhost/appointment-system/capstone-project-runtime_terrors/public_html/index.php
   3. Navigate the Website- Three lines on the right of page
      1. Home Page - Welcome message
      2. Login- enroll with email address and password
         1. Get a verification email when registering for account
      3. Appointments- manage appointments
      4. Provider - add availability