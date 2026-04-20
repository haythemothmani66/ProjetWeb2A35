# Dasher UI - Free Bootstrap 5 Admin Dashboard Template

#### Preview

 - [Demo](https://themewagon.github.io/dasher/)

#### Download
 - [Download from ThemeWagon](https://themewagon.com/themes/dasher/)

## Getting Started

1. Clone Repository
```
git clone https://github.com/themewagon/dasher.git
```
2. Install Dependencies
```
npm i
```
3. Run the development server:

```bash
npm run dev
# or
yarn dev
# or
pnpm dev
# or
bun dev
```

## EduMatch BackOffice User Management (MVC)

This project now includes a complete User Management module with MVC architecture.

### Run the backend API + dashboard

```bash
npm run start:api
```

Open:

- `http://localhost:4000/index.html`
- `http://localhost:4000/pages/backoffice/users.html`

### Admin-only API access

The User Management API is protected by an admin middleware.
Requests must include this header:

```text
x-user-role: admin
```

### Required API routes

- `GET /users`
- `GET /users/{id}`
- `POST /users`
- `PUT /users/{id}`
- `DELETE /users/{id}`

## Author 
```
Design and code is completely written by CodesCandy and development team. 
```

## License

 - Design and Code is Copyright &copy; <a href="https://codescandy.com/" target="_blank">CodesCandy</a>
 - Licensed cover under [MIT]
 - Distributed by <a href="https://themewagon.com" target="_blank">ThemeWagon</a>
