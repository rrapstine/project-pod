[Includes]
../setup.hurl

# Test that the authenticated user can logout
POST http://api.project-pod.test/auth/logout
Accept: application/json
[Cookies]
X-XSRF-TOKEN: {{xsrf_token}}

HTTP 200
