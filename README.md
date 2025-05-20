# HR Dashboard System

## Overview
HR Dashboard is a comprehensive web-based Human Resources Management System designed to streamline employee management, time tracking, and leave management processes. The system provides a user-friendly interface for HR personnel to efficiently manage employee data, work schedules, and leave requests.

## Features

### Employee Management
- Add, edit, and delete employee records
- View employee details including contact information
- Filter and search employees by name or department
- Track employees' leave status

### Time Management
- Interactive calendar for scheduling
- Record and track employee work hours
- Support for different work statuses (present, leave, vacation)
- Daily, weekly, and monthly views of employee schedules
- Summary statistics of working hours and attendance

### Leave Management
- Process leave requests
- Approve or reject leave applications
- Filter leave requests by status
- Track leave history

### User Interface
- Responsive design that works on various devices
- Light and dark theme options
- Intuitive navigation
- Real-time notifications for actions

## Technologies Used
- Frontend: HTML5, CSS3, JavaScript (vanilla)
- Backend: PHP
- Database: MySQL
- Server: XAMPP

## Installation

### Prerequisites
- XAMPP (or equivalent PHP development environment)
- MySQL
- Web browser

### Setup Steps
1. Clone or download the repository to your XAMPP's htdocs folder
```
git clone https://github.com/yourusername/HRDashboard.git /Applications/XAMPP/xamppfiles/htdocs/HRDashboard
```

2. Start XAMPP and ensure Apache and MySQL services are running

3. Create a database named `HRDASHBOARD` in MySQL

4. Import the database schema (located in the `database` folder)

5. Access the system through your web browser
```
http://localhost/HRDashboard/
```

6. Login using the default credentials:
   - Username: admin
   - Password: admin123

## Project Structure

```
HRDashboard/
├── css/                  # Stylesheet files
│   ├── common.css        # Shared styles
│   ├── dashboard.css     # Dashboard specific styles
│   ├── employees.css     # Employee management styles
│   ├── forms-modals.css  # Form and modal styles
│   ├── leave.css         # Leave management styles
│   ├── style.css         # Login page styles
│   └── time_management.css # Time management styles
├── js/                   # JavaScript files
│   ├── calendar.js       # Calendar and scheduling functionality
│   ├── common.js         # Shared functionality
│   ├── dashboard.js      # Dashboard specific functionality
│   ├── employees.js      # Employee management functionality
│   └── leave.js          # Leave management functionality
├── php/                  # PHP backend files
│   ├── add_employee.php  # Add new employee
│   ├── delete_employee.php # Delete employee
│   ├── edit_employee.php # Edit employee details
│   ├── get_employees.php # Retrieve employee data
│   ├── login.php         # Authentication
│   ├── logout.php        # Log out functionality
│   ├── save_employee.php # Save employee data
│   ├── update_employee.php # Update employee data
│   └── ... (other PHP files)
├── database/             # Database files
│   └── HRDASHBOARD.sql   # Database schema
├── screenshots/          # System screenshots for documentation
├── dashboard.php         # Main dashboard page
├── login.html            # Login page
├── manage_employees.php  # Employee management page
├── manage_leave.php      # Leave management page
├── manage_time.php       # Time management page
└── README.md             # This documentation file
```

## Usage

### Login
1. Access the login page at `http://localhost/HRDashboard/login.html`
2. Enter your username and password
3. Click "Zaloguj się" (Login)

### Managing Employees
1. Navigate to "Zarządzaj pracownikami" (Manage employees)
2. Use filters to search for specific employees
3. Add new employees using the "Dodaj pracownika" button
4. Edit or delete employees using the action buttons

### Managing Time
1. Navigate to "Zarządzaj czasem pracy" (Manage work time)
2. Use the calendar to select specific dates
3. View, add, edit, or delete schedules for employees
4. Review summary statistics at the bottom of the page

### Managing Leave Requests
1. Navigate to "Wnioski urlopowe" (Leave requests)
2. View all leave requests with their statuses
3. Approve or reject pending leave requests
4. Filter requests by status or date

## Customization
- Toggle between light and dark themes using the sun/moon icon in the header
- Modify CSS files to change the appearance
- Edit PHP files to adjust functionality

## Credits
- Font Awesome for icons
- Random User API for demo avatar images
