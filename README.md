Banking Management System — Web Application

A secure and user-friendly web application designed to manage banking operations digitally. The system allows users to manage accounts, perform transactions, check balances, and generate account statements efficiently, with role-based access and secure authentication.

Features

User Account Management: Create, update, and manage user accounts

Secure Authentication & Session Management

Transaction Processing: Deposit, withdraw, and transfer funds

Balance Inquiry: View current account balances

Account Statements: Generate detailed transaction histories

Technology Stack

Backend: PHP

Database: MySQL

Server: Apache (XAMPP recommended)

Frontend: HTML, CSS, PHP

System Architecture

PHP Backend: Handles business logic, authentication, and database queries

MySQL Database: Stores account, transaction, and user information

Apache Server (XAMPP): Serves the application locally

Frontend: Provides a responsive and interactive user interface

Installation & Setup
Clone the repository
git clone https://github.com/mishal-raziq/banking_project.git
cd banking_project

Install dependencies
composer install

Configure Database

Open .env file and update database credentials

Run migrations to create tables:

php artisan migrate

Run the application

Start your local server and access the app at:

http://localhost/banking_project

User Roles
Role	Responsibilities
Admin	Manage users, accounts, transactions, and view reports
User/Customer	View account details, perform transactions, check balance, generate statements
Key Learning Outcomes

PHP and MySQL integration for web applications

Secure user authentication and session management

Transaction handling and account management logic

Connecting front-end with backend dynamically

Implementing a user-friendly banking interface

Author

Mishal Raziq
