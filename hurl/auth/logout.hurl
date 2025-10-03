[Includes]
../setup.hurl

# Test that the authenticated user can logout
POST http://api.project-pod.test/auth/logout
Accept: application/json

HTTP 200