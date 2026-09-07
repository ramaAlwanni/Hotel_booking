# 🏨 Hotel Booking — Project Context

> This file is the single source of truth for the Hotel Booking project.
> Read it before making architectural or code suggestions.
> Update it when a major feature, decision, or sprint is completed.

---

# 1. Project Overview

A realistic hotel booking platform built with Laravel.

The goal is NOT to build a simple CRUD application.

The goal is to build a realistic, portfolio-ready web application that demonstrates:

- Laravel
- PHP
- MySQL
- Eloquent ORM
- Authentication
- Authorization
- Business logic
- Validation
- Error handling
- Testing
- Notifications
- Queues
- REST API
- Docker
- CI/CD
- Deployment
- Git/GitHub
- JavaScript
- React later

The application should be realistic enough to discuss confidently in a job interview.

IMPORTANT:

Do not add features just to increase the feature count.

Every feature must have a clear business purpose.

---

# 2. Tech Stack

Current:

- Laravel 12
- PHP >= 8.2
- MySQL
- Eloquent ORM
- Spatie Laravel Permission
- Laravel Sanctum
- Blade
- Vite
- JavaScript
- CSS

Planned:

- REST API
- Docker
- Docker Compose
- GitHub Actions
- CI/CD
- Linux
- Nginx
- Deployment
- HTTPS
- Queues
- Notifications
- Redis if needed
- React frontend later

---

# 3. Current Models

## User

Responsibilities:

- Customer
- Hotel Manager
- Admin

Relationships:

- hasMany Hotels
- hasMany Bookings
- hasMany ReinstatementRequests
- hasMany reviewed ReinstatementRequests

Authentication:

- Registration
- Login
- Logout
- Email verification through OTP

---

## Hotel

Current fields:

- id
- user_id
- name
- description
- address
- city
- country
- phone
- email
- is_active
- timestamps

Relationships:

- belongsTo User
- hasMany Rooms
- hasMany Bookings

The User represents the hotel manager/owner.

---

## Room

Current fields:

- id
- hotel_id
- room_number
- type
- price_per_night
- max_occupancy
- is_active
- timestamps

Current room types:

- single
- double
- suite

Relationships:

- belongsTo Hotel
- hasMany Bookings

Existing business method:

`isAvailableForDates($checkIn, $checkOut)`

IMPORTANT:

This method needs review and automated tests.

The current overlap logic may incorrectly reject a new booking when:

Existing booking:
10 → 15

New booking:
15 → 20

These should normally be allowed because checkout and check-in happen on the same date.

---

## Booking

Current fields:

- id
- user_id
- hotel_id
- room_id
- check_in
- check_out
- total_price
- status
- expires_at
- timestamps

Current statuses:

- pending
- confirmed
- cancelled
- expired

Relationships:

- belongsTo User
- belongsTo Hotel
- belongsTo Room
- hasMany ReinstatementRequests

Current business methods:

- nightsCount
- isPayable
- isCancellable

These methods need to be reviewed and tested as the booking lifecycle is implemented.

---

## ReinstatementRequest

Purpose:

Allow a user to request reopening/reinstatement of an expired booking/payment window.

Current fields:

- id
- booking_id
- user_id
- reason
- status
- reviewed_by
- reviewed_at
- timestamps

Statuses:

- pending
- approved
- rejected

Current behavior:

- User creates a reinstatement request.
- Admin/authorized reviewer can approve or reject.
- Approval currently returns the booking to pending.
- Approval currently gives the booking a new payment expiration window.
- Rejection changes request status to rejected.

IMPORTANT:

The business rules around reinstatement must be reviewed before considering this feature complete.

---

# 4. Current Database

Existing tables include:

- users
- hotels
- rooms
- bookings
- reinstatement_requests
- Spatie roles/permissions tables
- cache
- jobs
- personal_access_tokens
- other Laravel framework tables

DO NOT redesign the database from scratch.

Before creating a new table:

1. Check whether the data can belong to an existing model.
2. Decide whether the feature is actually needed.
3. Explain the business reason.
4. Only then create the migration.

---

# 5. Main Business Roles

## Customer

Can:

- register
- login
- verify email
- browse hotels
- search hotels
- filter rooms
- view hotel details
- view room details
- check room availability
- create booking
- view own bookings
- cancel eligible bookings
- receive notifications

---

## Hotel Manager

Can:

- manage own hotel
- manage own rooms
- manage room availability
- view bookings for own hotel
- manage relevant bookings
- view hotel statistics

A manager MUST NOT be able to modify another manager's hotel or rooms.

---

## Admin

Can:

- manage users
- manage hotels
- manage rooms
- manage bookings
- manage permissions
- review reinstatement requests
- view system statistics

---

# 6. Authentication Requirements

Authentication foundation must be completed before major booking features.

Required:

- Registration
- Login
- Logout
- Password hashing
- Email verification
- OTP verification
- Protected routes
- Authentication middleware

## OTP Requirements

The OTP system already exists locally but may not yet be pushed to GitHub.

Required behavior:

- Generate OTP
- Send OTP
- Verify OTP
- Expiration
- Resend OTP
- Invalidate previous OTP
- Limit verification attempts
- Prevent abuse
- Store OTP securely
- Set email_verified_at after successful verification

Do not rebuild OTP from scratch.

First inspect the existing implementation.

---

# 7. Validation

Use Laravel validation appropriately.

Prefer Form Requests for complex request validation.

Important examples:

- required fields
- email format
- unique email
- password confirmation
- date validation
- check-in before check-out
- guest capacity
- valid room
- valid hotel
- valid booking state

Validation errors must be user-friendly.

---

# 8. Error Handling

The application should handle:

- 401 Unauthenticated
- 403 Unauthorized
- 404 Not Found
- 422 Validation Error
- Business logic errors
- Unexpected 500 errors

Never expose sensitive stack traces or internal information to normal users.

API responses should eventually follow a consistent structure.

---

# 9. Authorization

Authentication != Authorization.

Authentication:
"Who are you?"

Authorization:
"What are you allowed to do?"

Use:

- Spatie roles/permissions
- Laravel Policies
- middleware where appropriate

Examples:

- Customer can only manage own bookings.
- Manager can only manage own hotel.
- Manager cannot modify another manager's room.
- Admin can manage all resources.

Authorization must be tested.

---

# 10. Booking Business Rules

## Dates

- check_in must be valid
- check_out must be valid
- check_out must be after check_in
- check-in should not be in the past
- number of nights = checkout - checkin

## Availability

A room is unavailable if an existing active booking overlaps the requested period.

Example:

Existing:
10 → 15

New:
15 → 20

Allowed.

Existing:
10 → 15

New:
12 → 18

Not allowed.

Existing:
10 → 15

New:
5 → 11

Not allowed.

Existing:
10 → 15

New:
1 → 20

Not allowed.

The exact overlap query must be implemented and tested correctly.

## Capacity

Guests cannot exceed:

`room.max_occupancy`

## Room

Inactive rooms cannot be booked.

## Hotel

Inactive hotels should not expose bookable rooms.

## Ownership

Customers can only access their own booking management functionality.

---

# 11. Booking Lifecycle

Initial target lifecycle:

PENDING
↓
CONFIRMED
↓
COMPLETED

Alternative states:

PENDING → CANCELLED
PENDING → EXPIRED
CONFIRMED → CANCELLED

Exact transitions must be enforced by business rules.

Users must not be able to arbitrarily change booking status.

---

# 12. Planned Customer Features

- Registration
- Login
- OTP email verification
- Hotel listing
- Hotel search
- Hotel filters
- Hotel details
- Room listing
- Room details
- Date availability
- Booking
- Booking history
- Booking details
- Cancellation
- Notifications

---

# 13. Planned Manager Features

- Hotel dashboard
- Hotel CRUD
- Room CRUD
- Room availability management
- Booking management
- Hotel statistics
- Room image management

---

# 14. Planned Admin Features

- Admin dashboard
- User management
- Hotel management
- Room management
- Booking management
- Role/permission management
- Reinstatement request management
- Statistics

---

# 15. Planned Advanced Features

Only implement when there is a real reason:

- Payment integration
- Email notifications
- Database notifications
- Queues
- Scheduled jobs
- Redis
- Caching
- REST API
- Feature tests
- Unit tests
- Docker
- Docker Compose
- Nginx
- GitHub Actions
- CI
- CD
- Deployment
- HTTPS
- Logging
- Database backups

---

# 16. Testing Strategy

Critical workflows must have automated tests.

Priority tests:

## Authentication

- registration
- login
- logout
- OTP verification
- invalid OTP
- expired OTP

## Authorization

- customer cannot access admin routes
- manager cannot modify another manager's hotel
- customer cannot modify another customer's booking

## Booking

- valid booking
- invalid dates
- overlapping booking
- boundary booking
- capacity exceeded
- inactive room
- inactive hotel
- cancellation rules
- booking status transitions

Tests should be written as features are implemented.

Do not leave all testing until the end.

---

# 17. Git Workflow

Recommended:

main
↓
feature branch
↓
implementation
↓
tests
↓
commit
↓
merge

Example branch:

`feature/booking-availability`

Examples of commit messages:

`feat: add booking availability validation`

`fix: handle adjacent booking dates`

`test: cover booking overlap scenarios`

Do not commit secrets.

---

# 18. DevOps Roadmap

## Phase 1 — Git/GitHub

- branches
- pull requests
- merge conflicts
- issues
- project board
- tags
- releases
- README
- conventional commits

## Phase 2 — Linux

- filesystem
- permissions
- users/groups
- processes
- services
- SSH
- environment variables
- logs
- basic networking

## Phase 3 — Docker

- Docker concepts
- Dockerfile
- images
- containers
- volumes
- networks
- Docker Compose
- Laravel container
- MySQL container
- environment configuration

Target:

`docker compose up`

should allow the project to run locally.

## Phase 4 — Nginx

Understand:

Browser
→ Nginx
→ Laravel/PHP-FPM
→ MySQL

## Phase 5 — CI

GitHub Actions:

push/PR
→ install dependencies
→ run tests
→ code checks
→ pass/fail

## Phase 6 — CD

main
→ CI
→ build
→ deploy

## Phase 7 — Production

- Linux server/VPS
- SSH
- Docker
- Nginx
- domain
- HTTPS
- environment variables
- backups
- logs

---

# 19. Project Sprints

## Sprint 0 — Foundation
Deadline: 10 September

- Authentication audit
- Registration
- Login
- Logout
- OTP email verification
- OTP expiration
- OTP resend
- Validation
- Error handling
- Protected routes
- Basic tests

Definition of Done:

- [ ] Auth works
- [ ] OTP works
- [ ] Verification works
- [ ] Validation works
- [ ] Errors handled
- [ ] Protected routes work
- [ ] Tests pass
- [ ] GitHub updated

---

## Sprint 1 — Authorization
Deadline: 13 September

- Roles
- Permissions
- Middleware
- Policies
- Authorization tests

Definition of Done:

- [ ] Customer role works
- [ ] Manager role works
- [ ] Admin role works
- [ ] Policies implemented
- [ ] Cross-user access blocked
- [ ] Tests pass

---

## Sprint 2 — Booking Core
Deadline: 18 September

- Availability
- Booking creation
- Date validation
- Capacity
- Price calculation
- Booking status
- Cancellation

Definition of Done:

- [ ] No overlapping bookings
- [ ] Boundary dates handled
- [ ] Capacity enforced
- [ ] Price calculated correctly
- [ ] Status transitions controlled
- [ ] Tests pass

---

## Sprint 3 — Hotel Management
Deadline: 22 September

- Hotel management
- Room management
- Manager dashboard
- Images
- Room availability

---

## Sprint 4 — User Experience
Deadline: 26 September

- Search
- Filters
- Pagination
- Booking history
- Notifications
- Queues
- Dashboard

---

## Sprint 5 — Quality
Deadline: 29 September

- Tests
- API
- Error handling improvements
- Documentation
- README
- Architecture diagram

---

## Sprint 6 — DevOps

After core application is stable:

- Docker
- Docker Compose
- Linux
- Nginx
- GitHub Actions
- CI/CD

---

## Sprint 7 — Production

- Server
- Deployment
- HTTPS
- Backups
- Logging
- Production configuration

---

# 20. Current Status

Current date:
6 September 2026

Current sprint:

Sprint 0 — Foundation

Current task:

AUTHENTICATION AUDIT

Next tasks:

1. Inspect existing authentication implementation.
2. Inspect OTP implementation.
3. Review validation.
4. Review protected routes.
5. Review error handling.
6. Write/update tests.
7. Push OTP implementation to GitHub.
8. Complete Sprint 0.

Do NOT start booking features until the foundation is sufficiently stable.

---

# 21. AI Assistant Rules

When working on this project:

1. Inspect existing code before suggesting new code.
2. Do not recreate existing functionality unnecessarily.
3. Do not redesign the database without a strong reason.
4. Do not add tables just because they are common in hotel systems.
5. Explain architectural decisions.
6. Teach the concept before/while implementing it.
7. Prefer incremental changes.
8. Keep code appropriate for a student portfolio project.
9. Avoid unnecessary enterprise complexity.
10. Write tests for important business rules.
11. Never claim a feature is complete until it actually works.
12. Never recommend adding something to the CV unless it is implemented.
13. When stuck, help debug instead of immediately replacing the whole implementation.
14. Preserve existing good work.
15. Keep the project realistic and maintainable.

---

# 22. Learning Rule

For every major feature:

LEARN
↓
UNDERSTAND
↓
IMPLEMENT
↓
TEST
↓
COMMIT
↓
DOCUMENT

The goal is not only to finish the application.

The goal is to understand why it works.
