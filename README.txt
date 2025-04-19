Description:


This project is a web-based Patient Appointment Scheduling System developed by our team as part of our Software Development Capstone. The application allows patients to register, log in, and schedule appointments with available providers, while administrators can manage user accounts and appointment data. The system streamlines the scheduling process through an intuitive calendar interface and real-time availability management. Our goal is to reduce administrative workload and increase accessibility to care through digital convenience. This project showcases our collaborative skills in backend and frontend development, database integration, and user interface design.


Installation:


Follow these steps to set up the Patient Appointment Scheduling System locally.


Prerequisites
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
Go to http://localhost/phpmyadmin
Create a new database named:


appointment_system


If a database.sql file is provided in the repository, use the Import tab in phpMyAdmin to import the database schema and sample data.


If not, manually create the required tables: users, services, and appointments.


4. Configure Database Connection
Open the file: config/database.php
Ensure the following values match your local environment:


DB_HOST = localhost
DB_USER = root
DB_PASS = (leave blank if using default XAMPP)
DB_NAME = appointment_system




Testing
The Runtime Terrors capstone project includes four main test files to validate your environment setup. The main file, test_env.php, verifies PHP environment settings, required extensions, and database connectivity. The public_html/env_bridge_test.php checks environment detection and database connection through the configuration bridge, while public_html/bootstrap_test.php validates bootstrap loading and path configurations. The public_html/tech_integration_test.php performs comprehensive testing of all system components, verifying that PHP, MySQL, Bootstrap, and FullCalendar.js are properly integrated and functional. 
To run these tests, ensure your local web server (Apache, Nginx, etc.) is running with the project files in your document root, then navigate to each test URL in your browser (e.g., http://localhost/test_env.php, http://localhost/public_html/env_bridge_test.php, http://localhost/public_html/bootstrap_test.php, and http://localhost/public_html/tech_integration_test.php). These tests will display detailed information about your environment configuration and will help identify any issues that need to be addressed before working with the application.
Check that all tests pass as shown in the following files:
  - ./screenshots/env_bridge_test.png
  - ./screenshots/bootstrap_test.png
  - ./screenshots/TechValidationKholley.png
  - ./screenshots/Integrated_Tech_Test.png
  - ./screenshots/Integrated_Tech_Test2.png
  - ./screenshots/Integrated_Tech_Test3.png


3. A successful test will show "PASS" indicators for all components.




Usage
 
1. Start local server (XAMPP), ensuring Apache and MySQL are running
2. Open your browser and go to: Appointment-System
3. Navigate the Website- Three lines on the right of page
   1. Home Page - Welcome message
   2. Login- enroll with email address and password
      1. Get a verification email when registering for account
   3. Appointments- manage appointments
   4. Provider - add availability