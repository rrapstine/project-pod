POST http://api.project-pod.test/auth/login
Accept: application/json

{
  "email": "wrong@example.com",
  "password": "wrongpassword"
}

HTTP/1.1 200
