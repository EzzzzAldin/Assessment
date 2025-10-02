📌 Task Manager API

<p align="center"> <a href="https://laravel.com" target="_blank"> <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"> </a> </p> <p align="center"> <img src="https://img.shields.io/badge/Laravel-11.x-red" alt="Laravel Version"> <img src="https://img.shields.io/badge/API-RESTful-blue" alt="API Type"> <img src="https://img.shields.io/badge/License-MIT-green" alt="License"> </p>

🚀 About the Project

Task Manager API is a RESTful backend built with Laravel 11 that allows users to:

Register, login, and manage authentication via tokens.

Create, update, and delete tasks.

Assign tasks to users.

Filter tasks by status and priority.

Get detailed task information (creator, assignee, timestamps).

This API powers the Task Manager Frontend (React).

📋 API Endpoints

🔑 Authentication

| Method | Endpoint        | Description                    |
| ------ | --------------- | ------------------------------ |
| POST   | `/api/login`    | Login user and get token       |
| POST   | `/api/register` | Register new user              |
| POST   | `/api/logout`   | Logout user (invalidate token) |

📌 Tasks
| Method | Endpoint | Description | Body Params |
| ------ | ----------------- | ---------------------- | ---------------------------------------------------------------------------------- |
| GET | `/api/tasks` | Get all tasks | — |
| POST | `/api/tasks` | Create new task | `title`, `description`, `due_date`, `priority`, `assignee_email` |
| GET | `/api/tasks/{id}` | Get task details by ID | — |
| PUT | `/api/tasks/{id}` | Update task | Any of: `title`, `description`, `due_date`, `priority`, `status`, `assignee_email` |
| DELETE | `/api/tasks/{id}` | Delete a task | — |

🏷️ Task Status Values

done → Completed

due_today → Due Today

missed → Overdue

upcoming → Upcoming

🎯 Task Priority Values

high

medium

low

🔐 Authentication

All task endpoints require authentication via Bearer Token:

Authorization: Bearer YOUR_ACCESS_TOKEN

POST /api/tasks
Content-Type: application/json
Authorization: Bearer TOKEN

📦 Example Request

Create Task
{
"title": "Prepare report",
"description": "Collect numbers from dev team",
"due_date": "2025-09-30",
"priority": "high",
"assignee_email": "ezz@example.com"
}

{
"data": {
"id": 2,
"title": "Prepare report",
"description": "Collect numbers from dev team",
"due_date": "2025-09-30",
"priority": "high",
"status": "due_today",
"creator": { "id": 1, "name": "Test User" },
"assignee": { "id": 3, "name": "Ezz Aldin" },
"created_at": "2025-09-30 09:07:01",
"updated_at": "2025-09-30 09:07:01"
}
}

🛠️ Installation

Clone the repository
git clone https://github.com/EzzzzAldin/Assessment.git
cd Assessment

composer install
cp .env.example .env
php artisan key:generate
cp .env.example .env
php artisan key:generate
