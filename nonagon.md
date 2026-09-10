# Nonagon Project File Analysis

## Current architecture direction — 10 September 2026

This section records the current product requirements and architecture discussion. It describes the target platform, not completed functionality. The earlier Laravel analysis is preserved below as historical context.

### Products

| Product | Scope |
| --- | --- |
| Equipment.ng | Seamless equipment marketplace for buyers, sellers, equipment owners, renters and agents (middlemen). |
| Nonagon Insights | News, analysis and glossary managed through WordPress. |
| Equipment Learning (working name) | Modern learning through equipment breakdowns: equipment type → system → subsystem → component → lessons and assessments. Final name remains open. |
| Nonagon Project OSINT | Monitoring companies, assets, projects, tenders and related information, overlaid on a map with sources and update history. |
| Nonagon ERP | Equipment management linked to the marketplace, people management, project management, site management and procurement. |

### Platforms

| Platform | Products | Target stack |
| --- | --- | --- |
| Web — first | All products | HTML, CSS, JavaScript, plain PHP and MySQL, with PWA support. Insights uses WordPress. No Laravel, Next.js or React in the target custom application. |
| Desktop | Nonagon ERP | Flutter, with the user-proposed local PHP/MySQL backend, offline operation and internet synchronization. Local deployment topology remains undecided. |
| Android and iOS | Equipment.ng and Nonagon ERP | Flutter clients using the PHP backend API. ERP offline scope remains to be defined. |

Native Android and iOS now form part of the target roadmap, expanding the earlier platform plan. Web remains the first implementation priority. PWA installation and offline functionality require separate implementation.

### Marketplace users: Agent added

Supported participants are Buyer, Seller, Equipment Owner, Renter and **Agent (middleman)**. An agent connects parties and facilitates equipment sales or rentals on behalf of a buyer, seller, owner or renter.

Recommended identity design: these are overlapping marketplace capabilities, not mutually exclusive account types. One account can operate in several capacities and belong to multiple organizations. Organization administrator is a separate permission role from equipment owner.

Proposed agent workflow, subject to detailed design:

- Record the represented party and the scope of the agent's authorization.
- Support authorized introductions, listing management, enquiries and negotiations, with agent participation visible to the relevant parties.
- Keep the agent, represented party and actual equipment owner distinct; representation does not transfer ownership.
- Agent status alone grants no private ERP access or authority to accept contracts, confirm payments or transfer assets.
- Record agent involvement in transaction history. Commission model, payer, payout timing, verification and dispute handling remain undecided; no fee structure has been approved.

### Shared application architecture — proposal

Start with one modular plain-PHP backend, separating Marketplace, ERP, Learning and OSINT business modules. Share identity, organizations, memberships, permissions, equipment taxonomy, files and audit history. Keep WordPress in its own installation/database and integrate published editorial content through its REST API.

PHP-rendered web pages and a versioned JSON API should use the same business services. Flutter clients communicate through authenticated HTTPS API requests, not direct cloud MySQL connections. Separate presentation, business rules and PDO database access. Media/documents need file storage alongside MySQL; ingestion, notifications and synchronization need background processing.

Organization permissions belong to memberships, allowing one user to work for multiple organizations. Public OSINT observations, verified marketplace companies, generic learning definitions and private ERP records remain distinct even when linked through shared identifiers.

### ERP and marketplace integration

An ERP asset is a private operational record. A listing is an explicitly published selection of information. Publishing must not automatically expose internal costs, staff assignments, maintenance documents or other private records. Marketplace users can create standalone listings without an ERP subscription and optionally link listings to ERP assets.

Availability checks must account for project assignments, maintenance and rental reservations before confirming a booking online. Agent-mediated transactions follow the same authorization and availability rules as direct transactions.

### Offline ERP — decision still open

The user proposed Flutter with local PHP/MySQL. Two possible deployment models were discussed; neither has been selected:

1. **Independent computer:** Flutter with local SQLite, syncing through the PHP API to cloud MySQL. This was recommended for a single device and would revise the proposed local stack.
2. **Shared offline office/site:** Flutter clients connect to a local PHP/MySQL server over the site network; that server synchronizes with the cloud.

The unanswered question is whether each computer works independently or several computers share one local server during internet outages.

Synchronization needs offline-generated IDs, a durable pending-operation queue, safe retries, record versions, conflict detection, propagated deletions, attachment synchronization and rules for permissions revoked while offline. Notes can append; conflicting asset edits require resolution; stock movements should remain transactions. Purchase requests can be drafted offline, while rental and payment confirmation should initially require online access. Mobile/PWA offline scope and conflict-resolution responsibilities remain open.

### Learning, Insights and OSINT

WordPress owns articles and glossary content. Learning owns structured equipment breakdowns and learning progress, with links to glossary definitions and shared equipment categories. Generic learning models are distinct from individual customer assets.

OSINT needs source attribution, collection time, last verification, confidence, entity matching and change history before map presentation. An observation must not automatically become a verified company or ERP asset. Define "live" by source and refresh frequency rather than assuming continuous updates everywhere.

### Proposed delivery sequence

1. Shared identity, organizations, permissions, taxonomy, files, audit history and API conventions.
2. ERP web asset register, location hierarchy and maintenance records.
3. Marketplace web listings, agent representation, ERP publishing, enquiries and availability.
4. WordPress Insights integration, which can progress alongside ERP.
5. A bounded offline ERP pilot before expanding synchronization.
6. Flutter desktop and Android/iOS applications.
7. Structured Learning and OSINT workflows using established identifiers and taxonomy.

This sequence and backend design are recommendations from the discussion, not finalized implementation decisions. Existing Laravel code remains a reference for the plain-PHP rebuild. See PROJECT-ANALYSIS.md for the earlier workspace review.

References discussed: [Flutter offline-first architecture](https://docs.flutter.dev/app-architecture/design-patterns/offline-first), [SQLite deployment guidance](https://www.sqlite.org/whentouse.html), [WordPress REST API](https://developer.wordpress.org/plugins/rest-api/).

## Historical analysis — Executive Summary

Nonagon is a Laravel-based business platform with a role-aware auth flow, admin dashboard management screens, and a marketing/public landing experience. Its core features include user registration and login, admin vs. non-admin routing, equipment and maintenance management views, reporting dashboards, PWA support, and a static asset-heavy frontend built around Blade templates and CDN libraries.

Key capabilities:
- User authentication and password reset/email verification flows
- Role-based dashboard routing via `HomeController`
- Admin pages for equipment, users, maintenance, and reports
- Public marketing pages and branded landing experience
- PWA manifest/service worker support for offline behavior
- Database-backed sessions, cache, and queue configuration with migrations

Generated on: 2026-03-10

Scope: This catalog covers every file currently in this workspace except third-party dependency trees under `vendor/` and `node_modules/`. Those trees are external package code, pinned by `composer.lock` and `package-lock.json`.

## System Overview

- Framework: Laravel 12 (PHP 8.2) with Laravel UI auth scaffolding.
- Frontend approach: mostly server-rendered Blade + static CSS/JS/CDN assets; Vite packages are installed but current views rely on direct CDN and `public/assets` files.
- Role flow: `HomeController` sends admin users to `resources/views/admin/index.blade.php` and others to `resources/views/home.blade.php`.
- PWA layer: `silviolleite/laravelpwa` with manifest config in `config/laravelpwa.php`, meta template override in `resources/views/vendor/laravelpwa/meta.blade.php`, and runtime worker in `public/serviceworker.js`.
- Data defaults: config defaults to database-backed `sessions`, `cache`, and `queue`, so migrations for those tables are mandatory in DB-backed environments.

## High-Level Dependencies and Runtime Requirements

- PHP: 8.2+
- Composer dependencies: install from `composer.lock`
- Node/npm tooling: install from `package-lock.json` if using Vite build scripts
- Database: sqlite or mysql credentials set in `.env`; run `php artisan migrate`
- Web server: Apache rewrite support for `public/.htaccess` behavior
- Writable paths: `storage/` and `bootstrap/cache/`
- CDN/network at runtime for Bootstrap, jQuery, DataTables, Chart.js, OwlCarousel, Font Awesome, Ionicons (as currently coded in Blade templates)

## File-by-File Catalog

- `.editorconfig` | Function: Editor formatting defaults (UTF-8, LF, indentation). | Relationship: Applies to all source files and markdown files. | Dependencies/Requirements: Requires editor/IDE support for EditorConfig.
- `.env` | Function: Environment-specific runtime configuration with secrets. | Relationship: Read by Laravel config files via env() calls. | Dependencies/Requirements: Requires valid DB/cache/session/mail credentials; values must stay private.
- `.env.example` | Function: Template of required environment keys for onboarding. | Relationship: Documents keys consumed by config/*.php. | Dependencies/Requirements: Requires copying to .env and filling deployment-specific values.
- `.gitattributes` | Function: Git normalization and diff hints for core file types. | Relationship: Improves version-control behavior for Blade/PHP/CSS/Markdown. | Dependencies/Requirements: Requires Git clients respecting attributes.
- `.gitignore` | Function: Excludes secrets and generated artifacts from version control. | Relationship: Protects .env, vendor, node_modules, logs, build outputs. | Dependencies/Requirements: Requires keeping ignore rules aligned with project tooling.
- `.vscode\sftp.json` | Function: VS Code SFTP deployment profile placeholder. | Relationship: Used only by developers using the SFTP extension. | Dependencies/Requirements: Requires valid host/credential values before use.
- `app\Http\Controllers\Auth\ConfirmPasswordController.php` | Function: Prompts password re-confirmation for sensitive actions. | Relationship: Connected to password.confirm route. | Dependencies/Requirements: Requires authenticated session and auth middleware.
- `app\Http\Controllers\Auth\ForgotPasswordController.php` | Function: Sends password reset links using framework trait. | Relationship: Connected to password.email route from Auth::routes(). | Dependencies/Requirements: Requires mail setup and password_reset_tokens table.
- `app\Http\Controllers\Auth\LoginController.php` | Function: Handles login/logout via AuthenticatesUsers trait. | Relationship: Used by Auth::routes() generated login endpoints. | Dependencies/Requirements: Requires session/auth middleware and users provider.
- `app\Http\Controllers\Auth\RegisterController.php` | Function: Handles registration validation and user creation. | Relationship: Creates App\Models\User with default role user. | Dependencies/Requirements: Requires users table schema including role column.
- `app\Http\Controllers\Auth\ResetPasswordController.php` | Function: Processes password reset submissions via framework trait. | Relationship: Linked to password.update route and login redirect. | Dependencies/Requirements: Requires reset tokens and users provider.
- `app\Http\Controllers\Auth\VerificationController.php` | Function: Email verification flow via VerifiesEmails trait. | Relationship: Uses signed/throttle middleware on verify/resend actions. | Dependencies/Requirements: Requires email verification routes and mail transport.
- `app\Http\Controllers\Controller.php` | Function: Base controller with authorization/validation traits. | Relationship: Parent for HomeController and Auth controllers. | Dependencies/Requirements: Requires Laravel routing/controller pipeline.
- `app\Http\Controllers\HomeController.php` | Function: Post-login home routing logic with role-based view switch. | Relationship: Reads auth()->user()->role; returns home or admin.index blade. | Dependencies/Requirements: Requires authenticated user and role column in users table.
- `app\Models\User.php` | Function: Authenticatable user model with fillable role and hashed casts. | Relationship: Used by auth provider, registration flow, session ownership. | Dependencies/Requirements: Requires users table and role field migration.
- `app\Providers\AppServiceProvider.php` | Function: Application-level service provider scaffold. | Relationship: Registered in bootstrap/providers.php and services manifest. | Dependencies/Requirements: No custom requirements until bindings are added.
- `artisan` | Function: Laravel CLI entry point for artisan commands. | Relationship: Bootstraps bootstrap/app.php and vendor autoloading. | Dependencies/Requirements: Requires PHP CLI and vendor dependencies installed.
- `bootstrap\app.php` | Function: Main Laravel application bootstrap and routing registration. | Relationship: Loads routes/web.php and routes/console.php; defines health endpoint. | Dependencies/Requirements: Requires framework packages from composer install.
- `bootstrap\cache\.gitignore` | Function: Keeps bootstrap cache directory in repo without cached payloads. | Relationship: Works with generated bootstrap/cache/*.php manifests. | Dependencies/Requirements: No runtime requirement; hygiene for deployments.
- `bootstrap\cache\packages.php` | Function: Generated package discovery manifest. | Relationship: Maps composer packages to service providers (UI, Tinker, PWA). | Dependencies/Requirements: Requires regeneration when dependencies change (artisan package:discover).
- `bootstrap\cache\services.php` | Function: Generated service container/provider manifest. | Relationship: Caches eager/deferred providers used during boot. | Dependencies/Requirements: Requires clearing/rebuilding cache after provider changes.
- `bootstrap\providers.php` | Function: Registers application service providers. | Relationship: Currently points to App\Providers\AppServiceProvider only. | Dependencies/Requirements: Requires provider classes to exist and be autoloadable.
- `composer.json` | Function: Primary PHP dependency and script manifest. | Relationship: Defines Laravel 12, laravel/ui auth scaffolding, laravelpwa package. | Dependencies/Requirements: Requires PHP ^8.2 and Composer install/update workflows.
- `composer.lock` | Function: Pinned PHP dependency graph for reproducible installs. | Relationship: Locks transitive packages used by Laravel runtime/test stack. | Dependencies/Requirements: Requires Composer to honor lockfile during install.
- `config\app.php` | Function: Core application settings (name, env, URL, locale, maintenance). | Relationship: Consumed globally through config() and framework bootstrapping. | Dependencies/Requirements: Requires APP_KEY and environment values in .env.
- `config\auth.php` | Function: Auth guards/providers/password reset configuration. | Relationship: Connects auth system to App\Models\User and session guard. | Dependencies/Requirements: Requires users table and auth routes/controllers.
- `config\cache.php` | Function: Cache backend configuration; default is database driver. | Relationship: Depends on cache/cache_locks tables from migrations. | Dependencies/Requirements: Requires cache tables when CACHE_STORE=database.
- `config\database.php` | Function: Database connection definitions and migration repository settings. | Relationship: Used by Eloquent model, sessions, cache, queue when DB-backed. | Dependencies/Requirements: Requires selected DB driver/credentials and created schema.
- `config\filesystems.php` | Function: Local/public/S3 disk definitions and storage symlink mapping. | Relationship: Used by Storage facade and file upload flows. | Dependencies/Requirements: Requires writable storage paths and optional storage:link.
- `config\laravelpwa.php` | Function: PWA manifest metadata, icon/splash path mapping, shortcuts. | Relationship: Feeds @laravelPWA blade directive and vendor PWA meta template. | Dependencies/Requirements: Requires matching files under public/images/icons.
- `config\logging.php` | Function: Log channel definitions and defaults. | Relationship: Writes runtime issues to storage/logs/laravel.log by default. | Dependencies/Requirements: Requires writable storage/logs directory.
- `config\mail.php` | Function: Mailer transports and global sender defaults. | Relationship: Used by password reset and other mail notifications. | Dependencies/Requirements: Requires valid MAIL_* settings for non-log transport.
- `config\queue.php` | Function: Queue connection definitions; default database driver. | Relationship: Relies on jobs/job_batches/failed_jobs tables. | Dependencies/Requirements: Requires queue tables and worker process for async handling.
- `config\services.php` | Function: Third-party integration credential placeholders. | Relationship: Referenced by package integrations (SES, Slack, etc.). | Dependencies/Requirements: Requires env credentials only when integrations are enabled.
- `config\session.php` | Function: Session storage/cookie configuration; default database sessions. | Relationship: Works with sessions table migration and middleware stack. | Dependencies/Requirements: Requires sessions table when SESSION_DRIVER=database.
- `database\.gitignore` | Function: Keeps database directory while ignoring volatile sqlite backups. | Relationship: Works with local sqlite file storage strategy. | Dependencies/Requirements: No runtime dependency.
- `database\database.sqlite` | Function: Local SQLite database file for development/runtime. | Relationship: Used when DB_CONNECTION=sqlite; consumed by migrations, sessions, cache, queue if configured. | Dependencies/Requirements: Requires writable file permissions.
- `database\factories\UserFactory.php` | Function: Factory blueprint for creating fake users in tests/seeders. | Relationship: Used by DatabaseSeeder and potential tests. | Dependencies/Requirements: Requires Faker and users table schema.
- `database\migrations\0001_01_01_000000_create_users_table.php` | Function: Creates users, password_reset_tokens, and sessions tables. | Relationship: Supports auth, password reset, and DB session driver. | Dependencies/Requirements: Requires running php artisan migrate.
- `database\migrations\0001_01_01_000001_create_cache_table.php` | Function: Creates cache and cache_locks tables. | Relationship: Supports database cache driver defaults. | Dependencies/Requirements: Requires migration execution before using DB cache.
- `database\migrations\0001_01_01_000002_create_jobs_table.php` | Function: Creates jobs, job_batches, and failed_jobs tables. | Relationship: Supports database queue driver defaults. | Dependencies/Requirements: Requires migration execution before queue workers run.
- `database\migrations\2025_07_31_114311_add_role_to_users_table.php` | Function: Adds role column to users for authorization branching. | Relationship: Read by HomeController and set by RegisterController. | Dependencies/Requirements: Requires running migration after base users table exists.
- `database\seeders\DatabaseSeeder.php` | Function: Seeds a default test user record. | Relationship: Invokes UserFactory and App\Models\User. | Dependencies/Requirements: Requires migrated users table and DB connection.
- `LICENSE` | Function: Project license text (MIT template from Laravel). | Relationship: Legal metadata for repository distribution. | Dependencies/Requirements: No runtime dependency.
- `package.json` | Function: Node tooling manifest for frontend build/dev scripts. | Relationship: Defines Vite/Tailwind/Bootstrap/Sass toolchain dependencies. | Dependencies/Requirements: Requires Node.js + npm to run build/dev scripts.
- `package-lock.json` | Function: Pinned npm dependency graph for deterministic frontend installs. | Relationship: Locks versions used by Vite and related tooling. | Dependencies/Requirements: Requires npm install using this lockfile.
- `phpunit.xml` | Function: PHPUnit suite and testing environment overrides. | Relationship: Configures in-memory sqlite and array cache/session for tests. | Dependencies/Requirements: Requires phpunit binary from composer dependencies.
- `public\.htaccess` | Function: Apache rewrite rules routing all requests to public/index.php. | Relationship: Enables clean URLs and auth headers forwarding. | Dependencies/Requirements: Requires Apache mod_rewrite/mod_negotiation enabled.
- `public\assets\css\admin.css` | Function: Base visual system for admin dashboard components. | Relationship: Loaded by layouts/admin.blade.php and inherited by admin pages. | Dependencies/Requirements: Requires matching class names in admin blade templates.
- `public\assets\css\auth.css` | Function: Styling for login/register/password pages. | Relationship: Loaded by layouts/app.blade.php. | Dependencies/Requirements: Requires auth blade markup class structure.
- `public\assets\css\equipment-table.css` | Function: Custom DataTable controls and table styling. | Relationship: Loaded in admin equipment/users views. | Dependencies/Requirements: Requires DataTables markup/classes and JS initialization.
- `public\assets\css\maintenance.css` | Function: Maintenance board/table status and accordion styling. | Relationship: Loaded in maintenance and reports pages. | Dependencies/Requirements: Requires task table and badge class names from blades.
- `public\assets\css\master.css` | Function: Primary marketing-site stylesheet for landing experiences. | Relationship: Loaded by welcome.blade.php and second.blade.php; references multiple background images. | Dependencies/Requirements: Requires public/assets/img assets and Bootstrap 4 markup patterns.
- `public\assets\css\reports.css` | Function: Report-page specific chart and card styling. | Relationship: Loaded by admin/reports.blade.php. | Dependencies/Requirements: Requires report card/chart markup IDs and classes.
- `public\assets\css\responsive.css` | Function: Responsive overrides for admin and marketing pages. | Relationship: Loaded by layouts/admin.blade.php and second.blade.php. | Dependencies/Requirements: Requires base styles from admin.css/master.css.
- `public\assets\img\1.jpg` | Function: Equipment sample photo #1. | Relationship: Used in admin equipment cards. | Dependencies/Requirements: Requires static file serving from public/.
- `public\assets\img\10.jpg` | Function: Additional stock photo asset. | Relationship: Currently not referenced by first-party blades/CSS. | Dependencies/Requirements: Optional; can be used for future cards/carousels.
- `public\assets\img\11.jpg` | Function: Additional stock photo asset. | Relationship: Currently not referenced by first-party blades/CSS. | Dependencies/Requirements: Optional; can be used for future cards/carousels.
- `public\assets\img\12.jpg` | Function: Additional stock photo asset. | Relationship: Currently not referenced by first-party blades/CSS. | Dependencies/Requirements: Optional; can be used for future cards/carousels.
- `public\assets\img\2.jpg` | Function: Equipment/avatar photo #2. | Relationship: Used in admin equipment cards and users avatars; also master.css background set. | Dependencies/Requirements: Requires static file serving and matching blade references.
- `public\assets\img\3.jpg` | Function: Equipment sample photo #3. | Relationship: Used in admin equipment cards; also referenced by master.css. | Dependencies/Requirements: Requires static file serving.
- `public\assets\img\4.jpg` | Function: Equipment sample photo #4. | Relationship: Used in admin equipment cards; also referenced by master.css. | Dependencies/Requirements: Requires static file serving.
- `public\assets\img\5.jpg` | Function: Equipment sample photo #5. | Relationship: Used in admin equipment cards. | Dependencies/Requirements: Requires static file serving.
- `public\assets\img\6.jpg` | Function: Equipment sample photo #6. | Relationship: Used in admin equipment cards. | Dependencies/Requirements: Requires static file serving.
- `public\assets\img\7.jpg` | Function: Additional stock photo asset. | Relationship: Currently not referenced by first-party blades/CSS. | Dependencies/Requirements: Optional; can be used for future cards/carousels.
- `public\assets\img\8.jpg` | Function: Additional stock photo asset. | Relationship: Currently not referenced by first-party blades/CSS. | Dependencies/Requirements: Optional; can be used for future cards/carousels.
- `public\assets\img\9.jpg` | Function: Additional stock photo asset. | Relationship: Currently not referenced by first-party blades/CSS. | Dependencies/Requirements: Optional; can be used for future cards/carousels.
- `public\assets\img\avatar.png` | Function: Admin sidebar avatar icon. | Relationship: Used by layouts/admin.blade.php account card. | Dependencies/Requirements: Requires static file serving.
- `public\assets\img\companies\1.png` | Function: Partner logo tile image for company strip. | Relationship: Used by second.blade.php sponsor/company rows. | Dependencies/Requirements: Requires static file serving and repeated carousel markup.
- `public\assets\img\companies\10.png` | Function: Partner logo tile image for company strip. | Relationship: Used by second.blade.php sponsor/company rows. | Dependencies/Requirements: Requires static file serving and repeated carousel markup.
- `public\assets\img\companies\11.png` | Function: Partner logo tile image for company strip. | Relationship: Used by second.blade.php sponsor/company rows. | Dependencies/Requirements: Requires static file serving and repeated carousel markup.
- `public\assets\img\companies\12.png` | Function: Partner logo tile image for company strip. | Relationship: Used by second.blade.php sponsor/company rows. | Dependencies/Requirements: Requires static file serving and repeated carousel markup.
- `public\assets\img\companies\2.png` | Function: Partner logo tile image for company strip. | Relationship: Used by second.blade.php sponsor/company rows. | Dependencies/Requirements: Requires static file serving and repeated carousel markup.
- `public\assets\img\companies\3.png` | Function: Partner logo tile image for company strip. | Relationship: Used by second.blade.php sponsor/company rows. | Dependencies/Requirements: Requires static file serving and repeated carousel markup.
- `public\assets\img\companies\4.png` | Function: Partner logo tile image for company strip. | Relationship: Used by second.blade.php sponsor/company rows. | Dependencies/Requirements: Requires static file serving and repeated carousel markup.
- `public\assets\img\companies\5.png` | Function: Partner logo tile image for company strip. | Relationship: Used by second.blade.php sponsor/company rows. | Dependencies/Requirements: Requires static file serving and repeated carousel markup.
- `public\assets\img\companies\6.png` | Function: Partner logo tile image for company strip. | Relationship: Used by second.blade.php sponsor/company rows. | Dependencies/Requirements: Requires static file serving and repeated carousel markup.
- `public\assets\img\companies\7.png` | Function: Partner logo tile image for company strip. | Relationship: Used by second.blade.php sponsor/company rows. | Dependencies/Requirements: Requires static file serving and repeated carousel markup.
- `public\assets\img\companies\8.png` | Function: Partner logo tile image for company strip. | Relationship: Used by second.blade.php sponsor/company rows. | Dependencies/Requirements: Requires static file serving and repeated carousel markup.
- `public\assets\img\companies\9.png` | Function: Partner logo tile image for company strip. | Relationship: Used by second.blade.php sponsor/company rows. | Dependencies/Requirements: Requires static file serving and repeated carousel markup.
- `public\assets\img\dark.png` | Function: Dark logo variant. | Relationship: Used in landing nav and auth forms. | Dependencies/Requirements: Requires static file serving.
- `public\assets\img\env-left.png` | Function: Decorative envelope asset (left). | Relationship: Referenced by master.css newsletter visuals. | Dependencies/Requirements: Requires CSS path resolution from master.css.
- `public\assets\img\env-right.png` | Function: Decorative envelope asset (right). | Relationship: Referenced by master.css newsletter visuals. | Dependencies/Requirements: Requires CSS path resolution from master.css.
- `public\assets\img\favicon.png` | Function: PNG favicon used in HTML heads. | Relationship: Referenced by layouts and landing pages. | Dependencies/Requirements: Requires static file serving.
- `public\assets\img\frame\1.png` | Function: Step illustration image for How-it-works section. | Relationship: Used by second.blade.php journey cards. | Dependencies/Requirements: Requires static file serving.
- `public\assets\img\frame\2.png` | Function: Step illustration image for How-it-works section. | Relationship: Used by second.blade.php journey cards. | Dependencies/Requirements: Requires static file serving.
- `public\assets\img\frame\3.png` | Function: Step illustration image for How-it-works section. | Relationship: Used by second.blade.php journey cards. | Dependencies/Requirements: Requires static file serving.
- `public\assets\img\greybg.png` | Function: Background texture asset for marketing sections. | Relationship: Referenced by public/assets/css/master.css. | Dependencies/Requirements: Requires CSS path resolution from master.css.
- `public\assets\img\light.png` | Function: Light logo variant. | Relationship: Used in admin sidebar and second.blade footer. | Dependencies/Requirements: Requires static file serving.
- `public\assets\img\truck.png` | Function: Hero illustration for landing page. | Relationship: Used by welcome.blade.php hero section. | Dependencies/Requirements: Requires static file serving.
- `public\assets\img\user.jpg` | Function: Sample user photo in landing top bar. | Relationship: Used in welcome.blade.php. | Dependencies/Requirements: Requires static file serving.
- `public\assets\img\why\heart.png` | Function: Extra why-section icon asset. | Relationship: Currently not referenced by first-party blades/CSS. | Dependencies/Requirements: Optional; reserved for future UI usage.
- `public\assets\img\why\main.png` | Function: Primary Why QuipDeck visual. | Relationship: Used by second.blade.php why section. | Dependencies/Requirements: Requires static file serving.
- `public\assets\img\why\small.png` | Function: Secondary Why QuipDeck visual. | Relationship: Used by second.blade.php why section. | Dependencies/Requirements: Requires static file serving.
- `public\favicon.ico` | Function: Browser favicon asset. | Relationship: Referenced by browser default favicon lookup and potentially HTML heads. | Dependencies/Requirements: No runtime dependency beyond static file serving.
- `public\images\icon\quote.png` | Function: Quote icon image used in testimonials block. | Relationship: Referenced in second.blade.php quote marker. | Dependencies/Requirements: Requires static file serving.
- `public\images\icons\icon-128x128.png` | Function: PWA app icon size variant. | Relationship: Mapped in config/laravelpwa.php and cached in public/serviceworker.js. | Dependencies/Requirements: Requires laravelpwa manifest generation and browser PWA support.
- `public\images\icons\icon-144x144.png` | Function: PWA app icon size variant. | Relationship: Mapped in config/laravelpwa.php and cached in public/serviceworker.js. | Dependencies/Requirements: Requires laravelpwa manifest generation and browser PWA support.
- `public\images\icons\icon-152x152.png` | Function: PWA app icon size variant. | Relationship: Mapped in config/laravelpwa.php and cached in public/serviceworker.js. | Dependencies/Requirements: Requires laravelpwa manifest generation and browser PWA support.
- `public\images\icons\icon-192x192.png` | Function: PWA app icon size variant. | Relationship: Mapped in config/laravelpwa.php and cached in public/serviceworker.js. | Dependencies/Requirements: Requires laravelpwa manifest generation and browser PWA support.
- `public\images\icons\icon-384x384.png` | Function: PWA app icon size variant. | Relationship: Mapped in config/laravelpwa.php and cached in public/serviceworker.js. | Dependencies/Requirements: Requires laravelpwa manifest generation and browser PWA support.
- `public\images\icons\icon-512x512.png` | Function: PWA app icon size variant. | Relationship: Mapped in config/laravelpwa.php and cached in public/serviceworker.js. | Dependencies/Requirements: Requires laravelpwa manifest generation and browser PWA support.
- `public\images\icons\icon-72x72.png` | Function: PWA app icon size variant. | Relationship: Mapped in config/laravelpwa.php and cached in public/serviceworker.js. | Dependencies/Requirements: Requires laravelpwa manifest generation and browser PWA support.
- `public\images\icons\icon-96x96.png` | Function: PWA app icon size variant. | Relationship: Mapped in config/laravelpwa.php and cached in public/serviceworker.js. | Dependencies/Requirements: Requires laravelpwa manifest generation and browser PWA support.
- `public\images\icons\splash-1125x2436.png` | Function: PWA splash screen image for specific device resolutions. | Relationship: Mapped in config/laravelpwa.php and emitted by vendor laravelpwa meta blade. | Dependencies/Requirements: Requires correct file paths and iOS/standalone launch behavior.
- `public\images\icons\splash-1242x2208.png` | Function: PWA splash screen image for specific device resolutions. | Relationship: Mapped in config/laravelpwa.php and emitted by vendor laravelpwa meta blade. | Dependencies/Requirements: Requires correct file paths and iOS/standalone launch behavior.
- `public\images\icons\splash-1242x2688.png` | Function: PWA splash screen image for specific device resolutions. | Relationship: Mapped in config/laravelpwa.php and emitted by vendor laravelpwa meta blade. | Dependencies/Requirements: Requires correct file paths and iOS/standalone launch behavior.
- `public\images\icons\splash-1536x2048.png` | Function: PWA splash screen image for specific device resolutions. | Relationship: Mapped in config/laravelpwa.php and emitted by vendor laravelpwa meta blade. | Dependencies/Requirements: Requires correct file paths and iOS/standalone launch behavior.
- `public\images\icons\splash-1668x2224.png` | Function: PWA splash screen image for specific device resolutions. | Relationship: Mapped in config/laravelpwa.php and emitted by vendor laravelpwa meta blade. | Dependencies/Requirements: Requires correct file paths and iOS/standalone launch behavior.
- `public\images\icons\splash-1668x2388.png` | Function: PWA splash screen image for specific device resolutions. | Relationship: Mapped in config/laravelpwa.php and emitted by vendor laravelpwa meta blade. | Dependencies/Requirements: Requires correct file paths and iOS/standalone launch behavior.
- `public\images\icons\splash-2048x2732.png` | Function: PWA splash screen image for specific device resolutions. | Relationship: Mapped in config/laravelpwa.php and emitted by vendor laravelpwa meta blade. | Dependencies/Requirements: Requires correct file paths and iOS/standalone launch behavior.
- `public\images\icons\splash-640x1136.png` | Function: PWA splash screen image for specific device resolutions. | Relationship: Mapped in config/laravelpwa.php and emitted by vendor laravelpwa meta blade. | Dependencies/Requirements: Requires correct file paths and iOS/standalone launch behavior.
- `public\images\icons\splash-750x1334.png` | Function: PWA splash screen image for specific device resolutions. | Relationship: Mapped in config/laravelpwa.php and emitted by vendor laravelpwa meta blade. | Dependencies/Requirements: Requires correct file paths and iOS/standalone launch behavior.
- `public\images\icons\splash-828x1792.png` | Function: PWA splash screen image for specific device resolutions. | Relationship: Mapped in config/laravelpwa.php and emitted by vendor laravelpwa meta blade. | Dependencies/Requirements: Requires correct file paths and iOS/standalone launch behavior.
- `public\index.php` | Function: HTTP front controller entry point. | Relationship: Bootstraps Laravel app from bootstrap/app.php. | Dependencies/Requirements: Requires vendor autoload and writable storage runtime directories.
- `public\robots.txt` | Function: Search crawler policy file (currently unrestricted). | Relationship: Served directly by web server. | Dependencies/Requirements: No code dependency.
- `public\serviceworker.js` | Function: PWA service worker caching offline/assets and fetch fallback. | Relationship: Registered by resources/views/vendor/laravelpwa/meta.blade.php. | Dependencies/Requirements: Requires browser service worker support and cached asset paths to exist.
- `README.md` | Function: Default Laravel README plus project marker line. | Relationship: Documentation baseline for setup and framework links. | Dependencies/Requirements: Should be updated with project-specific setup.
- `resources\views\admin\equipment.blade.php` | Function: Equipment management page with tabs, modal, cards, and DataTable. | Relationship: Linked from /equipment route and extends admin layout; uses equipment-table.css. | Dependencies/Requirements: Requires jQuery + DataTables CDN and local equipment image assets.
- `resources\views\admin\index.blade.php` | Function: Admin overview dashboard with KPI cards and chart container. | Relationship: Rendered by HomeController for admin users; chart script initialized in layouts.admin when route is /home. | Dependencies/Requirements: Requires admin layout scripts/styles and role-based access logic.
- `resources\views\admin\maintenance.blade.php` | Function: Maintenance workflow UI with kanban/table sections and modal. | Relationship: Linked from /maintenance route and extends admin layout; uses maintenance.css. | Dependencies/Requirements: Requires Bootstrap 5 tabs/accordion and DataTables assets.
- `resources\views\admin\reports.blade.php` | Function: Reporting dashboard with tabs, chart, and quick report cards. | Relationship: Linked from /reports route; reuses maintenance.css plus reports.css and Chart.js. | Dependencies/Requirements: Requires chart script and optional DataTables assets.
- `resources\views\admin\users.blade.php` | Function: User/staff management page with metrics, modal, and DataTable UI. | Relationship: Linked from /users route; uses equipment-table.css and admin layout. | Dependencies/Requirements: Requires jQuery + DataTables and avatar image assets.
- `resources\views\auth\login.blade.php` | Function: Custom login form UI. | Relationship: Posts to route(login) from Auth::routes() and extends layouts.app. | Dependencies/Requirements: Requires LoginController and CSRF/session middleware.
- `resources\views\auth\passwords\confirm.blade.php` | Function: Password confirmation page before sensitive operations. | Relationship: Posts to password.confirm route. | Dependencies/Requirements: Requires auth middleware and confirm-password flow.
- `resources\views\auth\passwords\email.blade.php` | Function: Request password reset link page (custom styled). | Relationship: Posts to password.email route. | Dependencies/Requirements: Requires mail settings and password reset token storage.
- `resources\views\auth\passwords\reset.blade.php` | Function: Reset password submission form. | Relationship: Posts token + credentials to password.update route. | Dependencies/Requirements: Requires valid reset token and user email.
- `resources\views\auth\register.blade.php` | Function: Custom registration form UI. | Relationship: Posts to route(register); integrates with RegisterController validation. | Dependencies/Requirements: Requires users table and password confirmation fields.
- `resources\views\auth\verify.blade.php` | Function: Email verification notice page. | Relationship: Calls verification.resend route. | Dependencies/Requirements: Requires verification routes and mail delivery.
- `resources\views\home.blade.php` | Function: Default authenticated user dashboard stub (non-admin). | Relationship: Returned by HomeController when role is not admin. | Dependencies/Requirements: Requires layouts.app and active session.
- `resources\views\layouts\admin.blade.php` | Function: Base admin dashboard layout with sidebar/nav, shared scripts, and chart bootstrap code. | Relationship: Extended by admin/* blades and yields local_css/local_js/content sections. | Dependencies/Requirements: Requires admin.css/responsive.css, jQuery, Bootstrap, Chart.js, Ionicons.
- `resources\views\layouts\app.blade.php` | Function: Base layout for auth/basic pages with auth.css and Bootstrap 5. | Relationship: Extended by auth views and home/offline pages via @extends. | Dependencies/Requirements: Requires public/assets/css/auth.css and shared assets.
- `resources\views\second.blade.php` | Function: Primary marketing landing page at /. | Relationship: Uses master.css/responsive.css, OwlCarousel, Font Awesome, many image assets. | Dependencies/Requirements: Requires CDN assets plus local images and @laravelPWA support.
- `resources\views\vendor\laravelpwa\meta.blade.php` | Function: PWA meta/manifest/service-worker registration snippet. | Relationship: Injected by @laravelPWA directive into landing pages. | Dependencies/Requirements: Requires config/laravelpwa.php and public/serviceworker.js.
- `resources\views\vendor\laravelpwa\offline.blade.php` | Function: Offline fallback page template for PWA mode. | Relationship: Referenced by service worker fallback cache path /offline. | Dependencies/Requirements: Requires route provided by laravelpwa package.
- `resources\views\welcome.blade.php` | Function: Older/simple public landing page variant. | Relationship: Served at /second route; uses master.css and @laravelPWA. | Dependencies/Requirements: Requires public assets and PWA package views.
- `routes\console.php` | Function: Defines artisan console-only closure command inspire. | Relationship: Loaded by bootstrap/app.php command routing. | Dependencies/Requirements: Requires artisan runtime only.
- `routes\web.php` | Function: HTTP routes for landing pages, admin pages, auth scaffolding, and /home controller. | Relationship: Maps URI paths to blade views or HomeController@index; enables Auth::routes(). | Dependencies/Requirements: Requires corresponding blade files and auth controllers.
- `storage\app\.gitignore` | Function: Maintains storage/app folder while excluding uploaded files. | Relationship: Pairs with storage disk configuration in config/filesystems.php. | Dependencies/Requirements: No runtime dependency.
- `storage\app\private\.gitignore` | Function: Keeps private disk path present in repo. | Relationship: Used by local filesystem disk root. | Dependencies/Requirements: Requires write permissions for private file storage.
- `storage\app\public\.gitignore` | Function: Keeps public disk path present in repo. | Relationship: Target of public storage symlink when using storage:link. | Dependencies/Requirements: Requires write permissions and optional symlink.
- `storage\framework\.gitignore` | Function: Framework cache/session/view directory retention rules. | Relationship: Controls which framework-generated artifacts remain ignored. | Dependencies/Requirements: No runtime dependency.
- `storage\framework\cache\.gitignore` | Function: Retains framework cache directory skeleton. | Relationship: Supports file cache fallback paths. | Dependencies/Requirements: Requires writable storage/framework/cache.
- `storage\framework\cache\data\.gitignore` | Function: Retains framework cache data directory skeleton. | Relationship: Used by file cache driver. | Dependencies/Requirements: Requires writable storage framework path.
- `storage\framework\sessions\.gitignore` | Function: Retains file-session directory skeleton. | Relationship: Used when SESSION_DRIVER=file. | Dependencies/Requirements: Requires writable session directory if file sessions enabled.
- `storage\framework\testing\.gitignore` | Function: Retains testing temp directory. | Relationship: Used during framework testing workflows. | Dependencies/Requirements: Requires writable path during tests.
- `storage\framework\views\.gitignore` | Function: Retains compiled blade cache directory. | Relationship: Holds generated PHP templates in production/dev. | Dependencies/Requirements: Requires writable view cache directory.
- `storage\framework\views\07371deec43948edd56e872db267dad2.php` | Function: Compiled Blade/template cache artifact generated by Laravel. | Relationship: Derived from:  | Dependencies/Requirements: C:\xampp\htdocs\nonagon\nonagon\vendor\laravel\framework\src\Illuminate\Foundation\Providers/../resources/exceptions/renderer/components/icons/moon.blade.php
- `storage\framework\views\2173d58636d238d4d3495a5b8b1b00b3.php` | Function: Compiled Blade/template cache artifact generated by Laravel. | Relationship: Derived from:  | Dependencies/Requirements: C:\xampp\htdocs\nonagon\nonagon\vendor\laravel\framework\src\Illuminate\Foundation\Providers/../resources/exceptions/renderer/show.blade.php
- `storage\framework\views\293e3c2fc0df6e4257afd4c2e31ca008.php` | Function: Compiled Blade/template cache artifact generated by Laravel. | Relationship: Derived from:  | Dependencies/Requirements: C:\xampp\htdocs\nonagon\nonagon\vendor\laravel\framework\src\Illuminate\Foundation\Providers/../resources/exceptions/renderer/components/trace-and-editor.blade.php
- `storage\framework\views\347aa5b9f4a528f2a0e9f830b8393381.php` | Function: Compiled Blade/template cache artifact generated by Laravel. | Relationship: Derived from:  | Dependencies/Requirements: C:\xampp\htdocs\nonagon\nonagon\vendor\laravel\framework\src\Illuminate\Foundation\Providers/../resources/exceptions/renderer/components/theme-switcher.blade.php
- `storage\framework\views\3a0b298decd24b5dd177bd8d1a137f11.php` | Function: Compiled Blade/template cache artifact generated by Laravel. | Relationship: Derived from:  | Dependencies/Requirements: C:\xampp\htdocs\nonagon\nonagon\vendor\laravel\framework\src\Illuminate\Foundation\Providers/../resources/exceptions/renderer/components/editor.blade.php
- `storage\framework\views\67a078477b669fd7d0b0462192c5d4bc.php` | Function: Compiled Blade/template cache artifact generated by Laravel. | Relationship: Derived from:  | Dependencies/Requirements: C:\xampp\htdocs\nonagon\nonagon\vendor\laravel\framework\src\Illuminate\Foundation\Providers/../resources/exceptions/renderer/components/header.blade.php
- `storage\framework\views\6b4e754c19e496695ff43dcc36be70ff.php` | Function: Compiled Blade/template cache artifact generated by Laravel. | Relationship: Derived from:  | Dependencies/Requirements: C:\xampp\htdocs\nonagon\nonagon\vendor\laravel\framework\src\Illuminate\Foundation\Providers/../resources/exceptions/renderer/components/icons/computer-desktop.blade.php
- `storage\framework\views\87334890320959b13ed203de11cac9aa.php` | Function: Compiled Blade/template cache artifact generated by Laravel. | Relationship: Derived from:  | Dependencies/Requirements: C:\xampp\htdocs\nonagon\nonagon\vendor\laravel\framework\src\Illuminate\Foundation\Providers/../resources/exceptions/renderer/components/icons/sun.blade.php
- `storage\framework\views\901472c7cb9bc069f0fc93b89b61aff1.php` | Function: Compiled Blade/template cache artifact generated by Laravel. | Relationship: Derived from:  | Dependencies/Requirements: C:\xampp\htdocs\nonagon\nonagon\vendor\laravel\framework\src\Illuminate\Foundation\Providers/../resources/exceptions/renderer/components/icons/chevron-up.blade.php
- `storage\framework\views\9c6a10f1b520f8b08c5bb9ed1b791b21.php` | Function: Compiled Blade/template cache artifact generated by Laravel. | Relationship: Derived from:  | Dependencies/Requirements: C:\xampp\htdocs\nonagon\nonagon\vendor\laravel\framework\src\Illuminate\Foundation\Providers/../resources/exceptions/renderer/components/icons/chevron-down.blade.php
- `storage\framework\views\b012e8b73ed70b7ab4731dabbfeaefa7.php` | Function: Compiled Blade/template cache artifact generated by Laravel. | Relationship: Derived from:  | Dependencies/Requirements: C:\xampp\htdocs\nonagon\nonagon\vendor\laravel\framework\src\Illuminate\Foundation\Providers/../resources/exceptions/renderer/components/card.blade.php
- `storage\framework\views\d5b06888a70338c7fca417fabd609b28.php` | Function: Compiled Blade/template cache artifact generated by Laravel. | Relationship: Derived from:  | Dependencies/Requirements: C:\xampp\htdocs\nonagon\nonagon\vendor\laravel\framework\src\Illuminate\Foundation\Providers/../resources/exceptions/renderer/components/navigation.blade.php
- `storage\framework\views\d83b30a3611f54896f4ca2cbbcade985.php` | Function: Compiled Blade/template cache artifact generated by Laravel. | Relationship: Derived from:  | Dependencies/Requirements: C:\xampp\htdocs\nonagon\nonagon\vendor\laravel\framework\src\Illuminate\Foundation\Providers/../resources/exceptions/renderer/components/context.blade.php
- `storage\framework\views\e2543d265decfe28bce66b053303af33.php` | Function: Compiled Blade/template cache artifact generated by Laravel. | Relationship: Derived from:  | Dependencies/Requirements: C:\xampp\htdocs\nonagon\nonagon\vendor\laravel\framework\src\Illuminate\Foundation\Providers/../resources/exceptions/renderer/components/layout.blade.php
- `storage\framework\views\f697041163ee4716ad80dcc5c06220d3.php` | Function: Compiled Blade/template cache artifact generated by Laravel. | Relationship: Derived from:  | Dependencies/Requirements: C:\xampp\htdocs\nonagon\nonagon\vendor\laravel\framework\src\Illuminate\Foundation\Providers/../resources/exceptions/renderer/components/trace.blade.php
- `storage\logs\.gitignore` | Function: Retains logs directory while ignoring rotating log files. | Relationship: Supports logging channels writing to storage/logs. | Dependencies/Requirements: Requires writable directory.
- `storage\logs\laravel.log` | Function: Runtime application log history. | Relationship: Contains historical errors (Vite manifest missing, DB/session connection issues, view/route syntax incidents). | Dependencies/Requirements: Requires log rotation and secret-safe handling in shared environments.
- `tests\Feature\ExampleTest.php` | Function: Smoke feature test ensuring / responds HTTP 200. | Relationship: Exercises routes/web.php root path and public landing blade. | Dependencies/Requirements: Requires test environment and app boot.
- `tests\TestCase.php` | Function: Base feature-test class extending Laravel test harness. | Relationship: Inherited by tests/Feature/*.php. | Dependencies/Requirements: Requires phpunit and Laravel testing bootstrap.
- `tests\Unit\ExampleTest.php` | Function: Placeholder unit test verifying true is true. | Relationship: Independent of app runtime. | Dependencies/Requirements: Requires phpunit runtime only.

## Notes From Current Runtime Artifacts

- `storage/logs/laravel.log` shows repeated historical failures for missing Vite manifest and database session/cache connectivity (including access denied and refused connections).
- `storage/framework/views/*.php` are compiled view caches, largely from Laravel exception renderer templates in vendor code, and should not be treated as source-of-truth templates.

## Suggested Maintenance Follow-Ups

- If Vite is intentionally unused, remove unused Vite expectations from old templates/config to avoid confusion.
- If DB-backed sessions/cache/queue stay enabled, ensure credentials are valid and migrations are always applied in each environment.
- Consider replacing CDN dependencies with bundled local assets for stronger offline and deployment predictability.
- `nonagon.md` | Function: This repository analysis document. | Relationship: Summarizes purpose, dependencies, and links for every workspace file except vendor/node_modules trees by design. | Dependencies/Requirements: Must be updated when files or architecture change.
