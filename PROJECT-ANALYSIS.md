# Nonagon workspace analysis

Reviewed: 10 September 2026. This is a source analysis before migration; application code and databases were not changed. Dependency trees and generated reference bundles are classified rather than audited line by line. Runtime behavior, mail delivery, and database contents have not been verified.

## Workspace and source of truth

| Location | Contents | Assessment |
| --- | --- | --- |
| `nonagon/` | Laravel app with installed PHP/Node dependencies, Git metadata and a SQLite file | Smaller UI-oriented implementation |
| `nonagon-main/` | Laravel source with expanded domain model, no installed vendor tree or bundled SQLite database | Richer implementation, but incomplete |
| `Equipment.ng/` | HTTrack-style website download with HTML, Next.js bundles, images and cache metadata | Reference website snapshot, not an editable Next.js application source tree |
| `.history/` | Editor history | Historical copies, not runtime source |
| `nonagon.md` | Earlier file catalog | Describes the smaller app; does not capture the expanded backend adequately |

The workspace root does not have an application entry point. The two Laravel directories must not be treated as interchangeable copies. No applicable AGENTS.md was found outside dependency and Git trees. The smaller repository had a clean Git status during review.

| First-party source category | `nonagon/` | `nonagon-main/` |
| --- | ---: | ---: |
| Models | 1 | 39 |
| Controllers, including auth | 8 | 17 |
| Blade views | 18 | 21 |
| Migration files | 4 | 42 |

Use `nonagon-main/` as the proposed functional baseline for conversion, while comparing shared templates/assets with `nonagon/`. This is based on its broader implementation, not a verified deployment history.

## Stack and application structure

Both applications declare PHP ^8.2, Laravel ^12.0, Laravel UI, Tinker and Laravel PWA. Frontend tooling declares Vite scripts, Laravel Vite integration, Bootstrap, Sass, Tailwind and Axios. Neither application package manifest declares React or Next.js.

Actual pages use server-rendered Blade, local CSS and images, inline JavaScript and CDN libraries. Marketing templates use Bootstrap 4, jQuery, OwlCarousel and icon libraries. Admin/auth pages use Bootstrap 5; dashboard pages additionally use DataTables and Chart.js. JavaScript and styles are embedded in several templates rather than consistently separated into files.

Request flow is `public/index.php` → Laravel bootstrap → routes/middleware → controllers/Eloquent → Blade layouts/pages. Laravel currently supplies routing, sessions, authentication, CSRF, validation, password hashing, database access, migrations, mail hooks, redirects and view rendering.

## Pages and feature readiness

| Feature | Source evidence | Current state |
| --- | --- | --- |
| Marketing pages | `/` renders `second`; `/second` renders `welcome` | Substantial existing markup/assets; some forms target `#`; newsletter refers to `handleFormSubmit` without a definition found in views |
| Login and registration | Standard scaffold in smaller app; custom AuthController in expanded app | Expanded registration creates organization, owner, default base/site/plant/unit and access assignment transactionally |
| Dashboard | HomeController and admin/index in both apps | Smaller app has `/home`; expanded app comments that route out; charts use sample arrays |
| Equipment | Expanded EquipmentController and equipment view | Listing, filters, organization scoping, location tree, creation, JSON detail/update exist; create/edit/delete resource methods are empty |
| Users | Admin users view; invitation methods in AuthController | UI largely sample data; export/share/filter/edit contain placeholder alerts; invitation flow incomplete |
| Maintenance | Maintenance view, controller, models and migrations | UI route serves a view directly; controller methods are not wired into web routes and contain defects |
| Reports | Reports view with Chart.js | Hardcoded chart series; no reporting query/export backend found in routes |
| Settings/profile/account | Three extra expanded-app templates and view routes | Heading-only placeholders; corresponding save endpoints are absent from web routes |
| Subscriptions/features | Organization feature helpers, models, migrations and middleware | Domain scaffolding exists; no complete subscription management workflow in routes |
| Password reset/email verification | Laravel auth traits and expanded verification routes | Framework-dependent; mail/runtime unverified; route/redirect inconsistencies remain |
| PWA | Config, meta templates, icons, serviceworker.js | Framework-generated manifest; worker paths need repair and subdirectory support |

## Data model

The smaller app contains users/roles and Laravel infrastructure migrations. The expanded app adds UUID-based business records. A direct copy of the smaller database cannot be assumed compatible with the expanded schema.

The location hierarchy is organization → base → site → plant → unit. Equipment references an organization and location hierarchy, plus category, subcategory, type and status. Location-user assignments determine operator access. Expanded registration uses role `owner`; older dashboard code distinguishes `admin` from other roles. Role behavior needs one consistent definition.

Additional schema groups include:

- Identity: users, invitations, email verification and password-reset records.
- Entitlements: subscription templates, feature registry, template features, organization features and subscriptions.
- Asset details: photos, part masters, assembly templates/items and equipment assemblies/items.
- Valuation/history: depreciation profiles, value snapshots/current values, events and audit logs.
- Maintenance: breakdown incidents, meters, schedules, work orders/tasks/attachments, maintenance logs, meter readings/summaries.
- Framework infrastructure: sessions, cache/locks, jobs/batches and failed jobs.

The number of migration files is not the number of tables: several migrations create multiple tables. Models and schemas express intended scope, not proof of implemented workflows. Existing production data and the active database engine remain unverified.

## Findings to account for during conversion

### Access control

1. **Dashboard-style routes are public.** The smaller app exposes equipment, maintenance, users and reports without route auth protection. The expanded app similarly exposes maintenance, users, reports, settings, profile and account; its intended enclosing auth group is commented out. A template failing for guests is not access control.
2. **Equipment listing can fail open for unassigned operators.** In expanded EquipmentController::index, when every location-ID set is empty, no location condition is added inside the nested query. Organization scoping remains, but the user can receive the organization's equipment rather than none.
3. **Search lacks equivalent location filtering.** Expanded search scopes to organization but does not apply the listing/detail location restrictions. Middleware only checks a specific equipment route parameter, which search has none of.
4. **Mutation authorization is inconsistent.** Store verifies organization/base and hierarchy, but does not explicitly check operator access to the selected location. Update checks the existing equipment, then accepts new location IDs validated only with global existence rules; it does not repeat store's hierarchy/organization checks for the destination.
5. **Invitation protection is incomplete, including a role escalation defect.** Send-invite is registered outside the auth route group yet dereferences the current user. Complete-invite does not repeat expiration/accepted checks, and does not explicitly assign the invitation's operator role. The expanded users schema defaults role to `owner`, so account completion can create an owner instead of the intended operator. Delivery code is commented out despite returning “Invite sent”.

### Broken wiring and implementation gaps

6. Expanded category routes reference the **EquipmentCategory model**, not EquipmentCategoryController.
7. `/accept/invite` supplies no route token, while acceptInvite requires a `$token` argument.
8. Expanded `/home` is disabled, but scaffold controllers retain `/home` redirects. The custom logout method points at `/auth/login`, which is not the active login route; the method itself is not the currently wired logout handler.
9. Equipment search uses PostgreSQL-specific `ilike` despite other portable database configurations, and queries an `operator` relationship that is commented out in Equipment.php.
10. EnsureEmailIsVerified redirects verified users before its `$next` call, making the continuation unreachable; `verification.notice` is not explicitly registered by the expanded routes. The User model also does not implement MustVerifyEmail.
11. EnsureSubscriptionActive calls `activeSubscription()`, while Organization defines `subscription()` instead. Several middleware aliases are registered but not applied to the relevant web routes.
12. LocationAccess calls its non-static accessibleUnitIds method statically. EnsureBaseAccess calls accessibleBaseIds, which is commented out in User. These are additional inconsistencies in alternate access helpers.
13. Maintenance methods validate into `$bI`, `$mS`, or `$mL`, then create records using a different `$validated` variable, losing the submitted data. There are mismatched relationship/table names, a `nullabe` rule typo, and updateWorkOrder never persists its validated task. getWorkOrderByEquipment ignores its supplied equipment and returns the first organization equipment.
14. Equipment valuation percentage calculation divides by cost multiplied by 100 instead of multiplying the ratio by 100; the market-value fallback is overwritten and zero/missing cost is not handled.
15. The PWA worker caches `/css/app.css` and `/js/app.js`, unlike the current `public/assets` layout. Root-relative URLs also conflict with subdirectory deployment. Its offline fallback key differs from the cached `/offline` entry, and its timestamp-based cache name is unstable.

These findings are established from source paths and method bodies, not a claim that every branch has been reproduced through HTTP.

## Migration implications for plain PHP, CSS and JavaScript

Most existing markup, local styles, images and interaction designs can be preserved. Blade expressions/directives need actual PHP conversion; renaming `.blade.php` files is insufficient. The major work is replacing framework services and completing or explicitly retaining unfinished feature boundaries.

Proposed organization:

```text
index.php                 public landing page
login.php / register.php  authentication pages
dashboard.php             authenticated overview
equipment.php             asset management
maintenance.php           maintenance screen
users.php / reports.php   team and reporting screens
includes/                 bootstrap, PDO, sessions, auth, CSRF, validation, layout
actions/                  validated POST handlers
api/                      authenticated JSON endpoints
assets/css/               standalone styles
assets/js/                standalone browser scripts
assets/images/            existing usable assets
database/                 SQL schema and explicit upgrade/import scripts
storage/                  protected logs and uploads
```

Private configuration, database files and storage need server-side access restrictions; preserve existing data before any schema changes. Use PDO prepared queries, password_hash/password_verify, session-ID regeneration, CSRF tokens and centralized organization/location checks. Plain PHP does not require giving up these protections.

For fully vanilla frontend JavaScript, replace jQuery/DataTables/OwlCarousel/Bootstrap behavior as well as removing build tooling. Decide which existing visual features need custom equivalents; React removal is not the central task because the application already uses Blade.

Suggested sequence: preserve source/data → consolidate the baseline → implement PHP bootstrap/database/auth/access → convert layouts and public/auth pages → migrate equipment and hierarchy → connect remaining workflows → implement reports and exports → repair PWA → remove retired framework/build artifacts only after behavior checks.

Verification should cover guest restrictions, session login/logout, CSRF rejection, cross-organization reads/writes, operators with no assignments, location reassignment, expired/reused invitations, CRUD persistence, empty states, subdirectory links and asset loading. Existing tests only cover a root-page HTTP 200 and a true-is-true assertion in each app, so they do not establish functional parity.

## Review limits

PHP CLI is available (8.2.12). All 161 PHP files checked across both applications' app, routes, database and config directories passed syntax linting. This does not validate Blade rendering or runtime behavior. The expanded app has no installed vendor tree or bundled SQLite file, so its full Laravel runtime was not exercised. No dependencies were installed, migrations run, emails sent or data changed. No browser visual verification was performed. This analysis preserves the earlier nonagon.md catalog and records the larger workspace separately.
