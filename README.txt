Description:
  This project is a web-based Patient Appointment Scheduling System developed by our team as part of our Software Development Capstone. The application allows patients to register, log in, and schedule appointments with available providers, while administrators can manage user accounts and appointment data. The system streamlines the scheduling process through an intuitive calendar interface and real-time availability management. Our goal is to reduce administrative workload and increase accessibility to care through digital convenience. This project showcases our collaborative skills in backend and frontend development, database integration, and user interface design.
 
Installation:
  Follow these steps to set up the Patient Appointment Scheduling System locally.
Prerequisites:
  XAMPP (PHP 8.2+, Apache, MariaDB)
  A web browser (e.g., Chrome, Firefox) 
  Git (optional)
 
1. Download the Project- options below
Download the repository as a ZIP from GitHub and extract it into your XAMPP htdocs directory:
capstone-project-runtime_terrors-main.zip, you will then need to extract to XAMPP/htdocs
 
Alternatively, clone the repository into that same location using Git if preferred.
https://github.com/it-sd-capstone/capstone-project-runtime_terrors.git
clone: git clone https://github.com/it-sd-capstone/capstone-project-runtime_terrors.git
 
 
 
2. Start XAMPP
  Open the XAMPP Control Panel and start both Apache and MySQL.
 
3. Create the Database
   Access phpMyAdmin using one of these methods:
      - Standard path: http://localhost/phpmyadmin
      - If using a custom port: http://localhost:8080/phpmyadmin (common alternative port)
      - Alternatively: Open XAMPP Control Panel → Click "Admin" button next to MySQL

   Create a new database named:
   kholley_appointment_system

   Import the database files in the following order:
   1. First, import the schema structure by selecting the `sql/kholley_appointment_system.sql` file in the Import tab of phpMyAdmin
   2. Then, import the basic sample data by selecting the `sql/kholley_appointment_systemdata.sql` file

   Note: Each file must be imported separately in the specified order to ensure proper database setup. Wait for each import to complete before starting the next one.
 
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
  2. Open your browser and go to: http://localhost/[your-project-path]/index.php/auth
  3. Navigate the Website
Home Page - Welcome message
Login- once you click Login, then choose to create an Account if new user, if returning user, click Login as Patient, Login as Provider or Login as Admin
You will need to enter your information, whether to create an account or login
Appointments- once logged in, will take you to your patient/provider homepage
Patient - see appointments, manage appointments, or book appointments
Provider - Manage Services (what you offer), Manage Availability (set schedule), View Appointments (see appointments), and Edit Profile (Name, Specialty, Phone and Bio)