### API Endpoints and Details

#### Auth APIs

1. Auth Callback  
   - Endpoint: `POST /api/auth/callback`  
   - Input:  
     ```json
     {
       "code": "string"
     }
     ```  
   - Output:  set-cookie: { "jwt-token": "string" }
   - Status Codes: 200 (Success), 401 (Unauthorized)  

#### User Management APIs

2. Get All User Profiles  
   - Endpoint: `GET /api/users`  
   - Input:  
     ```json
     {
       "page": "number",
       "pageSize": "number",
       "pageOffset": "number",
       "filters": {
         "userId": "string",
         "email": "string",
         "role": "string",
         "isIn": "boolean",
         "name": "string"
       }
     }
     ```  
   - Output:  
     ```json
     [
       {
         "id": "string",
         "name": "string",
         "phone": "string",
         "email": "string"
       }
     ]
     ```  

3. Get My Profile  
   - Endpoint: `GET /api/users/me`  
   - Input: None  
   - Output:  
     ```json
     {
       "id": "string",
       "name": "string",
       "phone": "string",
       "email": "string"
     }
     ```  
   - Status Codes: 200 (Success), 422 (Validation Error)  

4. Get Users Count  
   - Endpoint: `GET /api/users/count`  
   - Input: None  
   - Headers: X-User-Info: { "email": "string" }
   - Output:  
     ```json
     {
       "normal": "number",
       "admin": "number"
     }
     ```  

5. Update My Profile  
   - Endpoint: `PUT /api/users/me`  
   - Headers: X-User-Info: { "email": "string" }
   - Input:  
     ```json
     {
       "name": "string"
     }
     ```  
   - Output:  
     ```json
     {
       "message": "Profile updated successfully"
     }
     ```  

6. Ban User  
   - Endpoint: `POST /api/users/{email}/ban`  
   - Input:  
     ```json
     {
       "reason": "string",
       "end_at": "Date"
     }
     ```  
   - Output: None  
   - Status Codes: 204 (Success), 400 (User Not Found)  

7. Unban User  
   - Endpoint: `DELETE /api/users/{email}/ban`  
   - Input: None  
   - Output: None  
   - Status Codes: 204 (Success), 400 (User Not Found)  

8. Update User Points  
   - Endpoint: `PUT /api/users/{email}/points`  
   - Input:  
     ```json
     {
       "points": "number"
     }
     ```  
   - Output: None  
   - Status Codes: 204 (Success), 400 (User Not Found)  

9. Grant Role  
   - Endpoint: `POST /api/users/{email}/grant-role`  
   - Input:  
     ```json
     {
       "role": "string"
     }
     ```  
   - Output: None  
   - Status Codes: 204 (Success), 400 (User Not Found)  

#### Settings APIs
10. Get Settings  
    - Endpoint: `GET /api/settings`  
    - Input: None  
    - Output:  
      ```json
      {
        "settings": "object"
      }
      ```  
    - Status Codes: 200 (Success)  

11. Update Settings  
    - Endpoint: `PUT /api/settings`  
    - Input:  
      ```json
      {
        "settings": "object"
      }
      ```  
    - Output: None  
    - Status Codes: 204 (Success), 400 (Validation Error)  
