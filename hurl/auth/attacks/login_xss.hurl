POST http://api.project-pod.test/auth/login
Accept: application/json

{
  "email": "<script>alert('xss')</script>@test.com",
  "password": "password123"
}

HTTP/1.1 200
