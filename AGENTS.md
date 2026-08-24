# DsJumpers - AI Agent Guidelines

## Project Overview

**DsJumpers** is a multi-tenant PHP web application for event/booking management with integrated payment processing, lead management, and operations tracking. It serves bounce house rental businesses and similar event management needs.

- **Type**: SaaS platform with multi-client architecture
- **Tech Stack**: PHP 7.4+, MySQL 5.7+, Composer, MAMP (dev environment)
- **Multi-language**: Spanish (es) and English (en)
- **Deployment**: Apache with .htaccess rewrite rules

## Architecture Overview

### Multi-Tenant Structure
- **Principal Database**: `evenuxne_principal` (credentials in `.env`)
  - Contains: `data_bases` table (client accounts), users, global settings
  - Each client has separate database name stored in `data_bases.nombre_db`
  
- **Client Databases**: Individual databases per client (e.g., `db_cliente_123`)
  - Contains: leads, payments, operations, products, customers, etc.
  - Selected at login via `$_SESSION['nombre_db']`

### API Architecture
Two main API entry points:
1. **`/api/index.php`** - Internal API with JWT authentication
   - Used by frontend JavaScript
   - Requires `Authorization: Bearer <token>` header
   - Decodes token using client-specific key from principal DB
   
2. **`/api-web/index.php`** - Web API for external integrations
   - Same JWT structure but with different validation
   - Used by public-facing forms, embeds, webhooks

### Session & Authentication Flow
1. User visits `index.php?company=COMPANY_NAME`
2. Frontend logs in via `login.php` → calls `POST /api/user_login`
3. Backend validates credentials, returns JWT token (stored in browser)
4. All subsequent requests use token in `Authorization` header
5. Database context (`DB_NAME`) set from token payload or session

## Key Files & Their Roles

| File | Purpose |
|------|---------|
| `config/config.php` | Global configuration, constants, .env loading |
| `config/database.php` | PDO database classes (`Database`, `DatabaseLogin`) |
| `api/functions.php` | Helper functions: email, image processing, PDF, translations |
| `api/handlers.php` | Request handlers for all API endpoints (5000+ lines) |
| `api/process_op.php` | Operation/lead processing logic |
| `index.php` | Login page & company selector |
| `login.php` | Authentication endpoint (form POST) |
| `valid_login.php` | Session validation helper |
| `idioma.php` | Translation/i18n loader |
| `.htaccess` | URL routing (company routing, 404 redirects) |

## Payment Integration

Two platforms supported:
- **Openpay**: `processpayment*.php` files use Openpay SDK
- **Square**: `processpayment_square*.php` files, Web Payments SDK on frontend

**Key classes**:
- `\Openpay\Data\Openpay` - Openpay SDK wrapper
- `\Square\SquareClient` - Square API client

**Payment tables**: `payments`, `payments_sale`, `folios` (sequential numbering)

## Common Development Patterns

### Database Connection Pattern
```php
include_once 'config/database.php';
$database = new Database(); // For client DB (requires DB_NAME defined)
// OR
$database = new DatabaseLogin(); // For principal DB (fixed DB_NAMEP)
$db = $database->getConnection();
```

### API Routing Pattern
```php
// In /api/index.php
$resource = $segments[1];  // e.g., 'login', 'leads', 'operations'
$id = $segments[2] ?? null;
$data = json_decode(file_get_contents("php://input"));
```

### Translation Pattern
```php
require 'idioma.php';
// $lang is auto-loaded (from URL param or session)
// $Traducciones array indexed by key, then language
echo Trd(123); // Translated string by ID
```

### Email Sending Pattern
```php
include_once 'api/functions.php';
$config = ['host' => '...', 'username' => '...', 'password' => '...'];
enviarEmail($config, $destinatario, $asunto, $html_body, $adjuntos, $binario, $filename);
```

### Image Upload & Processing
```php
// Upload endpoint: /ajax/upload.php
// Outputs filename without extension
// Auto-generates: normal AVIF, thumbnail AVIF, thumbnail JPG
// Uses: upload_Aws() to store in Cloudflare R2 (configured in .env)
```

## Environment Setup

### Prerequisites
1. MAMP installed (Apache 2.4, PHP 7.4+, MySQL 5.7+)
2. Two MySQL databases:
   - `evenuxne_principal` - system database (users, clients)
   - `evenuxne_XXXX` - per-client databases
3. Composer installed (`composer install`)
4. `.env` file with database and API credentials

### Required Environment Variables
```
SECRET_KEY          # JWT signing key
GOOGLE_API_KEY      # For Maps/geolocation
URL_BASE            # App base URL (e.g., http://localhost/DsJumpers)
URL_BASE_SALE       # Separate URL for sales module
host, db_name, username, password, port  # Principal DB credentials
endpoint, key, secret, publicurl  # Cloudflare R2 credentials
```

### Installation Steps
1. Clone repo to `/Applications/MAMP/htdocs/DsJumpers/`
2. Copy `.env.example` → `.env` and configure database
3. Run `composer install`
4. Import database schemas (check `/remove/` for old SQL files)
5. Start MAMP, visit `http://localhost/DsJumpers/?company=DEMO`

## Conventions & Best Practices

### Naming
- **Table names**: snake_case (e.g., `lead_detail`, `operation_master`)
- **Column names**: CamelCase (e.g., `IdLead`, `StartDateTime`, `TotalBT`)
- **PHP variables**: $camelCase for objects, $SCREAMING for constants
- **Functions**: snake_case (e.g., `generarThumbnailAVIF()`)

### Query Patterns
- Use prepared statements with bound parameters: `$stmt->execute([...])`
- Avoid string concatenation for SQL (SQL injection risk)
- Use `$db->lastInsertId()` for INSERT IDs
- Always define `define('DB_NAME', ...)` before instantiating `Database`

### Response Format
```json
{
  "status": "success|error",
  "message": "Human-readable message",
  "data": { /* payload */ }
}
```

### Error Handling
- Catch `PDOException` for database errors
- Use `http_response_code()` before JSON response
- Log errors to file (not shown to clients in production)

## Common Pitfalls & Solutions

| Issue | Cause | Solution |
|-------|-------|----------|
| "Error de conexión: SQLSTATE[HY000]" | `DB_NAME` not defined | Call `define('DB_NAME', ...)` before `new Database()` |
| Multi-client routing broken | `.htaccess` Base mismatch | Verify `RewriteBase` matches deployment path |
| Payment fails silently | SDK not initialized | Check `$_ENV` loading & Composer autoload |
| CORS errors on API calls | Missing headers | `/api-web/` sets `Access-Control-*` headers; `/api/` may not |
| Translation missing | Wrong `$lang` value | Ensure `idioma.php` loaded BEFORE `$Traducciones` used |
| Timezone issues | Server timezone ≠ Brazil | App sets `America/Mexico_City` globally; DB reads account timezone |

## Debugging Tips

1. **Enable error logging** in `config/config.php`:
   ```php
   error_reporting(E_ALL);
   ini_set('display_errors', 0);
   ini_set('log_errors', 1);
   ini_set('error_log', '/path/to/error.log');
   ```

2. **Check session data**:
   ```php
   var_dump($_SESSION['nombre_db'], $_SESSION['id_cliente']);
   ```

3. **Inspect JWT tokens** (use jwt.io or PHP):
   ```php
   $decoded = JWT::decode($token, new Key($key, 'HS256'));
   var_dump($decoded);
   ```

4. **Database query debugging**:
   ```php
   if (!$stmt->execute()) {
       var_dump($stmt->errorInfo());
   }
   ```

5. **Check logs**:
   - MAMP Apache: `/Applications/MAMP/logs/apache_error.log`
   - PHP error log: configured in `.env` or php.ini

## File Structure Key Paths

```
DsJumpers/
├── api/                    # Backend API handlers & functions
│   ├── index.php          # Main API entry point
│   ├── handlers.php       # Request handlers (LARGE FILE)
│   ├── functions.php      # Helper functions
│   └── process_op.php     # Operation logic
├── api-web/               # Public-facing API (embeds, webhooks)
│   └── index.php
├── ajax/                  # AJAX endpoints (upload, search, etc.)
├── config/
│   ├── config.php         # Main config & .env loading
│   └── database.php       # PDO wrapper classes
├── css/                   # Stylesheets
├── html/                  # Email templates (HTML, multi-language)
├── js/                    # Client-side JS
├── templates/             # HTML UI templates
├── vendor/                # Composer packages
├── .env                   # Environment secrets (DO NOT COMMIT)
├── .htaccess              # URL routing rules
├── composer.json          # Dependencies
├── idioma.php             # i18n system
├── login.php              # Auth endpoint
├── index.php              # Login page
└── [business-logic files] # leads.php, payments.php, operations.php, etc.
```

## Key Dependencies (Composer)

- `firebase/php-jwt` - JWT token generation/validation
- `dompdf/dompdf` - PDF generation for contracts, invoices
- `phpmailer/phpmailer` - Email sending
- `openpay/sdk` - Openpay payment integration
- `square/square` - Square payment integration
- `aws/aws-sdk-php` - Cloudflare R2 / AWS S3 storage
- `vlucas/phpdotenv` - Environment variable loading
- `google/auth` - Google API authentication

## When Adding Features

### New Database Table
1. Create migration SQL in `/remove/` folder (for reference)
2. Add to appropriate client DB
3. Update queries in relevant business logic files
4. Add translations for new labels (if UI-facing)

### New API Endpoint
1. Add handler function in `api/handlers.php`
2. Add route case in `api/index.php` switch statement
3. Include JWT validation check
4. Return JSON with standard response format
5. Handle `PDOException` with 500 status

### New Payment Platform
1. Copy `processpayment_square.php` as template
2. Add SDK initialization with credentials from `.env`
3. Create payment record in `payments` table
4. Update `account.Pay_platform` setting
5. Test with test/sandbox credentials first

### New Language/Translation
1. Add keys to `idioma.php` arrays
2. Use `Trd(ID)` function to retrieve in code
3. Test with URL parameter: `?lang=en` or `?lang=es`

## Useful Commands

```bash
# Install/update dependencies
composer install
composer update

# Check for security issues
composer audit

# Format code (if using PHP CS Fixer)
vendor/bin/php-cs-fixer fix src/

# Run tests (if added later)
vendor/bin/phpunit
```

## Performance Considerations

1. **Database indexes**: Check slow queries on large tables (`lead`, `payments`, `operation_master`)
2. **Image optimization**: Thumbnail generation is CPU-intensive; consider queuing
3. **Email delivery**: PHPMailer is synchronous; consider async via queue service
4. **Session storage**: Relying on PHP sessions; scale with sessions storage backend
5. **API rate limiting**: Not implemented; add if exposing to public clients

## Security Notes

1. **.env file**: Contains secrets; ensure `.gitignore` blocks it
2. **SQL Injection**: Always use prepared statements; never concatenate user input into queries
3. **XSS**: HTML output from database is NOT sanitized in some areas; review before user-facing changes
4. **JWT**: Signed with `SECRET_KEY`; rotate periodically
5. **Password hashing**: Uses `password_hash(..., PASSWORD_BCRYPT)` for operators; ensure salt is sufficient
6. **CORS**: Allows all origins (`*`); restrict in production

## Next Steps for Agents

When working on a task:
1. **Identify database context**: Does it use principal DB or client DB?
2. **Check authentication**: Does endpoint need JWT validation?
3. **Review translations**: Are new UI labels translated in `idioma.php`?
4. **Test multi-tenant**: Ensure feature works for different clients
5. **Validate payment flow**: If payment-related, test with sandbox credentials
6. **Document changes**: Update this file if adding new conventions

---

**Last Updated**: 2026-08-23  
**Created by**: GitHub Copilot Agent Initialization
