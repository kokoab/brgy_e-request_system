# 🎓 COMPLETE SYSTEM EXPLANATION

## 📋 TABLE OF CONTENTS
1. [What This Project Does](#what-this-project-does)
2. [Architecture Overview](#architecture-overview)
3. [Docker Setup Explained](#docker-setup-explained)
4. [Backend (Laravel) Deep Dive](#backend-laravel-deep-dive)
5. [Frontend (Angular) Deep Dive](#frontend-angular-deep-dive)
6. [Database Structure](#database-structure)
7. [Authentication Flow](#authentication-flow)
8. [Role-Based Access Control](#role-based-access-control)
9. [How Everything Connects](#how-everything-connects)

---

## 🎯 WHAT THIS PROJECT DOES

This is a **Document Request Management System** with 3 types of users:

1. **Requestor** - Regular users who request documents (like clearance certificates)
2. **Staff** - Employees who approve/reject document requests
3. **Admin** - Administrators who see system overview and statistics

**Real-world example:** A student needs a clearance certificate. They submit a request → Staff reviews it → Staff approves/rejects → Admin sees all activity.

---

## 🏗️ ARCHITECTURE OVERVIEW

```
┌─────────────────────────────────────────────────────────────┐
│                    YOUR COMPUTER (HOST)                     │
│                                                             │
│  ┌──────────────┐    ┌──────────────┐    ┌──────────────┐ │
│  │   Browser    │    │   Browser    │    │   Browser    │ │
│  │  localhost:  │    │  localhost:  │    │  localhost:  │ │
│  │    4200      │    │    8000       │    │    3306      │ │
│  └──────┬───────┘    └──────┬───────┘    └──────┬───────┘ │
│         │                   │                    │         │
└─────────┼───────────────────┼────────────────────┼─────────┘
          │                   │                    │
          ▼                   ▼                    ▼
┌─────────────────────────────────────────────────────────────┐
│                    DOCKER CONTAINERS                        │
│                                                             │
│  ┌──────────────┐    ┌──────────────┐    ┌──────────────┐ │
│  │   Angular    │    │    Nginx     │    │    MySQL     │ │
│  │   (Frontend) │    │   (Web       │    │   (Database) │ │
│  │   Port 4200  │    │    Server)   │    │   Port 3306  │ │
│  │              │    │   Port 80    │    │              │ │
│  └──────┬───────┘    └──────┬───────┘    └──────┬───────┘ │
│         │                   │                    │         │
│         │                   ▼                    │         │
│         │          ┌──────────────┐               │         │
│         │          │   Laravel    │               │         │
│         │          │   (Backend)  │               │         │
│         │          │   PHP-FPM    │               │         │
│         │          └──────────────┘               │         │
│         │                                         │         │
│         └─────────────────────────────────────────┘         │
│                    (All on app-network)                     │
└─────────────────────────────────────────────────────────────┘
```

**Key Concept:** Everything runs in isolated containers, but they can talk to each other through Docker networking.

---

## 🐳 DOCKER SETUP EXPLAINED

### What is Docker?
Docker packages applications and their dependencies into "containers" - like lightweight virtual machines that share your OS kernel.

### Your docker-compose.yml Breakdown

```yaml
services:
  angular:    # Frontend container
  app:        # Laravel PHP container  
  webserver:  # Nginx web server container
  db:         # MySQL database container
```

#### 1. **Angular Container** (Frontend)
```yaml
angular:
  build:
    context: ./frontend          # Build from frontend folder
    dockerfile: Dockerfile       # Use this Dockerfile
  ports:
    - "4200:4200"               # Map host port 4200 → container port 4200
  volumes:
    - ./frontend:/usr/src/app   # Sync your code into container
```

**What this means:**
- When you edit files in `./frontend`, they sync into the container
- Access it at `http://localhost:4200`
- Runs `ng serve` to start Angular dev server

#### 2. **App Container** (Laravel Backend)
```yaml
app:
  build:
    context: ./backend
  working_dir: /var/www         # All commands run here
  volumes:
    - ./backend:/var/www        # Your Laravel code
```

**What this means:**
- Runs PHP-FPM (FastCGI Process Manager)
- Processes PHP code
- Can't be accessed directly - goes through Nginx

#### 3. **Webserver Container** (Nginx)
```yaml
webserver:
  image: nginx:alpine           # Use pre-built Nginx image
  ports:
    - "8000:80"                 # Host 8000 → Container 80
  depends_on:
    - app                       # Start after 'app' container
```

**What this means:**
- Nginx receives HTTP requests on port 80 (inside container)
- Forwards PHP requests to the `app` container
- You access it at `http://localhost:8000`
- Acts as a reverse proxy

**Why Nginx?**
- PHP-FPM can't handle HTTP directly
- Nginx handles HTTP, forwards PHP to PHP-FPM
- Like a receptionist directing visitors

#### 4. **DB Container** (MySQL)
```yaml
db:
  image: mysql:5.7.22
  environment:
    MYSQL_DATABASE: laravel
    MYSQL_ROOT_PASSWORD: sql123
  volumes:
    - dbdata:/var/lib/mysql/    # Persistent storage
```

**What this means:**
- Database data persists even if container restarts
- Access at `localhost:3306`
- Stores all your application data

#### 5. **Networks**
```yaml
networks:
  app-network:
    driver: bridge
```

**What this means:**
- All containers can talk to each other
- Angular can call `http://webserver:80/api/...`
- Containers use service names as hostnames

---

## 🔧 BACKEND (LARAVEL) DEEP DIVE

### What is Laravel?
Laravel is a PHP framework - it provides structure and tools to build web applications faster.

### Laravel File Structure

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/        # Handle HTTP requests
│   │   │   ├── AuthController.php
│   │   │   └── DocumentRequestController.php
│   │   └── Middleware/         # Run code before/after requests
│   │       └── CheckRole.php
│   └── Models/                 # Represent database tables
│       ├── User.php
│       └── DocumentRequest.php
├── database/
│   └── migrations/             # Database schema definitions
├── routes/
│   └── api.php                 # Define API endpoints
└── public/
    └── index.php               # Entry point
```

### 1. MODELS (Database Tables as Code)

#### User Model (`app/Models/User.php`)
```php
class User extends Authenticatable
{
    protected $fillable = [
        'name', 'email', 'password', 'role'
    ];
}
```

**What this means:**
- Represents the `users` table in database
- `$fillable` = fields that can be mass-assigned (security)
- `extends Authenticatable` = has authentication features

**Role Constants:**
```php
const ROLE_REQUESTOR = 'requestor';
const ROLE_STAFF = 'staff';
const ROLE_ADMIN = 'admin';
```

**Helper Methods:**
```php
public function isStaff(): bool {
    return $this->role === self::ROLE_STAFF;
}
```
These check the user's role easily.

#### DocumentRequest Model (`app/Models/DocumentRequest.php`)
```php
protected $fillable = [
    'user_id',
    'document_type',
    'document_data',
    'document_status',
    'staff_message',  // Optional message from staff
];

protected $casts = [
    'document_data' => 'array',
];
```

**What this means:**
- `document_data` is stored as JSON in database
- Laravel automatically converts: array ↔ JSON
- You work with arrays in code, database stores JSON
- `staff_message` is a fillable field (can be mass-assigned)
- `staff_message` is nullable (optional field)

### 2. CONTROLLERS (Handle Requests)

#### AuthController (`app/Http/Controllers/AuthController.php`)

**Register Method:**
```php
public function register(Request $request)
{
    // 1. Validate input
    $request->validate([
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8|confirmed',
    ]);
    
    // 2. Create user
    $user = User::create([
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'role' => User::ROLE_REQUESTOR,
    ]);
    
    // 3. Generate token
    $token = $user->createToken('auth-token')->plainTextToken;
    
    // 4. Return response
    return response()->json([
        'user' => $user,
        'token' => $token
    ]);
}
```

**Step-by-step:**
1. **Validate** - Check if data is correct
2. **Create** - Save user to database
3. **Hash password** - Never store plain passwords!
4. **Create token** - For authentication (Laravel Sanctum)
5. **Return JSON** - Send response to frontend

**Login Method:**
```php
public function login(Request $request)
{
    // Find user by email
    $user = User::where('email', $request->email)->first();
    
    // Check password
    if (!Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['Wrong credentials']
        ]);
    }
    
    // Generate token
    $token = $user->createToken('auth-token')->plainTextToken;
    
    return response()->json(['user' => $user, 'token' => $token]);
}
```

**Key Concept:** `Hash::check()` compares plain password with hashed password.

#### DocumentRequestController

**Store Method (Create Request):**
```php
public function store(Request $request)
{
    $request->validate([
        'document_type' => 'required|string'
    ]);

    $user = $request->user();  // Get authenticated user

    // Capture user info at time of request (snapshot)
    $documentData = [
        'name' => $user->name,
        'email' => $user->email,
        'phone' => $user->phone,
        'birthday' => $user->birthday,
    ];
    
    $documentRequest = DocumentRequest::create([
        'user_id' => $user->id,
        'document_type' => $request->document_type,
        'document_data' => $documentData,  // Stored as JSON
        'document_status' => 'pending',    // Default status
    ]);
    
    return response()->json($documentRequest, 201);
}
```

**Key Features:**
- **Validates input:** Ensures document_type is provided
- **Captures user snapshot:** Saves user info at time of request
- **Why snapshot?** Even if user updates profile later, request data stays the same
- **Default status:** All new requests start as 'pending'
- **JSON storage:** document_data stored as JSON in database

**Index Method (List Requests):**
```php
public function index(Request $request)
{
    $user = $request->user();
    
    if ($user->isRequestor()) {
        // Requestors see only their requests
        $documentRequests = DocumentRequest::where('user_id', $user->id)
            ->with('user:id,name,email')
            ->orderBy('created_at', 'desc')  // Newest first
            ->get();
    } else {
        // Staff/Admin see all requests
        $documentRequests = DocumentRequest::with('user:id,name,email,role')
            ->orderBy('created_at', 'desc')  // Newest first
            ->get();
    }
    
    return response()->json([
        'data' => $documentRequests,
        'message' => 'Document requests fetched successfully'
    ]);
}
```

**Key Features:**
- **Same endpoint, different data** based on user role
- **Ordered by date:** Newest requests appear first (`orderBy('created_at', 'desc')`)
- **Eager loading:** Includes user relationship (avoids N+1 query problem)
- **Selective fields:** Only loads needed user fields (id, name, email)
- **Includes staff_message:** Returns staff messages if they exist

**Approve Method:**
```php
public function approve(Request $request)
{
    $user = $request->user();
    
    // Only staff can approve
    if (!$user->isStaff()) {
        return response()->json([
            'message' => 'Only staff members can approve document requests'
        ], 403);
    }

    $request->validate([
        'document_request_id' => 'required|exists:document_requests,id',
        'message' => 'nullable|string|max:1000',  // Optional message
    ]);

    $documentRequest = DocumentRequest::find($request->document_request_id);
    $documentRequest->update([
        'document_status' => 'approved',
        'staff_message' => $request->input('message')  // Can be NULL
    ]);
    
    return response()->json([
        'message' => 'Document request approved successfully',
        'data' => $documentRequest->load('user:id,name,email')
    ]);
}
```

**Reject Method:**
```php
public function reject(Request $request)
{
    $user = $request->user();
    
    // Only staff can reject
    if (!$user->isStaff()) {
        return response()->json([
            'message' => 'Only staff members can reject document requests'
        ], 403);
    }

    $request->validate([
        'document_request_id' => 'required|exists:document_requests,id',
        'message' => 'nullable|string|max:1000',  // Optional message
    ]);

    $documentRequest = DocumentRequest::find($request->document_request_id);
    $documentRequest->update([
        'document_status' => 'rejected',
        'staff_message' => $request->input('message')  // Can be NULL
    ]);
    
    return response()->json([
        'message' => 'Document request rejected successfully',
        'data' => $documentRequest->load('user:id,name,email')
    ]);
}
```

**Key Features of Approve/Reject:**
- **Role check:** Only staff can approve/reject (checked at middleware and controller level)
- **Optional message:** Staff can include a message (max 1000 characters)
- **Message storage:** Saved to `staff_message` field in database
- **Message visibility:** Visible to requestors on their dashboard
- **Status update:** Changes `document_status` to 'approved' or 'rejected'

### 3. MIDDLEWARE (Run Code Before/After Requests)

#### CheckRole Middleware (`app/Http/Middleware/CheckRole.php`)
```php
public function handle(Request $request, Closure $next, ...$roles)
{
    $userRole = $request->user()->role;
    
    if (!in_array($userRole, $roles)) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }
    
    return $next($request);  // Continue to controller
}
```

**What this does:**
- Checks if user has required role
- If not → return 403 error
- If yes → continue to controller

**Usage:**
```php
Route::middleware('role:staff')->group(function () {
    // Only staff can access these routes
});
```

### 4. ROUTES (Define API Endpoints)

#### `routes/api.php`
```php
// Public routes (no authentication needed)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (need authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/document-request', [DocumentRequestController::class, 'store']);
    
    // Staff only
    Route::middleware('role:staff')->group(function () {
        Route::post('/document-request/approve', [DocumentRequestController::class, 'approve']);
        Route::post('/document-request/reject', [DocumentRequestController::class, 'reject']);
    });
    
    // Admin only
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/overview', [DocumentRequestController::class, 'overview']);
    });
});
```

**Route Structure:**
```
HTTP Method + URL Path → Controller@Method
```

**Example:**
- `POST /api/register` → `AuthController@register`
- `GET /api/user` → `AuthController@user` (needs auth)
- `POST /api/document-request/approve` → `DocumentRequestController@approve` (needs auth + staff role)

### 5. MIGRATIONS (Database Schema)

#### Create Users Table
```php
Schema::create('users', function (Blueprint $table) {
    $table->id();                    // Auto-incrementing ID
    $table->string('name');
    $table->string('email')->unique();
    $table->string('password');
    $table->enum('role', ['requestor', 'staff', 'admin'])->default('requestor');
    $table->timestamps();             // created_at, updated_at
});
```

**What migrations do:**
- Define database structure in code
- Version control for database
- Run with: `php artisan migrate`

#### Add Staff Message Column
```php
Schema::table('document_requests', function (Blueprint $table) {
    $table->text('staff_message')->nullable()->after('document_status');
});
```

**What this does:**
- Adds `staff_message` column to `document_requests` table
- **Type:** TEXT (can store long messages)
- **Nullable:** Can be NULL (optional field)
- **Position:** After `document_status` column
- **Purpose:** Store optional messages from staff when approving/rejecting

**Migration file:** `2025_11_26_000000_add_staff_message_to_document_requests_table.php`

**To run the migration:**
```bash
# If using Docker
docker-compose exec -T app php artisan migrate

# Or if running locally
php artisan migrate
```

**Important:** This migration must be run before using the approve/reject with message feature. The `staff_message` column must exist in the database.

---

## 🎨 FRONTEND (ANGULAR) DEEP DIVE

### What is Angular?
Angular is a TypeScript framework for building web applications. It uses components, services, and dependency injection.

### Angular File Structure

```
frontend/src/app/
├── components/              # UI components
│   ├── login/
│   ├── register/
│   └── dashboard/
│       ├── requestor-dashboard/
│       ├── staff-dashboard/
│       └── admin-dashboard/
├── services/                # Business logic, API calls
│   ├── auth.service.ts
│   └── document-request.service.ts
├── guards/                  # Route protection
│   └── auth.guard.ts
├── interceptors/            # Modify HTTP requests
│   └── auth.interceptor.ts
└── app.routes.ts           # Define routes
```

### 1. SERVICES (API Communication)

#### AuthService (`services/auth.service.ts`)

**What is a Service?**
- Contains business logic
- Makes HTTP requests
- Can be injected into components
- Singleton (one instance shared)

**Key Parts:**

```typescript
@Injectable({
  providedIn: 'root'  // Available everywhere
})
export class AuthService {
  private apiUrl = environment.apiUrl;  // 'http://localhost:8000/api'
  
  // Signals (reactive state)
  currentUser = signal<User | null>(null);
  isAuthenticated = signal<boolean>(false);
}
```

**Register Method:**
```typescript
register(
  name: string, 
  birthday: string, 
  phone: string, 
  email: string, 
  password: string, 
  passwordConfirmation: string
): Observable<AuthResponse> {
  return this.http.post<AuthResponse>(`${this.apiUrl}/register`, {
    name,
    birthday,        // Date format: YYYY-MM-DD
    phone,           // Phone number
    email,
    password,
    password_confirmation: passwordConfirmation  // Must match password
  }).pipe(
    tap(response => this.handleAuthResponse(response))
  );
}
```
- **All fields required:** name, birthday, phone, email, password, password_confirmation
- **Password validation:** Must match password_confirmation (Laravel `confirmed` rule)
- **Phone validation:** Must be unique
- **Email validation:** Must be unique and valid email format
- **Birthday format:** Must be valid date (YYYY-MM-DD)

**What happens:**
1. `http.post()` sends POST request to backend
2. Returns `Observable` (async data stream)
3. `.pipe(tap(...))` runs code when response arrives
4. Saves token and user data

**Login Method:**
```typescript
login(email: string, password: string): Observable<AuthResponse> {
  return this.http.post<AuthResponse>(`${this.apiUrl}/login`, {
    email, password
  }).pipe(
    tap(response => this.handleAuthResponse(response))
  );
}
```

**handleAuthResponse:**
```typescript
private handleAuthResponse(response: AuthResponse): void {
  localStorage.setItem('auth_token', response.token);  // Save token
  this.currentUser.set(response.user);                 // Update state
  this.isAuthenticated.set(true);                      // Mark as logged in
}
```

**Key Concept:** `localStorage` persists data in browser (survives refresh).

#### DocumentRequestService (`services/document-request.service.ts`)

**Purpose:** Handles all document request operations (create, list, approve, reject, overview)

**Interfaces (Type Definitions):**
```typescript
export interface DocumentRequest {
  id: number;
  user_id: number;
  document_type: string;
  document_data: {
    name: string;
    email: string;
    phone: string;
    birthday: string;
  };
  document_status: 'pending' | 'approved' | 'rejected';
  staff_message?: string | null;  // Optional message from staff
  created_at: string;
  updated_at: string;
  user?: {  // Populated when staff/admin views
    id: number;
    name: string;
    email: string;
    role?: string;
  };
}

export interface OverviewStats {
  total_requests: number;
  pending_requests: number;
  approved_requests: number;
  rejected_requests: number;
  total_users: number;
  requestors: number;
  staff: number;
  admins: number;
}
```

**Create Request:**
```typescript
createRequest(documentType: string): Observable<DocumentRequest> {
  return this.http.post<DocumentRequest>(
    `${this.apiUrl}/document-request`,
    { document_type: documentType }
  );
}
```
- **Who can use:** Requestors
- **What it does:** Creates a new document request
- **Backend:** Extracts user info automatically, saves to database

**Get Requests:**
```typescript
getRequests(): Observable<{ data: DocumentRequest[]; message: string }> {
  return this.http.get<{ data: DocumentRequest[]; message: string }>(
    `${this.apiUrl}/document-requests`
  );
}
```
- **Who can use:** Everyone (but sees different data)
- **Requestors:** See only their own requests (filtered by user_id)
- **Staff/Admin:** See all requests from all users
- **Backend:** 
  - Filters based on user role
  - Orders by `created_at DESC` (newest first)
  - Includes `staff_message` field in response
- **Response includes:** 
  - All request fields
  - User relationship (who made the request)
  - Staff message (if exists)

**Approve Request:**
```typescript
approveRequest(documentRequestId: number, message?: string): Observable<{ message: string; data: DocumentRequest }> {
  return this.http.post(
    `${this.apiUrl}/document-request/approve`,
    { 
      document_request_id: documentRequestId,
      message: message || null  // Optional staff message (max 1000 chars)
    }
  );
}
```
- **Who can use:** Staff only
- **What it does:** 
  - Changes status to 'approved'
  - Optionally saves staff message (visible to requestor)
  - Updates `staff_message` field in database
- **Backend:** 
  - Validates staff role
  - Validates message (nullable, string, max 1000)
  - Updates database with status and message

**Reject Request:**
```typescript
rejectRequest(documentRequestId: number, message?: string): Observable<{ message: string; data: DocumentRequest }> {
  return this.http.post(
    `${this.apiUrl}/document-request/reject`,
    { 
      document_request_id: documentRequestId,
      message: message || null  // Optional rejection reason (max 1000 chars)
    }
  );
}
```
- **Who can use:** Staff only
- **What it does:** 
  - Changes status to 'rejected'
  - Optionally saves rejection reason (visible to requestor)
  - Updates `staff_message` field in database
- **Use case:** Staff can explain why request was rejected

**Get Overview (Admin):**
```typescript
getOverview(): Observable<{
  stats: OverviewStats;
  recent_requests: DocumentRequest[];
  message: string;
}> {
  return this.http.get(`${this.apiUrl}/admin/overview`);
}
```
- **Who can use:** Admin only
- **What it does:** Returns system statistics and recent activity
- **Backend:** Calculates counts, fetches recent requests

### 2. COMPONENTS (UI Pieces)

#### Component Structure
```typescript
@Component({
  selector: 'app-register',        // Use as <app-register>
  standalone: true,                 // Can be used independently
  imports: [CommonModule, FormsModule],
  templateUrl: './register.component.html'
})
export class RegisterComponent {
  name = '';                        // Component state
  email = '';
  password = '';
  
  constructor(
    private authService: AuthService  // Dependency injection
  ) {}
  
  onSubmit(): void {
    this.authService.register(...).subscribe({
      next: () => this.router.navigate(['/dashboard']),
      error: (err) => this.error = err.error.message
    });
  }
}
```

**Key Concepts:**
- **State** - Data the component holds (`name`, `email`)
- **Methods** - Functions component can call (`onSubmit()`)
- **Dependency Injection** - Angular provides services automatically

#### Template (HTML)
```html
<form (ngSubmit)="onSubmit()">
  <input [(ngModel)]="name" />
  <button type="submit">Register</button>
</form>
```

**Directives:**
- `(ngSubmit)` - Event binding (when form submits)
- `[(ngModel)]` - Two-way binding (syncs input with component state)
- `*ngIf` - Conditional rendering
- `*ngFor` - Loop through arrays

#### Dashboard Component (Role-Based Routing)

**Main Dashboard (`dashboard.component.ts`):**
```typescript
@Component({
  selector: 'app-dashboard',
  imports: [
    RequestorDashboardComponent,
    StaffDashboardComponent,
    AdminDashboardComponent
  ]
})
export class DashboardComponent {
  constructor(public authService: AuthService) {}
}
```

**Template (`dashboard.component.html`):**
```html
<div class="dashboard-container">
  <header>
    <h1>Dashboard</h1>
    <span>Welcome, {{ user.name }} ({{ user.role }})</span>
    <button (click)="logout()">Logout</button>
  </header>

  <div class="content">
    <!-- Show based on role -->
    <app-requestor-dashboard *ngIf="authService.isRequestor()"></app-requestor-dashboard>
    <app-staff-dashboard *ngIf="authService.isStaff()"></app-staff-dashboard>
    <app-admin-dashboard *ngIf="authService.isAdmin()"></app-admin-dashboard>
  </div>
</div>
```

**Key Concept:** Same route (`/dashboard`), different component based on user role!

#### Requestor Dashboard Component

**Purpose:** Allows requestors to create and view their document requests

**Component (`requestor-dashboard.ts`):**
```typescript
export class RequestorDashboardComponent implements OnInit {
  documentRequests: DocumentRequest[] = [];  // List of requests
  loading = false;                            // Loading state
  showRequestForm = false;                    // Toggle form visibility
  documentType = '';                          // Form input

  constructor(private documentService: DocumentRequestService) {}

  ngOnInit(): void {
    this.loadRequests();  // Load on component init
  }

  loadRequests(): void {
    this.loading = true;
    this.documentService.getRequests().subscribe({
      next: (response) => {
        this.documentRequests = response.data;  // Update list
        this.loading = false;
      },
      error: (err) => {
        this.error = err.error?.message;
        this.loading = false;
      }
    });
  }

  submitRequest(): void {
    this.documentService.createRequest(this.documentType).subscribe({
      next: () => {
        this.documentType = '';           // Clear form
        this.showRequestForm = false;     // Hide form
        this.loadRequests();              // Refresh list
      }
    });
  }
}
```

**Features:**
- Create new document requests
- View own requests with status (pending/approved/rejected)
- Color-coded status badges
- Form validation

#### Staff Dashboard Component

**Purpose:** Allows staff to review and approve/reject all document requests with optional messages

**Component (`staff-dashboard.ts`):**
```typescript
export class StaffDashboardComponent implements OnInit {
  documentRequests: DocumentRequest[] = [];  // All requests (not just own)
  loading = false;
  
  // Modal state management
  showModal = false;
  currentRequestId: number | null = null;
  actionType: 'approve' | 'reject' | null = null;
  message = '';  // Optional message for staff

  ngOnInit(): void {
    this.loadRequests();  // Loads ALL requests (ordered by newest first)
  }

  openApproveModal(requestId: number): void {
    this.currentRequestId = requestId;
    this.actionType = 'approve';
    this.message = '';
    this.showModal = true;  // Show modal
  }

  openRejectModal(requestId: number): void {
    this.currentRequestId = requestId;
    this.actionType = 'reject';
    this.message = '';
    this.showModal = true;
  }

  closeModal(): void {
    this.showModal = false;
    this.currentRequestId = null;
    this.actionType = null;
    this.message = '';
  }

  submitAction(): void {
    const message = this.message.trim() || undefined;
    const requestId = this.currentRequestId!;

    const request = this.actionType === 'approve'
      ? this.documentService.approveRequest(requestId, message)
      : this.documentService.rejectRequest(requestId, message);

    request.subscribe({
      next: () => {
        this.success = `Request ${this.actionType}d successfully`;
        this.closeModal();
        this.loadRequests();  // Refresh list
      }
    });
  }
}
```

**Features:**
- **Modal System:** Professional modal dialog for approve/reject actions
- **Optional Messages:** Staff can add messages when approving/rejecting
- **View ALL Requests:** See all document requests from all users
- **Requester Information:** See name, email, phone, birthday
- **Status Display:** Color-coded status badges (pending/approved/rejected)
- **Staff Messages:** Display staff messages on approved/rejected requests
- **Ordered by Date:** Requests shown newest first
- **Real-time Updates:** List refreshes after actions

**Modal Workflow:**
1. Staff clicks "Approve" or "Reject" button on a pending request
2. Modal opens with textarea for optional message
3. Staff can enter a message (max 1000 characters) or leave it blank
4. Staff clicks "Approve" or "Reject" button in modal footer
5. Request is processed with optional message
6. Modal closes and list refreshes
7. Staff message appears on the request card (if provided)
8. Requestor can see the message on their dashboard
1. Staff clicks "Approve" or "Reject" button
2. Modal opens with textarea for optional message
3. Staff can enter message or leave blank
4. Staff clicks "Approve" or "Reject" in modal
5. Request is processed with optional message
6. Modal closes, list refreshes
7. Staff message appears on the request card

**Key Difference from Requestor:**
- Requestors see: Only their requests
- Staff see: All requests from all users
- Staff can: Approve/reject with optional messages

#### Admin Dashboard Component

**Purpose:** Provides system overview and statistics

**Component (`admin-dashboard.ts`):**
```typescript
export class AdminDashboardComponent implements OnInit {
  stats: OverviewStats | null = null;
  recentRequests: DocumentRequest[] = [];

  ngOnInit(): void {
    this.loadOverview();
  }

  loadOverview(): void {
    this.documentService.getOverview().subscribe({
      next: (response) => {
        this.stats = response.stats;              // Statistics
        this.recentRequests = response.recent_requests;  // Recent activity
      }
    });
  }
}
```

**Features:**
- System statistics (total requests, pending, approved, rejected)
- User statistics (total users, by role)
- Recent requests table
- Visual cards with color-coded status

**Statistics Displayed:**
- Total document requests
- Pending/Approved/Rejected counts
- Total users
- Users by role (requestors, staff, admins)
- Recent activity (last 10 requests)

### 3. ROUTING (Navigation)

#### `app.routes.ts`
```typescript
export const routes: Routes = [
  { path: '', redirectTo: '/login', pathMatch: 'full' },
  { path: 'login', component: LoginComponent },
  { path: 'register', component: RegisterComponent },
  { 
    path: 'dashboard', 
    component: DashboardComponent,
    canActivate: [authGuard]  // Protect route
  }
];
```

**What this means:**
- `/login` → Shows LoginComponent
- `/dashboard` → Shows DashboardComponent (only if authenticated)

#### Auth Guard
```typescript
export const authGuard: CanActivateFn = (route, state) => {
  const authService = inject(AuthService);
  
  if (authService.isAuthenticated()) {
    return true;  // Allow access
  }
  
  return inject(Router).createUrlTree(['/login']);  // Redirect to login
};
```

**What this does:**
- Checks if user is authenticated
- If yes → allow access
- If no → redirect to login

### 4. HTTP INTERCEPTOR (Add Token to Requests)

```typescript
export const authInterceptor: HttpInterceptorFn = (req, next) => {
  const token = localStorage.getItem('auth_token');
  
  if (token) {
    req = req.clone({
      setHeaders: {
        Authorization: `Bearer ${token}`
      }
    });
  }
  
  return next(req);
};
```

**What this does:**
- Runs before every HTTP request
- Adds `Authorization: Bearer <token>` header
- Backend uses this to identify user

---

## 🗄️ DATABASE STRUCTURE

### Users Table
```
id | name | email | password (hashed) | role | birthday | phone | created_at | updated_at
1  | John | john@ | $2y$10$...       | requestor | 2000-01-01 | 1234567890 | 2025-11-25 | 2025-11-25
2  | Jane | jane@ | $2y$10$...       | staff | 1995-05-15 | 0987654321 | 2025-11-25 | 2025-11-25
3  | Admin| admin@| $2y$10$...       | admin | 1990-03-20 | 1122334455 | 2025-11-25 | 2025-11-25
```

**Fields:**
- `id` - Primary key (auto-increment)
- `name` - User's full name
- `email` - Unique email address
- `password` - Hashed password (never plain text!)
- `role` - Enum: 'requestor', 'staff', 'admin'
- `birthday` - Date of birth
- `phone` - Phone number (unique)
- `created_at` / `updated_at` - Timestamps

### Document Requests Table
```
id | user_id | document_type | document_data (JSON) | document_status | staff_message | created_at | updated_at
1  | 1       | clearance     | {"name":"John",...}  | pending | NULL | 2025-11-25 | 2025-11-25
2  | 1       | certificate   | {"name":"John",...}  | approved | "All good!" | 2025-11-25 | 2025-11-26
3  | 2       | clearance     | {"name":"Jane",...}  | rejected | "Missing info" | 2025-11-25 | 2025-11-26
```

**Fields:**
- `id` - Primary key (auto-increment)
- `user_id` - Foreign key to `users.id` (who made the request)
- `document_type` - Type of document (e.g., "clearance", "certificate")
- `document_data` - JSON object with user info at time of request
  ```json
  {
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "1234567890",
    "birthday": "2000-01-01"
  }
  ```
  **Why JSON?** Captures user info at time of request, even if user updates profile later
- `document_status` - Enum: 'pending', 'approved', 'rejected'
  - **pending** - Awaiting staff review
  - **approved** - Staff approved the request
  - **rejected** - Staff rejected the request
- `staff_message` - Optional text message from staff (nullable, max 1000 chars)
  - Added when staff approves or rejects
  - Visible to requestor on their dashboard
  - Helps explain approval/rejection reason
- `created_at` / `updated_at` - Timestamps (auto-managed by Laravel)

**Ordering:**
- Requests are ordered by `created_at DESC` (newest first)
- Ensures most recent requests appear at the top

**Relationships:**
- `user_id` → Foreign key to `users.id`
- One user can have many document requests (One-to-Many)
- When user is deleted, their requests are deleted (CASCADE)

**Why store document_data as JSON?**
- Captures user info at time of request
- Even if user updates their profile later, request data stays the same
- Historical record of what was requested

---

## 🔐 AUTHENTICATION FLOW

### Registration Flow
```
1. User fills form → RegisterComponent
   - Enters: name, birthday, phone, email, password, password_confirmation
   
2. Component calls → AuthService.register()
   - Passes all form data
   
3. Service sends POST → http://localhost:8000/api/register
   - Body: { name, birthday, phone, email, password, password_confirmation }
   
4. Request goes through → Nginx (port 8000)
   - Receives HTTP request
   
5. Nginx forwards to → Laravel PHP-FPM (app container)
   - PHP processes the request
   
6. Laravel validates → Creates user → Returns token
   - Validates all fields
   - Hashes password (never store plain text!)
   - Sets default role to 'requestor'
   - Creates token using Laravel Sanctum
   
7. Frontend receives → Saves token → Updates state
   - Saves token to localStorage
   - Updates currentUser signal
   - Sets isAuthenticated to true
   
8. User redirected → Dashboard
   - Router navigates to /dashboard
   - Dashboard shows appropriate component based on role
```

### Login Flow
```
1. User enters credentials → LoginComponent
   - Email and password
   
2. Component calls → AuthService.login()
   - Passes email and password
   
3. Service sends POST → /api/login
   - Body: { email, password }
   
4. Laravel checks → email + password
   - Finds user by email
   - Uses Hash::check() to verify password
   - Compares plain password with hashed password in database
   
5. If correct → Generate token → Return user + token
   - Creates new Sanctum token
   - Returns user object (with role!)
   
6. Frontend saves → token in localStorage
   - Token persists across page refreshes
   
7. User redirected → Dashboard
   - Shows role-specific dashboard
```

### Authenticated Request Flow
```
1. Component calls → DocumentRequestService.getRequests()
   - Requestor wants to see their requests
   
2. Interceptor adds → Authorization: Bearer <token>
   - auth.interceptor.ts runs automatically
   - Reads token from localStorage
   - Adds header: Authorization: Bearer <token>
   
3. Request sent → /api/document-requests
   - GET request with Authorization header
   
4. Laravel middleware → auth:sanctum checks token
   - Validates token
   - Finds associated user
   - Attaches user to request object
   
5. If valid → Attach user to request → Continue to controller
   - $request->user() now returns the authenticated user
   
6. Controller uses → $request->user() to get authenticated user
   - Checks user role
   - Filters data based on role
   
7. Returns data → Frontend displays it
   - Requestors: Only their requests
   - Staff/Admin: All requests
```

### Complete Document Request Workflow

**Step 1: Requestor Creates Request**
```
Requestor Dashboard:
1. Clicks "New Request" button
2. Enters document type (e.g., "Clearance")
3. Submits form

Frontend:
- RequestorDashboardComponent.submitRequest()
- Calls DocumentRequestService.createRequest('Clearance')
- Sends POST /api/document-request
  Body: { document_type: 'Clearance' }

Backend:
- AuthController receives request
- Gets authenticated user via $request->user()
- Extracts user info (name, email, phone, birthday)
- Creates DocumentRequest:
  {
    user_id: 1,
    document_type: 'Clearance',
    document_data: { name: 'John', email: 'john@...', ... },
    document_status: 'pending'
  }
- Saves to database
- Returns created request

Frontend:
- Receives response
- Refreshes request list
- Shows new request with "pending" status
```

**Step 2: Staff Reviews Request**
```
Staff Dashboard:
1. Logs in as staff user
2. Sees ALL pending requests
3. Clicks on a request to see details

Frontend:
- StaffDashboardComponent loads
- Calls DocumentRequestService.getRequests()
- Sends GET /api/document-requests

Backend:
- DocumentRequestController.index()
- Checks: $user->isRequestor() → false (is staff)
- Returns ALL requests (not filtered by user_id)
- Includes user relationship (who made the request)

Frontend:
- Displays all requests
- Shows requester info (name, email, phone, birthday)
- Shows request details
```

**Step 3: Staff Approves/Rejects (With Modal)**
```
Staff Dashboard:
1. Staff clicks "Approve" or "Reject" button on a pending request
2. Modal dialog opens
3. Staff can optionally enter a message in textarea
4. Staff clicks "Approve" or "Reject" button in modal
5. Action is processed

Frontend (Modal Flow):
- StaffDashboardComponent.openApproveModal(requestId)
  - Sets currentRequestId
  - Sets actionType = 'approve'
  - Shows modal (showModal = true)
  
- Staff enters optional message in textarea
  - Two-way binding: [(ngModel)]="message"
  
- Staff clicks "Approve" in modal
  - StaffDashboardComponent.submitAction()
  - Calls DocumentRequestService.approveRequest(id, message)
  - Sends POST /api/document-request/approve
    Body: { 
      document_request_id: 1, 
      message: "All good!" // or null if empty
    }

Backend:
- DocumentRequestController.approve()
- Middleware checks: role:staff (only staff can access)
- Controller checks: $user->isStaff() → true
- Validates: document_request_id (required), message (optional, max 1000 chars)
- Finds request by ID
- Updates database:
  {
    document_status: 'approved',
    staff_message: 'All good!' // or NULL if no message
  }
- Returns updated request with user relationship

Frontend:
- Receives success response
- Closes modal (showModal = false)
- Shows success message: "Request approved successfully"
- Refreshes request list (loadRequests())
- Status badge changes from "pending" to "approved" (green)
- Staff message appears below request details (if provided)
```

**Step 4: Requestor Sees Update**
```
Requestor Dashboard:
1. Requestor refreshes or navigates back
2. Sees updated status and staff message (if provided)

Frontend:
- RequestorDashboardComponent.loadRequests()
- Calls DocumentRequestService.getRequests()
- Sends GET /api/document-requests

Backend:
- DocumentRequestController.index()
- Checks: $user->isRequestor() → true
- Filters: WHERE user_id = 1 (only their requests)
- Orders by: created_at DESC (newest first)
- Returns filtered requests with staff_message field

Frontend:
- Displays requests ordered by newest first
- Shows status badge (pending/approved/rejected)
- If staff_message exists and status is not pending:
  - Displays staff message in a highlighted box
  - Shows message below request details
  - Helps requestor understand approval/rejection reason
- Displays requests (newest first)
- Status badge shows "approved" (green) or "rejected" (red)
- If staff_message exists and status is not pending:
  - Displays staff message below request details
  - Format: "Staff Message: [message text]"
- Requestor can see why request was approved/rejected
```

**Step 5: Admin Views Overview**
```
Admin Dashboard:
1. Admin logs in
2. Sees overview page automatically

Frontend:
- AdminDashboardComponent.loadOverview()
- Calls DocumentRequestService.getOverview()
- Sends GET /api/admin/overview

Backend:
- DocumentRequestController.overview()
- Middleware checks: role:admin (only admin can access)
- Controller checks: $user->isAdmin() → true
- Calculates statistics:
  - Total requests: COUNT(*)
  - Pending: COUNT(*) WHERE status = 'pending'
  - Approved: COUNT(*) WHERE status = 'approved'
  - Rejected: COUNT(*) WHERE status = 'rejected'
  - Total users: COUNT(*) FROM users
  - Users by role: COUNT(*) GROUP BY role
- Fetches recent requests (last 10)
- Returns stats + recent requests

Frontend:
- Displays statistics in cards
- Shows recent requests table
- Color-coded status indicators
```

---

## 👥 ROLE-BASED ACCESS CONTROL

### How It Works

**1. User Has Role (Database)**
```php
// In database
users.role = 'staff'  // or 'requestor' or 'admin'
```

**2. Backend Middleware Checks Role (Route Level)**
```php
// routes/api.php
Route::middleware(['auth:sanctum', 'role:staff'])->group(function () {
    Route::post('/document-request/approve', [DocumentRequestController::class, 'approve']);
    Route::post('/document-request/reject', [DocumentRequestController::class, 'reject']);
});
```
- **auth:sanctum** - Must be authenticated
- **role:staff** - Must have staff role
- If either fails → 403 Forbidden

**3. Backend Controller Checks Role (Method Level)**
```php
public function approve(Request $request) {
    $user = $request->user();
    
    // Double-check (defense in depth)
    if (!$user->isStaff()) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }
    
    // Validate input
    $request->validate([
        'document_request_id' => 'required|exists:document_requests,id',
        'message' => 'nullable|string|max:1000',  // Optional staff message
    ]);
    
    // Update request with status and optional message
    $documentRequest = DocumentRequest::find($request->document_request_id);
    $documentRequest->update([
        'document_status' => 'approved',
        'staff_message' => $request->input('message')  // Can be NULL
    ]);
    
    return response()->json([
        'message' => 'Document request approved successfully',
        'data' => $documentRequest->load('user:id,name,email')
    ]);
}

public function reject(Request $request) {
    $user = $request->user();
    
    if (!$user->isStaff()) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }
    
    $request->validate([
        'document_request_id' => 'required|exists:document_requests,id',
        'message' => 'nullable|string|max:1000',  // Optional staff message
    ]);
    
    $documentRequest = DocumentRequest::find($request->document_request_id);
    $documentRequest->update([
        'document_status' => 'rejected',
        'staff_message' => $request->input('message')  // Can be NULL
    ]);
    
    return response()->json([
        'message' => 'Document request rejected successfully',
        'data' => $documentRequest->load('user:id,name,email')
    ]);
}
```
- Even if route allows, controller verifies
- Prevents accidental access
- **Message parameter:** Optional, max 1000 characters
- **Message storage:** Saved to `staff_message` field in database
- **Message visibility:** Visible to requestors on their dashboard

**4. Backend Data Filtering (Same Endpoint, Different Data)**
```php
public function index(Request $request) {
    $user = $request->user();
    
    if ($user->isRequestor()) {
        // Requestors see only their requests
        $requests = DocumentRequest::where('user_id', $user->id)->get();
    } else {
        // Staff/Admin see all requests
        $requests = DocumentRequest::all();
    }
    
    return response()->json(['data' => $requests]);
}
```
- Same endpoint: `/api/document-requests`
- Different data based on role
- Requestor: Filtered by user_id
- Staff/Admin: All requests

**5. Frontend Checks Role (UI Level)**
```typescript
// dashboard.component.html
<app-requestor-dashboard *ngIf="authService.isRequestor()"></app-requestor-dashboard>
<app-staff-dashboard *ngIf="authService.isStaff()"></app-staff-dashboard>
<app-admin-dashboard *ngIf="authService.isAdmin()"></app-admin-dashboard>
```
- Shows different component based on role
- User only sees what they're allowed to see

**6. Frontend Service Methods**
```typescript
// auth.service.ts
isRequestor(): boolean {
  return this.currentUser()?.role === 'requestor';
}

isStaff(): boolean {
  return this.currentUser()?.role === 'staff';
}

isAdmin(): boolean {
  return this.currentUser()?.role === 'admin';
}
```
- Helper methods for easy role checking
- Used in templates and components

### Role Permissions Matrix

| Action | Requestor | Staff | Admin |
|--------|-----------|-------|-------|
| **Create document request** | ✅ | ❌ | ❌ |
| **View own requests** | ✅ | ❌ | ❌ |
| **View all requests** | ❌ | ✅ | ✅ |
| **Approve requests** | ❌ | ✅ | ❌ |
| **Reject requests** | ❌ | ✅ | ❌ |
| **View system overview** | ❌ | ❌ | ✅ |
| **View statistics** | ❌ | ❌ | ✅ |

### Security Layers

**Defense in Depth - Multiple Security Checks:**

1. **Route Middleware** - First line of defense
   ```php
   Route::middleware('role:staff')->group(...)
   ```

2. **Controller Check** - Second line of defense
   ```php
   if (!$user->isStaff()) return 403;
   ```

3. **Data Filtering** - Third line of defense
   ```php
   if ($user->isRequestor()) {
       // Only return their data
   }
   ```

4. **Frontend UI** - User experience (not security!)
   ```typescript
   *ngIf="authService.isStaff()"  // Hides UI, but backend still protects
   ```

**Important:** Frontend checks are for UX only! Backend must always validate.

### How to Change User Roles

**Method 1: Direct SQL**
```bash
docker exec db mysql -uroot -psql123 laravel -e \
  "UPDATE users SET role = 'staff' WHERE email = 'user@example.com';"
```

**Method 2: Laravel Tinker**
```bash
docker exec app php artisan tinker
```
```php
$user = User::where('email', 'user@example.com')->first();
$user->role = 'staff';
$user->save();
```

**Method 3: During Registration (Not Recommended for Production)**
```php
// In AuthController@register
'role' => $request->role ?? User::ROLE_REQUESTOR
```
- Could allow users to set their own role (security risk!)
- Only use in development

---

## 🔗 HOW EVERYTHING CONNECTS

### Complete Request Flow Example

**User clicks "Create Request" button:**

1. **Frontend (Angular)**
   ```typescript
   // UserComponent
   onSubmit() {
     this.documentService.createRequest('clearance')
       .subscribe(response => console.log(response));
   }
   ```

2. **Service Makes HTTP Request**
   ```typescript
   // DocumentRequestService
   createRequest(type: string) {
     return this.http.post('/api/document-request', {
       document_type: type
     });
   }
   ```

3. **Interceptor Adds Token**
   ```typescript
   // auth.interceptor.ts
   // Adds: Authorization: Bearer <token>
   ```

4. **Request Goes to Backend**
   ```
   Browser → localhost:8000/api/document-request
   ↓
   Nginx receives on port 80
   ↓
   Forwards to PHP-FPM (app container)
   ```

5. **Laravel Processes**
   ```php
   // routes/api.php
   Route::post('/document-request', [DocumentRequestController::class, 'store']);
   
   // Middleware runs
   auth:sanctum → Checks token → Attaches user
   
   // Controller executes
   public function store(Request $request) {
     $user = $request->user();  // Get authenticated user
     // Create document request
   }
   ```

6. **Database Saves**
   ```php
   DocumentRequest::create([
     'user_id' => $user->id,
     'document_type' => $request->document_type
   ]);
   // SQL: INSERT INTO document_requests ...
   ```

7. **Response Returns**
   ```php
   return response()->json($documentRequest, 201);
   // JSON: {"id": 1, "document_type": "clearance", ...}
   ```

8. **Frontend Receives**
   ```typescript
   .subscribe({
     next: (response) => {
       // Update UI
       this.requests.push(response);
     }
   });
   ```

---

## 🎯 KEY CONCEPTS SUMMARY

### 1. **Separation of Concerns**
- **Frontend** = UI, user interaction
- **Backend** = Business logic, data validation
- **Database** = Data storage

### 2. **HTTP Communication**
- Frontend sends HTTP requests (GET, POST, PUT, DELETE)
- Backend responds with JSON
- Stateless (each request is independent)

### 3. **Authentication**
- Token-based (JWT-like with Laravel Sanctum)
- Token stored in browser localStorage
- Sent with every request in Authorization header

### 4. **Dependency Injection**
- Angular automatically provides services
- No need to manually create instances
- Makes testing easier

### 5. **Reactive Programming**
- Observables handle async operations
- Subscribe to data streams
- Components react to data changes

### 6. **Middleware Pattern**
- Code that runs before/after requests
- Authentication, authorization, logging
- Chain of responsibility

---

## 📝 RECENT FEATURES ADDED

### 1. Role-Based Dashboards
- **Requestor Dashboard:** Create and view own document requests
- **Staff Dashboard:** Review, approve, and reject all requests with modal system
- **Admin Dashboard:** System overview with statistics

### 2. Document Request System
- Create requests with document type
- Automatic capture of user information (name, email, phone, birthday)
- Status tracking (pending → approved/rejected)
- **Staff messages:** Optional messages when approving/rejecting (max 1000 characters)
- **Ordered display:** Requests shown newest first (ordered by `created_at DESC`)
- **Staff message display:** Messages visible to both staff and requestors
- **Message storage:** Saved to `staff_message` field in database (nullable TEXT column)
- **Backend validation:** Message is optional, nullable, string, max 1000 characters

### 3. Enhanced Authentication
- Password confirmation validation
- Field-level error display
- Role included in user object
- Role helper methods in frontend
- Registration includes: name, birthday, phone, email, password, password_confirmation

### 4. Docker Improvements
- Non-root user in Angular container (fixes permission issues)
- Named volumes for node_modules persistence
- Entrypoint script for dependency management
- Proper file permissions for development

### 5. API Enhancements
- Role-based data filtering (same endpoint, different data)
- Staff-only approve/reject endpoints
- Admin-only overview endpoint
- **Optional staff messages:** Max 1000 characters
- **Ordered results:** Newest requests first
- **Staff message field:** Added to document_requests table

### 6. UI/UX Improvements
- **Modal system:** Professional modal dialogs for approve/reject
  - Opens when staff clicks Approve/Reject button
  - Includes textarea for optional message input
  - Prevents accidental clicks (must confirm action in modal)
  - Click outside modal or X button to close
  - Modal shows action type (Approve/Reject) in header
  - Submit button changes color based on action (green for approve, red for reject)
- **Optional messages:** Textarea for staff to add notes (max 1000 chars)
  - Message is optional (can be left blank)
  - Validated on backend (nullable, string, max 1000 characters)
  - Stored in `staff_message` database field
- **Message display:** Staff messages shown on approved/rejected requests
  - Visible to requestors on their dashboard
  - Visible to staff on staff dashboard
  - Displayed in highlighted box with border
  - Helps explain approval/rejection decisions
  - Only shown when status is not 'pending' and message exists
- **Better feedback:** Success/error messages with auto-dismiss (3 seconds)
- **Status badges:** Color-coded (pending=yellow, approved=green, rejected=red)
- **Ordered lists:** Newest requests appear first for better UX
  - Backend orders by `created_at DESC`
  - Ensures most recent requests are at the top
  - Applies to both requestor and staff dashboards

### 7. Database Schema Updates
- **staff_message field:** Added to document_requests table
  - Type: TEXT (nullable)
  - Stores optional messages from staff
  - Migration: `2025_11_26_000000_add_staff_message_to_document_requests_table.php`
- **Ordering:** All queries order by `created_at DESC` (newest first)

---

## 🚀 NEXT STEPS TO LEARN

1. **Try modifying code** - Change colors, add fields
2. **Add features** - Add a "delete request" button, add filters
3. **Debug** - Use browser DevTools, check Network tab
4. **Read Laravel docs** - https://laravel.com/docs
5. **Read Angular docs** - https://angular.dev
6. **Experiment** - Change role permissions, add new fields
7. **Test** - Try all three roles, see how data differs

---

## 🔍 DEBUGGING TIPS

### Check Network Requests
1. Open browser DevTools (F12)
2. Go to Network tab
3. Make a request (e.g., create document request)
4. See:
   - Request URL
   - Request headers (including Authorization)
   - Request body
   - Response status
   - Response data

### Check Backend Logs
```bash
docker logs app --tail 50        # Laravel logs
docker logs angular_app --tail 50  # Angular logs
docker logs webserver --tail 50   # Nginx logs
```

### Check Database
```bash
docker exec db mysql -uroot -psql123 laravel -e "SELECT * FROM users;"
docker exec db mysql -uroot -psql123 laravel -e "SELECT * FROM document_requests;"
```

### Common Issues

**Issue: "Unauthenticated" error**
- Check if token exists: `localStorage.getItem('auth_token')`
- Check if token is sent in headers (Network tab)
- Try logging out and back in

**Issue: "Unauthorized" error**
- Check user role in database
- Verify middleware is applied correctly
- Check controller role check

**Issue: Permission denied (file editing)**
- Files created by Docker container might be owned by root
- Fix: `chmod -R u+w frontend/src/`
- Or rebuild container with non-root user (already done)

**Issue: Changes not appearing**
- Hard refresh browser: Ctrl+Shift+R (Windows) or Cmd+Shift+R (Mac)
- Check if Angular detected changes (check logs)
- Restart container if needed: `docker-compose restart angular`

---

## ❓ COMMON QUESTIONS

**Q: Why Docker?**
A: Ensures everyone has the same environment. No "works on my machine" issues.

**Q: Why Nginx + PHP-FPM?**
A: Nginx handles HTTP efficiently, PHP-FPM processes PHP. They work together.

**Q: Why TypeScript?**
A: Adds type safety to JavaScript. Catches errors before runtime.

**Q: What is Observable?**
A: A stream of data over time. Like a promise, but can emit multiple values.

**Q: What is Dependency Injection?**
A: Instead of creating objects yourself, framework provides them. Makes code testable.

---

**Remember:** Programming is about breaking big problems into small pieces. Each file, each function, each line has a purpose. Read the code, understand what it does, then modify it to see what happens!

