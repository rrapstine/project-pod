# Base setup file to run for each requst. Gets and captures the CSRF token from
# Laravel Sanctum and then logs the user in, ensuring that we get a session cookie.

# Capture the CSRF token
GET {{HURL_base_url}}/sanctum/csrf-cookie
Accept: application/json

HTTP 204
[Captures]
xsrf_token: cookie "XSRF-TOKEN"

###

# Login with Test User
POST {{HURL_base_url}}/auth/login
Accept: application/json
Content-Type: application/json
[Cookies]
X-XSRF-TOKEN: {{xsrf_token}}

{
  "email": "{{HURL_test_user_email}}",
  "password": "{{HURL_test_user_password}}"
}

HTTP 200
