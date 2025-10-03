POST http://api.project-pod.test/auth/register
Accept: application/json

{
  "name": "Test User",
  "email": "test@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}

HTTP/1.1 200
