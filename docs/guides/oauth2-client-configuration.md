# 🔐 OAuth2 Client Configuration

## 📋 Overview

This document explains how to configure and use OAuth2 clients with the n8n Clone API using Laravel Passport.

## 🛠️ Created Clients

Two OAuth2 clients have been automatically created:

### 1. Personal Access Client
- **Purpose**: Used for generating personal access tokens for API access
- **Type**: Personal Access Token client
- **Provider**: `users`

### 2. Password Grant Client
- **Purpose**: Used for traditional username/password authentication flows
- **Type**: Password Grant client
- **Provider**: `users`
- **Client ID**: `0199c4fc-d21c-707d-bcec-477b8f49f377`
- **Client Secret**: `Jv97QRngGkt3G8QB0u732hZqXdyyYgE5TQGwqGIT`

> ⚠️ **Security Warning**: The client secret should be kept confidential and never exposed in client-side code.

## 🔑 Authentication Methods

### 1. Password Grant Flow (Most Common for APIs)

To authenticate a user and obtain an access token:

```bash
curl -X POST http://localhost:8000/oauth/token \
  -H "Content-Type: application/json" \
  -d '{
    "grant_type": "password",
    "client_id": "0199c4fc-d21c-707d-bcec-477b8f49f377",
    "client_secret": "Jv97QRngGkt3G8QB0u732hZqXdyyYgE5TQGwqGIT",
    "username": "user@example.com",
    "password": "user-password",
    "scope": "*"
  }'
```

**Response:**
```json
{
  "token_type": "Bearer",
  "expires_in": 31536000,
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
  "refresh_token": "def50200a0e..."
}
```

### 2. Using the Access Token

Once you have an access token, use it in the Authorization header for all API requests:

```bash
curl -X GET http://localhost:8000/api/v1/workflows \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..." \
  -H "Accept: application/json"
```

### 3. Refreshing Tokens

To refresh an expired access token:

```bash
curl -X POST http://localhost:8000/oauth/token \
  -H "Content-Type: application/json" \
  -d '{
    "grant_type": "refresh_token",
    "refresh_token": "def50200a0e...",
    "client_id": "0199c4fc-d21c-707d-bcec-477b8f49f377",
    "client_secret": "Jv97QRngGkt3G8QB0u732hZqXdyyYgE5TQGwqGIT",
    "scope": "*"
  }'
```

## 📱 Client-Side Implementation

### JavaScript (Fetch API) Example

```javascript
// Authenticate user and get access token
async function authenticateUser(email, password) {
  const response = await fetch('/oauth/token', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      grant_type: 'password',
      client_id: '0199c4fc-d21c-707d-bcec-477b8f49f377',
      client_secret: 'Jv97QRngGkt3G8QB0u732hZqXdyyYgE5TQGwqGIT',
      username: email,
      password: password,
      scope: '*'
    })
  });

  const data = await response.json();
  return data.access_token;
}

// Make authenticated API request
async function getWorkflows(accessToken) {
  const response = await fetch('/api/v1/workflows', {
    headers: {
      'Authorization': `Bearer ${accessToken}`,
      'Accept': 'application/json'
    }
  });

  return await response.json();
}

// Usage
authenticateUser('user@example.com', 'password123')
  .then(token => {
    console.log('Authenticated successfully');
    return getWorkflows(token);
  })
  .then(workflows => {
    console.log('Workflows:', workflows);
  });
```

### Python (Requests) Example

```python
import requests

# Authenticate user and get access token
def authenticate_user(email, password):
    response = requests.post('http://localhost:8000/oauth/token', json={
        'grant_type': 'password',
        'client_id': '0199c4fc-d21c-707d-bcec-477b8f49f377',
        'client_secret': 'Jv97QRngGkt3G8QB0u732hZqXdyyYgE5TQGwqGIT',
        'username': email,
        'password': password,
        'scope': '*'
    })
    
    return response.json()['access_token']

# Make authenticated API request
def get_workflows(access_token):
    headers = {
        'Authorization': f'Bearer {access_token}',
        'Accept': 'application/json'
    }
    
    response = requests.get('http://localhost:8000/api/v1/workflows', headers=headers)
    return response.json()

# Usage
try:
    token = authenticate_user('user@example.com', 'password123')
    print('Authenticated successfully')
    
    workflows = get_workflows(token)
    print('Workflows:', workflows)
except Exception as e:
    print(f'Error: {e}')
```

## 🔒 Security Best Practices

### 1. Client Secret Protection
- Never hardcode client secrets in client-side applications
- Use environment variables for configuration
- Rotate secrets periodically
- Use different clients for different applications

### 2. Token Management
- Store tokens securely (HTTP-only cookies for web apps)
- Implement automatic token refresh
- Handle token expiration gracefully
- Use short-lived access tokens with refresh tokens

### 3. Scope Management
- Request only necessary scopes
- Implement scope-based access control
- Validate scopes on the server side

### 4. HTTPS Requirements
- Always use HTTPS in production
- Never transmit tokens over unencrypted connections
- Validate SSL certificates

## 🛠️ Troubleshooting

### Common Issues:

1. **Invalid Client Credentials**
   - Verify client ID and secret are correct
   - Ensure client exists in the database
   - Check that client is not revoked

2. **Invalid User Credentials**
   - Verify username and password
   - Ensure user account is active
   - Check for account lockouts

3. **Token Expiration**
   - Implement refresh token logic
   - Handle 401 responses appropriately
   - Store refresh tokens securely

4. **Scope Issues**
   - Verify requested scopes are valid
   - Check that scopes are granted to the client
   - Ensure scopes are appropriate for the requested action

## 🔄 Client Management

### Creating New Clients

To create additional clients for different applications:

```bash
# Create a new client
php artisan passport:client --name="Mobile App Client"

# Create a client for a specific user
php artisan passport:client --user=1 --name="User Specific Client"
```

### Listing Existing Clients

```bash
# Show all clients
php artisan tinker
>>> DB::table('oauth_clients')->get(['id', 'name', 'provider', 'revoked'])->toArray();
```

### Revoking Clients

To revoke a client:
```sql
UPDATE oauth_clients SET revoked = 1 WHERE id = 'client-id';
```

## 📞 Support

For issues with OAuth2 client configuration:
1. Check Laravel Passport documentation
2. Verify database client records
3. Ensure proper environment configuration
4. Review API logs for authentication errors