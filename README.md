Build a mini API for Task Management.
Requirements
Create MySQL table tasks:
id (PK, auto increment)
title (varchar 150, required)
description (text, nullable)
status (enum/string: todo, in_progress, done; default todo)
due_date (datetime, nullable)
created_at (datetime, required)
updated_at (datetime, required)
Implement with Symfony + Doctrine:
Entity + Migration
Repository method(s) for filtering
Endpoints:
POST /api/tasks
Input: title, description?, status?, due_date?
Validate:
title required, min 3 chars
status must be one of allowed values
due_date must be future datetime if provided
Return 201 with created task JSON
GET /api/tasks?status=&due_before=
Filters optional
Sort by due_date ASC, then created_at DESC
Return list JSON
PATCH /api/tasks/{id}/status
Input: status
Update only status + updated_at
Return 200 updated object, 404 if not found, 422 invalid status
Error handling
Consistent JSON error format: { "errors": { "field": ["message"] } }
Code quality expectations
Thin controllers
Use service layer for business logic
Use DTO or request model for validation (bonus)
No raw SQL in controllers
Deliverables
Source code
Migration file
Short README:
setup steps
run commands
3 example curl requests
